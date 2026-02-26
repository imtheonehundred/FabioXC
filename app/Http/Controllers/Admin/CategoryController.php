<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Stream\Models\StreamCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StreamCategory::withCount('streams');

        if ($search = $request->input('search')) {
            $query->where('category_name', 'like', "%{$search}%");
        }
        if ($request->filled('category_type')) {
            $query->where('category_type', $request->input('category_type'));
        }
        if ($sort = $request->input('sort')) {
            $query->orderBy($sort, $request->input('direction', 'asc'));
        } else {
            $query->orderBy('cat_order');
        }

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $query->paginate($request->input('per_page', 25))->withQueryString(),
            'filters' => $request->only(['search', 'sort', 'direction', 'category_type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Categories/Create', [
            'parents' => StreamCategory::whereNull('parent_id')->orderBy('category_name')->get(['id', 'category_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:live,movie,series,radio',
            'parent_id' => 'nullable|exists:stream_categories,id',
            'cat_order' => 'integer|min:0',
        ]);

        StreamCategory::create($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(StreamCategory $category)
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'parents' => StreamCategory::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('category_name')->get(['id', 'category_name']),
        ]);
    }

    public function update(Request $request, StreamCategory $category)
    {
        $data = $request->validate([
            'category_name' => 'required|string|max:255',
            'category_type' => 'required|in:live,movie,series,radio',
            'parent_id' => 'nullable|exists:stream_categories,id',
            'cat_order' => 'integer|min:0',
        ]);

        $category->update($data);
        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(StreamCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
