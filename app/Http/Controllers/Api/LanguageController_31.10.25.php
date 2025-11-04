<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    // Admin only - Get all users
    public function index()
    {
        $language = Language::get();
        return response()->json([
            'status' => 'success',
            'data' => $language,
            'message' => 'language list',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:languages,name',
                'shortname' => 'required|string|max:255|unique:languages,shortname',
                'thumble' => 'nullable|mimes:jpg,jpeg,png,svg',
                'country_code' => 'nullable|string|max:10',
                'status' => 'nullable',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $language = new Language;
        $language->name = $request->name;
        $language->shortname = $request->shortname;
        $language->country_code = $request->country_code;
        $language->status = $request->status;
        if ($request->hasFile('thumble')) {
            $imagePath = $request->file('thumble')->store('uploads/language', 'public');
            $language->thumble = $imagePath;
        }
        $language->save();

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $language,
            'message' => 'Language created successfully',
        ]);
    }

    public function languagedetails($id)
    {

        $language = Language::find($id);
        if ($language) {
            return response()->json([
                'status' => 'success',
                'message' => 'User details',
                'data' => $language,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Language not found',
            ], 404);
        }
    }

    public function update(Request $request, Language $language)
    {

        try {
            $request->validate([
                'name' => 'sometimes|string|max:255|unique:languages,name,' . $language->id,
                'shortname' => 'sometimes|string|max:255|unique:languages,shortname,' . $language->id,
                'thumble' => 'nullable|mimes:jpg,jpeg,png,svg',
                'country_code' => 'nullable|string|max:10',
                'status' => 'nullable',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        $language->name = $request->name;
        $language->shortname = $request->shortname;
        $language->country_code = $request->country_code;
        $language->status = $request->status;

        if ($request->hasFile('thumble')) {

            // Delete old image if exists
            if ($language->thumble && file_exists(public_path($language->thumble))) {
                unlink(public_path($language->thumble));
            }
            $destinationPath = public_path('uploads/language');

            $image = $request->file('thumble');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName = time() . '_' . $originalName . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            $language->thumble = 'uploads/language/' . $imageName;
        }

        $language->update();

        // Return response
        return response()->json([
            'status' => 'success',
            // 'data' => $language,
            'message' => 'Language updated successfully',
        ]);
    }

    public function storeorupdate(Request $request, $id = null)
    {
        $language = $id ? Language::find($id) : new Language;

        if ($id && !$language) {
            return response()->json([
                'status' => 'error',
                'message' => 'Language not found.',
            ], 404);
        }


        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:languages,name' . ($id ? ',' . $language->id : ''),
                'shortname' => 'required|string|max:255|unique:languages,shortname' . ($id ? ',' . $language->id : ''),
                'thumble' => 'nullable|mimes:jpg,jpeg,png,svg',
                'country_code' => 'nullable|integer|exists:countries,id',
                'status' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Assign values
        $language->name = $request->name ?? $language->name;
        $language->shortname = $request->shortname ?? $language->shortname;
        $language->country_code = $request->country_code ?? $language->country_code;
        $language->status = $request->status ?? $language->status;

        // Handle image upload
        if ($request->hasFile('thumble')) {
            // Delete old image if exists
            if ($language->thumble && file_exists(public_path($language->thumble))) {
                unlink(public_path($language->thumble));
            }

            $destinationPath = public_path('uploads/language');

            $image = $request->file('thumble');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName = time() . '_' . $originalName . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            $language->thumble = 'uploads/language/' . $imageName;
        }

        $language->save();

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'Language updated successfully.'
                : 'Language created successfully.',
        ], 200);
    }


    public function destroy(Language $language)
    {
        if ($language->id) {
            $language->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Language deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Language not found',
            ], 404);
        }
    }
}
