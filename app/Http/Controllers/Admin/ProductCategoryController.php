<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ProductCategory::with('parent');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->paginate(10);
        $parents = ProductCategory::whereNull('parent_id')->get();

        return view('admin.products.categories.index', compact('categories', 'parents'));
    }

    public function table(Request $request)
    {
        $query = ProductCategory::with('parent');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query->paginate(10);
        $parents = ProductCategory::whereNull('parent_id')->get();

        return view('admin.products.categories.table', compact('categories', 'parents'));
    }

    public function create()
    {
        $categories = ProductCategory::whereNull('parent_id')->get();
        return view('admin.products.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = Storage::url($imagePath);
        }

        ProductCategory::create($validated);

        return redirect()->route('admin.products.menu')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $category = ProductCategory::findOrFail($id);
        $categories = ProductCategory::whereNull('parent_id')->where('id', '!=', $id)->get();

        return view('admin.products.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:product_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:Active,Inactive',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldPath = str_replace('/storage/', '', $category->image);
                Storage::disk('public')->delete($oldPath);
            }

            $imagePath = $request->file('image')->store('categories', 'public');
            $validated['image'] = Storage::url($imagePath);
        }

        $category->update($validated);

        return redirect()->route('admin.products.menu')->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $category)
    {
        if ($category->image) {
            $oldPath = str_replace('/storage/', '', $category->image);
            Storage::disk('public')->delete($oldPath);
        }

        $category->delete();
        return redirect()->route('admin.products.menu')->with('success', 'Category deleted successfully.');
    }
}
