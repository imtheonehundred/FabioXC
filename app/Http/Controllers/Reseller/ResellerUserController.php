<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Domain\Line\Models\Line;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResellerUserController extends Controller
{
    public function index(Request $request)
    {
        $lines = Line::where('created_by', auth()->id());
        if ($search = $request->input('search')) $lines->where('username', 'like', "%{$search}%");

        return Inertia::render('Reseller/Users/Index', [
            'users' => $lines->orderByDesc('id')->paginate(25)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }
}
