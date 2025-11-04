<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Admin & Manager - View all clients
    public function index(Request $request)
    {
        $query = Client::with(['creator', 'leads', 'tickets']);

        // If user is not Admin or Manager, show only their created clients
        if (!$request->user()->hasAnyRole(['admin', 'manager'])) {
            $query->where('created_by', $request->user()->id);
        }

        $clients = $query->get();
        return response()->json($clients);
    }

    // All authenticated users can create clients
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'address' => $request->address,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Client created successfully',
            'client' => $client->load('creator')
        ], 201);
    }

    // View single client
    public function show(Request $request, Client $client)
    {
        // Check access
        if (!$request->user()->hasAnyRole(['admin', 'manager']) && $client->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($client->load(['creator', 'leads', 'tickets']));
    }

    // Manager & Admin can edit clients
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        $client->update($request->only(['name', 'email', 'phone', 'company', 'address']));

        return response()->json([
            'message' => 'Client updated successfully',
            'client' => $client
        ]);
    }

    // Admin only - Delete client
    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json([
            'message' => 'Client deleted successfully'
        ]);
    }
}
