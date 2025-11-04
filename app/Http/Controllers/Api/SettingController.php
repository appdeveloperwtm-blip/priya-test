<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;

class SettingController extends Controller
{

    public function banklist()
    {
        $bank = Bank::get();
        foreach ($bank as $value) {
            if ($value && isset($value->image)) {
                $value->image = $value->image ? asset($value->image) : null;
            }
        }
        return response()->json([
            'status' => 'success',
            'data' => $bank,
            'message' => 'Bank list',
        ]);
    }

    public function bank_store_or_update(Request $request, $id = null)
    {
        try {
            $bank = $id ? Bank::findOrFail($id) : new Bank;

            // Validation rules
            $request->validate([
                //'name' => 'required|string|max:255|unique:banks,name,' . ($id ?? 'NULL') . ',id',
                'name' => 'required|string|max:255|unique:banks,name' . ($id ? ',' . $bank->id : ''),
                'image' => 'nullable|mimes:jpeg,png,jpg,svg',
                'status' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $bank->name = $request->name;
        $bank->description = $request->description;
        $bank->status = $request->status;

        if ($request->hasFile('image')) {
            $bank->image = CommonHelper::uploadImage($request->file('image'), $bank->image, 'banks');
        }
        $bank->save();

        return response()->json([
            'status' => 'success',
            'message' => $id ? 'Bank updated successfully' : 'Bank created successfully',
        ]);
    }

    public function bankdelete($id)
    {
        $bank = Bank::find($id);
        if ($bank) {
            if (isset($bank->image) && file_exists(public_path($bank->image))) {
                @unlink(public_path($bank->image));
            }
            $bank->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Bank Deleted',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Bank not found',
            ], 404);
        }
    }

    public function bankdetails($id)
    {
        $bank = Bank::find($id);
        if ($bank) {
            if (isset($bank->image)) {
                $bank->image = $bank->image ? asset($bank->image) : null;
            }

            return response()->json([
                'status' => 'success',
                'data' => $bank,
                'message' => 'bank details',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'bank not found',
            ], 404);
        }
    }
}
