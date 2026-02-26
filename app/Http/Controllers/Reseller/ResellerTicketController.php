<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Domain\Ticket\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResellerTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::where('user_id', auth()->id());
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'desc'));
        else $query->orderByDesc('created_at');

        return Inertia::render('Reseller/Tickets/Index', [
            'tickets' => $query->paginate(25)->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create() { return Inertia::render('Reseller/Tickets/Create'); }

    public function store(Request $request)
    {
        Ticket::create(array_merge($request->validate([
            'subject' => 'required|string|max:255', 'message' => 'required|string',
            'priority' => 'required|in:low,normal,high',
        ]), ['user_id' => auth()->id()]));
        return redirect()->route('reseller.tickets.index')->with('success', 'Ticket submitted.');
    }
}
