<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    // Get leads based on role
    public function index(Request $request)
    {
        $query = Lead::with(['client', 'assignedUser', 'followUps']);

        // Sales Executive - only own leads
        if ($request->user()->hasRole('sales')) {
            $query->where('assigned_to', $request->user()->id);
        }
        // Manager & Admin - all leads

        $leads = $query->get();
        return response()->json($leads);
    }

    // Create lead
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:new,contacted,qualified,converted,lost',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create($request->all());

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead->load(['client', 'assignedUser'])
        ], 201);
    }

    // View single lead
    public function show(Request $request, Lead $lead)
    {
        // Sales can only view their own leads
        if ($request->user()->hasRole('sales') && $lead->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($lead->load(['client', 'assignedUser', 'followUps']));
    }

    // Update lead
    public function update(Request $request, Lead $lead)
    {
        // Sales can only update their own leads
        if ($request->user()->hasRole('sales') && $lead->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:new,contacted,qualified,converted,lost',
            'value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $lead->update($request->all());

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead->load(['client', 'assignedUser'])
        ]);
    }

    // Manager can assign leads
    public function assignLead(Request $request, Lead $lead)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update(['assigned_to' => $request->assigned_to]);

        return response()->json([
            'message' => 'Lead assigned successfully',
            'lead' => $lead->load(['client', 'assignedUser'])
        ]);
    }

    // Delete lead (Admin only)
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json([
            'message' => 'Lead deleted successfully'
        ]);
    }
}
