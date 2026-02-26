<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Epg\EpgService;
use App\Domain\Epg\Models\Epg;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EpgController extends Controller
{
    public function __construct(
        private EpgService $epgService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Epg/Index', [
            'epgs' => $this->epgService->list(
                $request->only(['search']),
                (int) $request->input('per_page', 25)
            ),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create() { return Inertia::render('Admin/Epg/Create'); }

    public function store(Request $request)
    {
        $this->epgService->create($request->validate(['epg_name' => 'required|string|max:255', 'epg_url' => 'required|string']));
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source created.');
    }

    public function edit(Epg $epg) { return Inertia::render('Admin/Epg/Edit', ['epg' => $epg]); }

    public function update(Request $request, Epg $epg)
    {
        $this->epgService->update($epg, $request->validate(['epg_name' => 'required|string|max:255', 'epg_url' => 'required|string']));
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source updated.');
    }

    public function destroy(Epg $epg)
    {
        $this->epgService->delete($epg);
        return redirect()->route('admin.epgs.index')->with('success', 'EPG source deleted.');
    }
}
