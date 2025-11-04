<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SmsGatewayResource;
use App\Models\Currency;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;

class CurrencyController extends Controller
{

    //currency----------------------
    public function currencylist()
    {
        $currency = Currency::get();
        $data = $currency->map(function ($currency) {
            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
                'status' => $currency->status,
                'symbol' => $currency->symbol,
                'exchange_rate' => $currency->exchange_rate,
                'created_at' => $currency->created_at,
                'updated_at' => $currency->updated_at,
            ];
        });
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Currency list',
        ]);
    }

    public function currency_store_or_update(Request $request, $id = null)
    {
        $currency = $id ? Currency::find($id) : new Currency;

        if ($id && !$currency) {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found.',
            ], 404);
        }
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:currencies,name' . ($id ? ',' . $currency->id : ''),
                'code' => 'required|string|max:6|unique:currencies,code' . ($id ? ',' . $currency->id : ''),
                'symbol' => 'required|string|max:5|unique:currencies,symbol' . ($id ? ',' . $currency->id : ''),
                'exchange_rate' => 'required|numeric',
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
        $currency->name = $request->name ?? $currency->name;
        $currency->code = $request->code ?? $currency->code;
        $currency->symbol = $request->symbol ?? $currency->symbol;
        $currency->exchange_rate = $request->exchange_rate ?? $currency->exchange_rate;
        $currency->status = $request->status ?? $currency->status;
        $currency->save();

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'Currency updated successfully.'
                : 'Currency created successfully.',
        ], 200);
    }

    public function currencydetails($id)
    {
        $currency = Currency::find($id);

        if ($currency) {
            return response()->json([
                'status' => 'success',
                'message' => 'Currency details',
                'data' => $currency,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found',
            ], 404);
        }
    }

    public function currencydelete($id)
    {
        $currency = Currency::find($id);
        if ($currency) {
            $currency->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Currency deleted successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Currency not found',
            ], 404);
        }
    }

    //paymentgateway ----------------------------

    public function paymentgatewaylist()
    {
        $paymentgatewaylist = PaymentGateway::all();
        foreach ($paymentgatewaylist as $gateway) {
            if ($gateway && isset($gateway->image)) {
                $gateway->image = $gateway->image ? asset($gateway->image) : null;
            }
        }
        return response()->json([
            'status' => 'success',
            'data' => $paymentgatewaylist,
            'message' => 'Paymentgateway list',
        ]);
    }

    public function paymentgatway_store_or_update(Request $request, $id = null)
    {
        $paymentgateway = $id ? PaymentGateway::find($id) : new PaymentGateway;

        if ($id && !$paymentgateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'PaymentGateway not found.',
            ], 404);
        }
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:payment_gateways,name' . ($id ? ',' . $paymentgateway->id : ''),
                'from_email' => 'required|string|max:255',
                'api_key' => 'required|string|max:255',
                'secret_key' => 'required|string|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg',
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
        $paymentgateway->name = $request->name ?? $paymentgateway->name;
        $paymentgateway->description = $request->description ?? $paymentgateway->description;
        $paymentgateway->from_email = $request->from_email ?? $paymentgateway->from_email;
        $paymentgateway->api_key = $request->api_key ?? $paymentgateway->api_key;
        $paymentgateway->secret_key = $request->secret_key ?? $paymentgateway->secret_key;
        $paymentgateway->status = $request->status ?? $paymentgateway->status;
        // Handle image upload
        if ($request->hasFile('image')) {
            $paymentgateway->image = CommonHelper::uploadImage(
                $request->file('image'),
                $paymentgateway->image,
                'paymentgateway'
            );
        }
        $paymentgateway->save();

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'PaymentGateway updated successfully.'
                : 'PaymentGateway created successfully.',
        ], 200);
    }

    public function paymentgateway_details($id)
    {
        $paymentgateway = PaymentGateway::find($id);
        if ($paymentgateway) {
            if (isset($paymentgateway->image)) {
                $paymentgateway->image = $paymentgateway->image ? asset($paymentgateway->image) : null;
            }

            return response()->json([
                'status' => 'success',
                'data' => $paymentgateway,
                'message' => 'Paymentgateway details',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Paymentgateway not found',
            ], 404);
        }
    }

    public function paymentgateway_delete($id)
    {
        $paymentgateway = PaymentGateway::find($id);
        if ($paymentgateway) {
            if (isset($paymentgateway->image) && file_exists(public_path($paymentgateway->image))) {
                @unlink(public_path($paymentgateway->image));
            }
            $paymentgateway->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Paymentgateway Deleted',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Paymentgateway not found',
            ], 404);
        }
    }

    //sms gateway----------------------
    public function smsgatewaylist()
    {
        $smsgatewaylist = SmsGateway::all();
        /* foreach ($smsgatewaylist as $gateway) {
            if ($gateway && isset($gateway->image)) {
                $gateway->image = $gateway->image ? asset($gateway->image) : null;
            }
        }*/
        return response()->json([
            'status' => 'success',
            //'data' => $smsgatewaylist,
            'data' => SmsGatewayResource::collection($smsgatewaylist),
            'message' => 'Smsgateway list',
        ]);
    }

    public function smsgateway_store_or_update(Request $request, $id = null)
    {

        $smsgateway = $id ? SmsGateway::find($id) : new SmsGateway;

        if ($id && !$smsgateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'SmsGateway not found.',
            ], 404);
        }
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:sms_gateways,name' . ($id ? ',' . $smsgateway->id : ''),
                'api_key' => 'required|string|max:255',
                'api_secret_key' => 'required|string|max:255',
                'sender_id' => 'required|string|max:255',
                'image' => 'nullable|mimes:jpg,jpeg,png,svg',
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
        $smsgateway->name = $request->name ?? $smsgateway->name;
        $smsgateway->description = $request->description ?? $smsgateway->description;
        $smsgateway->sender_id = $request->sender_id ?? $smsgateway->sender_id;
        $smsgateway->api_key = $request->api_key ?? $smsgateway->api_key;
        $smsgateway->api_secret_key = $request->api_secret_key ?? $smsgateway->secret_key;
        $smsgateway->status = $request->status ?? $smsgateway->status;
        // Handle image upload
        if ($request->hasFile('image')) {
            $smsgateway->image = CommonHelper::uploadImage(
                $request->file('image'),
                $smsgateway->image,
                'smsgateway'
            );
        }
        $smsgateway->save();

        return response()->json([
            'status' => 'success',
            'message' => $id
                ? 'SmsGateway updated successfully.'
                : 'SmsGateway created successfully.',
        ], 200);
    }

    public function smsgatewaydetails($id)
    {
        $smsgateway = Smsgateway::find($id);
        /* if ($smsgateway && isset($smsgateway->image)) {
            $smsgateway->image = $smsgateway->image ? asset($smsgateway->image) : null;
        }*/
        if ($smsgateway) {
            return response()->json([
                'status' => 'success',
                'data' => new SmsGatewayResource($smsgateway),
                'message' => 'Smsgateway details',
                //'data' => $smsgateway,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Smsgateway not found',
            ], 404);
        }
    }

    public function smsgatewaydelete($id)
    {
        $smsgateway = Smsgateway::find($id);
        if ($smsgateway) {
            if (isset($smsgateway->image) && file_exists(public_path($smsgateway->image))) {
                @unlink(public_path($smsgateway->image));
            }
            $smsgateway->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'smsgateway Deleted',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'smsgateway not found',
            ], 404);
        }
    }
}
