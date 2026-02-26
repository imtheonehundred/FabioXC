<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Bouquet\BouquetService;
use App\Domain\Bouquet\Models\Bouquet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BouquetController extends Controller
{
    public function __construct(
        private BouquetService $bouquetService
    ) {}

    public function index(Request $request)
    {
        return Inertia::render('Admin/Bouquets/Index', [
            'bouquets' => $this->bouquetService->list(
                $request->only(['search', 'sort', 'direction']),
                (int) $request->input('per_page', 25)
            ),
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bouquets/Create', [
            'streams' => $this->bouquetService->getStreams(),
            'movies' => $this->bouquetService->getMovies(),
            'series' => $this->bouquetService->getSeries(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bouquet_name' => 'required|string|max:255',
            'bouquet_channels' => 'nullable|array', 'bouquet_movies' => 'nullable|array',
            'bouquet_series' => 'nullable|array', 'bouquet_radios' => 'nullable|array',
            'bouquet_order' => 'integer|min:0',
        ]);
        $this->bouquetService->create($data);
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet created.');
    }

    public function edit(Bouquet $bouquet)
    {
        return Inertia::render('Admin/Bouquets/Edit', [
            'bouquet' => $bouquet,
            'streams' => $this->bouquetService->getStreams(),
            'movies' => $this->bouquetService->getMovies(),
            'series' => $this->bouquetService->getSeries(),
        ]);
    }

    public function update(Request $request, Bouquet $bouquet)
    {
        $data = $request->validate([
            'bouquet_name' => 'required|string|max:255',
            'bouquet_channels' => 'nullable|array', 'bouquet_movies' => 'nullable|array',
            'bouquet_series' => 'nullable|array', 'bouquet_radios' => 'nullable|array',
            'bouquet_order' => 'integer|min:0',
        ]);
        $this->bouquetService->update($bouquet, $data);
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet updated.');
    }

    public function destroy(Bouquet $bouquet)
    {
        $this->bouquetService->delete($bouquet);
        return redirect()->route('admin.bouquets.index')->with('success', 'Bouquet deleted.');
    }
}
