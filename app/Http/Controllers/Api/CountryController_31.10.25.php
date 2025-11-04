<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CountryController extends Controller
{
    // Admin only - Get all users
    public function index()
    {
        $countrydata = Country::get();
        $countries = $countrydata->map(function ($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'status' => $country->status,
                'order_by' => $country->order_by,
                'image' => $country->image ? url($country->image) : null,
                'created_at' => $country->created_at,
                'updated_at' => $country->updated_at,
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $countries,
            'message' => 'Country list',
        ]);
    }

    /* public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:countries,name',
                'code' => 'required|string|max:7|unique:countries,code',
                'status' => 'required',
                'order_by' => 'nullable|integer|unique:countries,order_by',
                //'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'image' => 'nullable|mimes:jpeg,png,jpg,svg',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $country = new Country;
        $country->name = $request->name;
        $country->code = $request->code;
        $country->status = $request->status;
        // Handle order_by
        if ($request->filled('order_by')) {
            $country->order_by = $request->order_by;
        } else {
            // Auto-assign unique order_by
            $maxOrder = Country::max('order_by');
            $country->order_by = $maxOrder ? $maxOrder + 1 : 1;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/countries'), $filename);
            $country->image = 'uploads/countries/' . $filename;
        }

        $country->save();

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $country,
            'message' => 'Country created successfully',
        ]);
    }*/

    /*public function update(Request $request, Country $country)
    {

        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:countries,name,' . $country->id,
                'code' => 'required|string|max:7|unique:countries,code,' . $country->id,
                'status' => 'required',
                'order_by' => 'nullable|integer',
                'image' => 'nullable|mimes:jpeg,png,jpg,svg',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }
        $country->name = $request->name;
        $country->code = $request->code;
        $country->status = $request->status;

        // Handle order_by
        if ($request->filled('order_by')) {
            $newOrder = $request->order_by;

            if ($newOrder != $country->order_by) {


                // Check if another country already has this order
                $existingCountry = Country::where('order_by', $newOrder)->first();

                if ($existingCountry) {

                    // Swap order numbers
                    $tempOrder = $existingCountry->order_by;
                    $existingCountry->order_by = $country->order_by;
                    $existingCountry->save();
                }
                $country->order_by = $newOrder;
            }
        } else {
            // Assign next available order_by if not provided
            $maxOrder = Country::max('order_by');
            $country->order_by = $maxOrder ? $maxOrder + 1 : 1;
        }

        // Handle image upload

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($country->image && Storage::disk('public')->exists($country->image)) {
                Storage::disk('public')->delete($country->thumble);
            }
            $imagePath = $request->file('image')->store('uploads/countries', 'public');
            $country->image = $imagePath;
        }

        $country->update();

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $country,
            'message' => 'Country updated successfully',
        ]);
    }*/

    public function countrystoreOrUpdate(Request $request, $id = null)
    {
        try {
            $country = $id ? Country::findOrFail($id) : new Country;

            // Validation rules
            $request->validate([
                'name' => 'required|string|max:255|unique:countries,name' . ($id ? ',' . $country->id : ''),
                'code' => 'required|string|max:7|unique:countries,code' . ($id ? ',' . $country->id : ''),
                'status' => 'required',
                'order_by' => 'nullable|integer',
                'image' => 'nullable|mimes:jpeg,png,jpg,svg',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $country->name = $request->name;
        $country->code = $request->code;
        $country->status = $request->status;

        // Handle order_by
        if ($request->filled('order_by')) {
            $newOrder = $request->order_by;

            if ($id && $newOrder != $country->order_by) {
                // Check if another country already has this order
                $existingCountry = Country::where('order_by', $newOrder)->first();

                if ($existingCountry) {
                    // Swap order numbers safely
                    $tempOrder = $existingCountry->order_by;
                    $existingCountry->order_by = $country->order_by;
                    $existingCountry->save();
                }
                $country->order_by = $newOrder;
            } elseif (!$id) {
                $country->order_by = $newOrder;
            }
        } else {
            // Auto-assign next available order_by
            $maxOrder = Country::max('order_by');
            $country->order_by = $maxOrder ? $maxOrder + 1 : 1;
        }

        // Handle image upload
       /* if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($country->image && Storage::disk('public')->exists($country->image)) {
                Storage::disk('public')->delete($country->image);
            }

            $imagePath = $request->file('image')->store('uploads/countries', 'public');
            $country->image = $imagePath;
        } */

        if ($request->hasFile('image')) {

            // Delete old image if exists
            if ($country->image && file_exists(public_path($country->image))) {
                unlink(public_path($country->image));
            }
            $destinationPath = public_path('uploads/countries');

            $image = $request->file('image');
            $originalName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName = time() . '_' . $originalName . '.' . $image->getClientOriginalExtension();
            $image->move($destinationPath, $imageName);
            $country->image = 'uploads/countries/' . $imageName;
        }

        // Save or update
        $country->save();

        return response()->json([
            'status' => 'success',
            'data' => $country,
            'message' => $id ? 'Country updated successfully' : 'Country created successfully',
        ]);
    }

    public function destroy(Country $country)
    {
        if ($country->id) {
            $country->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Country deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Country not found',
            ], 404);
        }
    }

    public function countrydetails($id)
    {
        $Country = Country::find($id);
        if ($Country) {
            return response()->json([
                'status' => 'success',
                'message' => 'Country details',
                'data' => $Country,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Country not found',
            ], 404);
        }
    }

    //state create---------------------

    public function statelist()
    {
        $state = State::get();
        return response()->json([
            'status' => 'success',
            'data' => $state,
            'message' => 'State list',
        ]);
    }

    /*public function statestore(Request $request)
    {
        try {
            $request->validate([
                //'name' => 'required|string|max:255',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    // Prevent same state name in same country
                    Rule::unique('states')->where(function ($query) use ($request) {
                        return $query->where('country_id', $request->country_id);
                    }),
                ],
                'country_id' => 'required|exists:countries,id',
                'status' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $state = new State;
        $state->name = $request->name;
        $state->country_id = $request->country_id;
        $state->status = $request->status;
        $state->save();

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $state,
            'message' => 'State created successfully',
        ]);
    }*/

    /*public function stateupdate(Request $request, $id)
    {

        try {
            $request->validate([
                //'name' => 'required|string|max:255',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    // Unique check but ignore the current state record
                    Rule::unique('states')->where(function ($query) use ($request, $id) {
                        return $query->where('country_id', $request->country_id);
                    })->ignore($id),
                ],
                'country_id' => 'required|exists:countries,id',
                'status' => 'required'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $state = State::find($id);
        if (!$state) {
            return response()->json([
                'status' => 'error',
                'message' => 'State not found',
            ], 404);
        }

        $state->name = $request->name;
        $state->country_id = $request->country_id;
        $state->status = $request->status;
        $state->save();

        // Return response
        return response()->json([
            'status' => 'success',
            'data' => $state,
            'message' => 'State updated successfully',
        ]);
    }*/
    public function stateStoreOrUpdate(Request $request, $id = null)
    {
        // If ID provided → update; otherwise → create
        $state = $id ? State::find($id) : new State;

        if ($id && !$state) {
            return response()->json([
                'status' => 'error',
                'message' => 'State not found',
            ], 404);
        }

        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('states')
                        ->where(fn($query) => $query->where('country_id', $request->country_id))
                        ->ignore($id), // ignore for update
                ],
                'country_id' => 'required|exists:countries,id',
                'status' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Assign fields
        $state->name = $request->name;
        $state->country_id = $request->country_id;
        $state->status = $request->status;
        $state->save();

        return response()->json([
            'status' => 'success',
            'data' => $state,
            'message' => $id
                ? 'State updated successfully'
                : 'State created successfully',
        ]);
    }
    public function statedetails($id)
    {
        $state = State::find($id);
        if ($state) {
            return response()->json([
                'status' => 'success',
                'message' => 'State details',
                'data' => $state,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'State not found',
            ], 404);
        }
    }

    public function statedelete($id)
    {
        $state = State::find($id);
        if ($state) {
            $state->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'State deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'State not found',
            ], 404);
        }
    }

    public function citylist()
    {
        $city = City::get();
        return response()->json([
            'status' => 'success',
            'data' => $city,
            'message' => 'City list',
        ]);
    }

    public function city_store_or_update(Request $request, $id = null)
    {
        $city = $id ? City::find($id) : new City;

        if ($id && !$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found',
            ], 404);
        }

        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cities')
                        ->where(fn($query) => $query->where('state_id', $request->state_id))
                        ->ignore($id), // ignore for update
                ],
                'state_id' => 'required|exists:states,id',
                'status' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Assign fields
        $city->name = $request->name;
        $city->state_id = $request->state_id;
        //$city->status = $request->status ?? '1';
        $city->status = $request->status;
        $city->save();

        return response()->json([
            'status' => 'success',
            'data' => $city,
            'message' => $id
                ? 'State updated successfully'
                : 'State created successfully',
        ]);
    }

    public function citydetails($id)
    {
        $city = City::find($id);
        if ($city) {
            return response()->json([
                'status' => 'success',
                'message' => 'City details',
                'data' => $city,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found',
            ], 404);
        }
    }

    public function citydelete($id)
    {
        $city = City::find($id);
        if ($city) {
            $city->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'City deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found',
            ], 404);
        }
    }
}
