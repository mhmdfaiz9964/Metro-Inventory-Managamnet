<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // List brands
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('admin.products.bom.brands.index', compact('brands'));
    }

    // Store new brand
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        Brand::create($request->only(['name', 'note']));

        return redirect()->back()->with('success', 'Brand created successfully.');
    }

    // Update brand
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $brand = Brand::findOrFail($id);
        $brand->update($request->only(['name', 'note']));

        return redirect()->back()->with('success', 'Brand updated successfully.');
    }

    // Delete brand
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->back()->with('success', 'Brand deleted successfully.');
    }
}
