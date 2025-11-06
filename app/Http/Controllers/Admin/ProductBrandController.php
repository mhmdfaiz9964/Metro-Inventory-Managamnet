<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    // List product brands
    public function index()
    {
        $productBrands = ProductBrand::latest()->paginate(10);
        return view('admin.products.brands.index', compact('productBrands'));
    }

    // Store product brand
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        ProductBrand::create($request->only(['name', 'note']));

        return redirect()->back()->with('success', 'Product brand created successfully.');
    }

    // Update product brand
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $productBrand = ProductBrand::findOrFail($id);
        $productBrand->update($request->only(['name', 'note']));

        return redirect()->back()->with('success', 'Product brand updated successfully.');
    }

    // Delete product brand
    public function destroy($id)
    {
        $productBrand = ProductBrand::findOrFail($id);
        $productBrand->delete();

        return redirect()->back()->with('success', 'Product brand deleted successfully.');
    }
}
