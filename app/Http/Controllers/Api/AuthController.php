<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default role (Sales Executive)
        $user->roles()->attach(3); // role_id 3 assumed

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'user' => $user->load('roles'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }


    public function login(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Find user by email
            $user = User::where('email', $validated['email'])->first();

            // Check credentials
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password.',
                ], 401);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Successful login
            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'user' => $user->load('roles'),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation failed — return error messages
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Other errors (optional)
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles', 'details');
        if ($user->details && isset($user->details->image)) {
            $user->details->image = $user->details->image ? asset($user->details->image) : null;
        }
        return response()->json([
            'status' => 'success',
           // 'data' => $request->user()->load('roles', 'details'),
            'data' => $user,
            'message' => 'login user details'
        ]);
    }

    public function profile_update(Request $request)
    {
        $user = $request->user();
        try {
            $request->validate([
                'fname' => 'required|string|max:255',
                'lname' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phoneno' => 'nullable|string|max:20',
                //'country_code' => 'nullable|string|max:100',
                //'country' => 'nullable|integer|max:100',
                'country_id' => 'nullable|integer|exists:countries,id',
                //'state_id' => 'nullable|integer|max:100',
                //'city_id' => 'nullable|integer|max:100',
                'state_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('states', 'id')->where(function ($query) use ($request) {
                        if ($request->country_id) {
                            $query->where('country_id', $request->country_id);
                        }
                    }),
                ],
                'city_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('cities', 'id')->where(function ($query) use ($request) {
                        if ($request->state_id) {
                            $query->where('state_id', $request->state_id);
                        }
                    }),
                ],
                'pin' => 'nullable|integer|max:20',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // ✅ Update User table fields
        $fname = $request->fname ?? $user->fname;
        $lname = $request->lname ?? $user->lname;
        $user->name = trim($fname . ' ' . $lname);
        $user->email = $request->email ?? $user->email;


        $userdetails = $user->details ?? new UserDetail(['user_id' => $user->id]);
        if ($request->hasFile('image')) {
            if ($userdetails->image && Storage::disk('public')->exists($userdetails->image)) {
                Storage::disk('public')->delete($userdetails->image);
            }

            $imagePath = $request->file('image')->store('uploads/users', 'public');
            $userdetails->image = $imagePath;
        }
        $userdetails->phone = $request->phoneno ?? $userdetails->phone;
        // $userdetails->country_code = $request->country_code ?? $userdetails->country_code;
        $userdetails->country = $request->country_id ?? $userdetails->country_id;
        $userdetails->state = $request->state_id ?? $userdetails->state_id;
        $userdetails->city = $request->city_id ?? $userdetails->city_id;
        $userdetails->pin = $request->pin ?? $userdetails->pin;
        $userdetails->address1 = $request->address1 ?? $userdetails->address1;
        $userdetails->address2 = $request->address2 ?? $userdetails->address2;
        $userdetails->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User Profile updated successfully',
            'data' => $user->load('details'),
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        try {
            $request->validate([
                'current_password' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            $fail('The current password is incorrect.');
                        }
                    },
                ],
                'new_password' => 'required|string',
                'confirm_password' => 'required|string|same:new_password',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ], 200);
    }
}
