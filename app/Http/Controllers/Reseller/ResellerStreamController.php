<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Domain\Stream\Models\Stream;
use App\Domain\Stream\Models\StreamCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResellerStreamController extends Controller
{
    public function index(Request $request)
    {
        $query = Stream::with('category');
        if ($search = $request->input('search')) $query->where('stream_display_name', 'like', "%{$search}%");
        if ($request->filled('category_id')) $query->where('category_id', $request->input('category_id'));
        if ($sort = $request->input('sort')) $query->orderBy($sort, $request->input('direction', 'asc'));
        else $query->orderByDesc('id');

        return Inertia::render('Reseller/Streams/Index', [
            'streams' => $query->paginate(25)->withQueryString(),
            'categories' => StreamCategory::where('category_type', 'live')->orderBy('category_name')->get(['id', 'category_name']),
            'filters' => $request->only(['search', 'sort', 'direction', 'category_id']),
        ]);
    }
}
