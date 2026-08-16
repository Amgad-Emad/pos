<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->with(['children.products', 'products'])
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create', [
            'parents' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')->with('success', __('messages.flash.created'));
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', [
            'category' => $category,
            'parents' => Category::whereNull('parent_id')
                ->whereKeyNot($category->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', __('messages.flash.updated'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists() || $category->products()->exists()) {
            return back()->with('error', __('messages.categories.has_children_or_products'));
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', __('messages.flash.deleted'));
    }
}
