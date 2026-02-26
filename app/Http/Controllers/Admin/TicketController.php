<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Ticket\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('user');
        if ($search = $request->input('search')) $query->where('subject', 'like', "%{$search}%");
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'asc'));
        else $query->orderByDesc('created_at');

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'status']),
        ]);
    }

    public function show(Ticket $ticket)
    {
        $ticket->load('user');
        return Inertia::render('Admin/Tickets/Show', ['ticket' => $ticket]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,closed',
            'admin_reply' => 'nullable|string',
        ]);
        if (!empty($data['admin_reply'])) $data['replied_at'] = now();
        $ticket->update($data);
        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->delete();
        return redirect()->route('admin.tickets.index')->with('success', 'Ticket deleted.');
    }
}
