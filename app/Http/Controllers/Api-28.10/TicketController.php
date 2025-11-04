<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    // Get tickets
    public function index(Request $request)
    {
        $query = Ticket::with(['client', 'assignedUser']);

        // Support staff - only assigned tickets
        if ($request->user()->hasRole('support')) {
            $query->where('assigned_to', $request->user()->id);
        }
        // Admin & Manager - all tickets

        $tickets = $query->get();
        return response()->json($tickets);
    }

    // Create ticket
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'required|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create($request->all());

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket->load(['client', 'assignedUser'])
        ], 201);
    }

    // View single ticket
    public function show(Request $request, Ticket $ticket)
    {
        // Support can only view their assigned tickets
        if ($request->user()->hasRole('support') && $ticket->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($ticket->load(['client', 'assignedUser']));
    }

    // Update ticket status (Support staff can update)
    public function update(Request $request, Ticket $ticket)
    {
        // Support can only update their assigned tickets
        if ($request->user()->hasRole('support') && $ticket->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($request->all());

        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket->load(['client', 'assignedUser'])
        ]);
    }

    // Delete ticket (Admin only)
    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return response()->json([
            'message' => 'Ticket deleted successfully'
        ]);
    }
}
