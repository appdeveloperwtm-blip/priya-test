<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;

class CategoryController extends Controller
{

    public function brandlist()
    {
        $brand = Brand::get();
        foreach ($brand as $brands) {
            if ($brands && isset($brands->image)) {
                $brands->image = $brands->image ? asset($brands->image) : null;
            }
        }
       /* $brands = $brand->map(function ($brand) {
            return [
                'id' => $brand->id,
                'name' => $brand->name,
                'description' => $brand->description,
                'status' => $brand->status,
                'image' => $brand->image ? asset($brand->image) : null,
                'created_at' => $brand->created_at,
                'updated_at' => $brand->updated_at,
            ];
        });*/

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Brand list',
        ]);
    }

    public function brand_store_or_update(Request $request, $id = null)
    {
        $brand = $id ? Brand::find($id) : new Brand;

        if ($id && !$brand) {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand not found.',
            ], 404);
        }
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg',
                'status' => 'required',
                'order_by' => 'nullable|integer',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Assign values
        $brand->name = $request->name ?? $brand->name;
        $brand->description = $request->description ?? $brand->description;
        $brand->status = $request->status ?? $brand->status;

        // Handle order_by
        if ($request->filled('order_by')) {
            $newOrder = $request->order_by;

            if ($id && $newOrder != $brand->order_by) {

                // Check if another brand already has this order
                $existingbrand = brand::where('order_by', $newOrder)->first();

                if ($existingbrand) {
                    // Swap order numbers safely
                    $tempOrder = $existingbrand->order_by;
                    $existingbrand->order_by = $brand->order_by;
                    $existingbrand->save();
                }
                $brand->order_by = $newOrder;
            } elseif (!$id) {
                $existingbrand = brand::where('order_by', $newOrder)->first();
                if ($existingbrand) {
                    $maxOrder = brand::max('order_by');
                    $brand->order_by = $maxOrder ? $maxOrder + 1 : 1;
                } else {
                   
                    $brand->order_by = $newOrder;
                }
            }
        } else {
            // Auto-assign next available order_by
            $maxOrder = brand::max('order_by');
            $brand->order_by = $maxOrder ? $maxOrder + 1 : 1;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $brand->image = CommonHelper::uploadImage(
                $request->file('image'),
                $brand->image,
                'brand' // folder inside /uploads
            );
        }
        $brand->save();

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'Brand updated successfully.'
                : 'Brand created successfully.',
        ], 200);
    }

    public function branddetails($id)
    {
        $brand = Brand::find($id);
        if ($brand && isset($brand->image)) {
            $brand->image = $brand->image ? asset($brand->image) : null;
        }
        if ($brand) {
            return response()->json([
                'status' => 'success',
                'message' => 'Brand details',
                'data' => $brand,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand not found',
            ], 404);
        }
    }

    public function branddelete($id)
    {
        $brand = Brand::find($id);
        if ($brand) {
            if ($brand->image && file_exists(public_path($brand->image))) {
                @unlink(public_path($brand->image));
            }
            $brand->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Brand deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand not found',
            ], 404);
        }
    }
}
