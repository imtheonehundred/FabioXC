<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Epg\Models\Epg;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EpgController extends Controller
{
    public function index(Request $request)
    {
        $query = Epg::query();
        if ($search = $request->input('search')) $query->where('epg_name', 'like', "%{$search}%");
        return Inertia::render('Admin/Epg/Index', [
            'epgs' => $query->orderByDesc('id')->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/Epg/Create'); }

    public function store(Request $request)
    {
        Epg::create($request->validate(['epg_name' => 'required|string|max:255', 'epg_url' => 'required|string']));
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source created.');
    }

    public function edit(Epg $epg) { return Inertia::render('Admin/Epg/Edit', ['epg' => $epg]); }

    public function update(Request $request, Epg $epg)
    {
        $epg->update($request->validate(['epg_name' => 'required|string|max:255', 'epg_url' => 'required|string']));
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source updated.');
    }

    public function destroy(Epg $epg)
    {
        $epg->delete();
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source deleted.');
    }
}
