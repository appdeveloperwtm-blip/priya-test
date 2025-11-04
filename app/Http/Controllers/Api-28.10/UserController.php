<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Admin only - Get all users
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    public function role()
    {
        $users = Role::get();
        return response()->json($users);
    }

    // Admin only - Create user
    public function store(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:55',
            'lname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:10',
        ]);

        // Save the user
        $user = User::create([
            'name' => $request->fname . ' ' . $request->lname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Attach role
        $user->roles()->attach($request->role_id);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/users', 'public');
        }

        // Create user details
        $user->details()->create([
            'image' => $imagePath,
            'phone' => $request->phone,
            'country' => $request->country,
            'country_code' => $request->country_code,
        ]);

        // Return response
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load('roles', 'details')
        ]);
    }

    // Admin only - Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        if ($request->has('role_id')) {
            $user->roles()->sync([$request->role_id]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles')
        ]);
    }

    // Admin only - Delete user
    public function destroy(User $user)
    {
        // Delete related user details (if exists)
        if ($user->details) {
            // Delete image file from storage if exists
            if ($user->details->image && Storage::disk('public')->exists($user->details->image)) {
                Storage::disk('public')->delete($user->details->image);
            }

            // Delete the user_details record
            $user->details()->delete();
        }

        // Finally delete the user record
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }


    // Admin only - Assign role to user
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->roles()->sync([$request->role_id]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles')
        ]);
    }
}
