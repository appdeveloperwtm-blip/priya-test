<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Models\Lead;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    // Get follow-ups
    public function index(Request $request)
    {
        $query = FollowUp::with(['lead', 'user']);

        // Sales Executive - only own follow-ups
        if ($request->user()->hasRole('sales')) {
            $query->where('user_id', $request->user()->id);
        }

        $followUps = $query->get();
        return response()->json($followUps);
    }

    // Create follow-up (Sales Executive)
    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'notes' => 'required|string',
            'follow_up_date' => 'required|date',
        ]);

        // Check if sales executive owns the lead
        $lead = Lead::find($request->lead_id);
        if ($request->user()->hasRole('sales') && $lead->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'You can only add follow-ups to your own leads'], 403);
        }

        $followUp = FollowUp::create([
            'lead_id' => $request->lead_id,
            'user_id' => $request->user()->id,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date,
        ]);

        return response()->json([
            'message' => 'Follow-up created successfully',
            'follow_up' => $followUp->load(['lead', 'user'])
        ], 201);
    }

    // View single follow-up
    public function show(Request $request, FollowUp $followUp)
    {
        if ($request->user()->hasRole('sales') && $followUp->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($followUp->load(['lead', 'user']));
    }

    // Update follow-up
    public function update(Request $request, FollowUp $followUp)
    {
        if ($request->user()->hasRole('sales') && $followUp->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'sometimes|string',
            'follow_up_date' => 'sometimes|date',
            'status' => 'sometimes|in:pending,completed',
        ]);

        $followUp->update($request->all());

        return response()->json([
            'message' => 'Follow-up updated successfully',
            'follow_up' => $followUp->load(['lead', 'user'])
        ]);
    }

    // Delete follow-up
    public function destroy(Request $request, FollowUp $followUp)
    {
        if ($request->user()->hasRole('sales') && $followUp->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $followUp->delete();
        return response()->json([
            'message' => 'Follow-up deleted successfully'
        ]);
    }
}
