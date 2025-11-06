<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BomComponent;
use App\Models\BomStock;
use App\Models\Products;
use App\Models\Brand;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class BomComponentController extends Controller
{
    public function index(Request $request)
    {
        $query = BomComponent::with(['product', 'brand']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('product_code', 'like', "%{$search}%")
                ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $boms = $query->orderBy('id', 'desc')->paginate(10);
        return view('admin.products.bom.index', compact('boms'));
    }

    public function table(Request $request)
    {
        $query = BomComponent::with(['product', 'brand']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('product_code', 'like', "%{$search}%")
                ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $boms = $query->orderBy('id', 'desc')->paginate(10);
        return view('admin.products.bom.table', compact('boms'));
    }

    public function create(Request $request)
    {
        $categories = ProductCategory::all(); // all categories
        $brands = Brand::all();
        $bomProduct = null;
        $boms = collect();

        // Get products for selected category
        $selectedCategoryId = $request->category_id;
        $products = $selectedCategoryId ? Products::where('category_id', $selectedCategoryId)->get() : collect();

        return view('admin.products.bom.create', compact('categories', 'products', 'brands', 'bomProduct', 'boms', 'selectedCategoryId'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'components.*.product_code' => 'nullable|string|max:255',
            'components.*.name' => 'required|string|max:255',
            'components.*.required_bom_qty' => 'required|numeric|min:0.01',
            'components.*.quantity' => 'nullable|numeric|min:0',
            'components.*.brand_id' => 'nullable|exists:brands,id',
            'components.*.model' => 'nullable|string|max:255',
            'components.*.notes' => 'nullable|string|max:255',
        ]);

        foreach ($request->components as $component) {
            $bomComponent = BomComponent::create([
                'product_id' => $request->product_id,
                'product_code' => $component['product_code'] ?? null,
                'name' => $component['name'],
                'quantity' => $component['quantity'] ?? 0,
                'required_bom_qty' => $component['required_bom_qty'],
                'brand_id' => $component['brand_id'] ?? null,
                'model' => $component['model'] ?? null,
                'notes' => $component['notes'] ?? null,
            ]);

            // ✅ Automatically create BomStock with available_stock = 0
            BomStock::create([
                'bom_component_id' => $bomComponent->id,
                'available_stock' => 0,
            ]);
        }

        return redirect()->route('admin.bom.menu')->with('success', 'BOM Components saved successfully.');
    }

    public function edit(BomComponent $bom, Request $request)
    {
        $categories = ProductCategory::all();
        $brands = Brand::all();
        $boms = collect();

        // Determine selected category
        $selectedCategoryId = $request->category_id ?? $bom->product->category_id;

        // Get products for selected category
        $products = $selectedCategoryId ? Products::where('category_id', $selectedCategoryId)->get() : collect();

        // Get selected product
        $selectedProductId = $request->product_id ?? $bom->product_id;
        $bomProduct = Products::find($selectedProductId);

        // Load existing BOM components for selected product
        if ($selectedProductId) {
            $boms = BomComponent::where('product_id', $selectedProductId)->get();
        }

        return view('admin.products.bom.edit', compact('categories', 'products', 'brands', 'bomProduct', 'boms', 'selectedCategoryId'));
    }

    public function update(Request $request, $productId)
    {
        $request->validate([
            'components.*.product_code' => 'nullable|string|max:255',
            'components.*.name' => 'required|string|max:255',
            'components.*.required_bom_qty' => 'required|numeric|min:0.01',
            'components.*.quantity' => 'nullable|numeric|min:0',
            'components.*.brand_id' => 'nullable|exists:brands,id',
            'components.*.model' => 'nullable|string|max:255',
            'components.*.notes' => 'nullable|string|max:255',
        ]);

        // Delete old components (and their stocks)
        BomComponent::where('product_id', $productId)->delete();

        foreach ($request->components as $component) {
            $bomComponent = BomComponent::create([
                'product_id' => $productId,
                'product_code' => $component['product_code'] ?? null,
                'name' => $component['name'],
                'quantity' => $component['quantity'] ?? 0,
                'required_bom_qty' => $component['required_bom_qty'],
                'brand_id' => $component['brand_id'] ?? null,
                'model' => $component['model'] ?? null,
                'notes' => $component['notes'] ?? null,
            ]);

            // ✅ Create BomStock with stock = 0
            BomStock::create([
                'bom_component_id' => $bomComponent->id,
                'available_stock' => 0,
            ]);
        }

        return redirect()->route('admin.bom.menu')->with('success', 'BOM Components updated successfully.');
    }

    public function destroy(BomComponent $bom)
    {
        // Delete its stock too
        $bom->stock()->delete();
        $bom->delete();

        return redirect()->route('admin.bom.menu')->with('success', 'BOM Component deleted successfully.');
    }

public function getProductsByCategory($categoryId)
{
    $products = Products::where('product_category_id', $categoryId)->get();
    return response()->json($products);
}
}
