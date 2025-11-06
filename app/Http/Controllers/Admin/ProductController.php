<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\Supplier;
use App\Models\Stocks;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Products::with('category', 'supplier', 'stock', 'brand');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('product_brand_id', $request->brand_id);
        }

        $products = $query->latest()->paginate(10);

        $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $productBrands = ProductBrand::all();

        return view('admin.products.index', compact('products', 'categories', 'suppliers', 'productBrands'));
    }

    public function table(Request $request)
    {
        $query = Products::with('category', 'supplier', 'stock', 'brand');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('product_brand_id', $request->brand_id);
        }

        $products = $query->latest()->paginate(10);

        $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $productBrands = ProductBrand::all();

        return view('admin.products.table', compact('products', 'categories', 'suppliers', 'productBrands'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $productBrands = ProductBrand::all();

        return view('admin.products.create', compact('categories', 'suppliers', 'productBrands'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:100|unique:products',
            'product_category_id' => 'required|exists:product_categories,id',
            'product_brand_id'    => 'nullable|exists:product_brands,id',
            'model'               => 'nullable|string|max:255',
            'supplier_id'         => 'nullable|string|max:255',
            'regular_price'       => 'nullable|numeric',
            'sale_price'          => 'nullable|numeric',
            'wholesale_price'     => 'nullable|numeric',
            'weight'              => 'nullable|numeric',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'available_stock'     => 'nullable|integer|min:0',
            'stock_alert'         => 'nullable|integer|min:0',
            'status'              => 'nullable|string',
            'is_manufactured'     => 'nullable|in:yes,no',
        ]);

        $data = $request->except(['available_stock', 'stock_alert']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Products::create($data);

        Stocks::create([
            'product_id'      => $product->id,
            'available_stock' => $request->available_stock ?? 0,
            'stock_alert'     => $request->stock_alert ?? 0,
            'notes'           => null,
            'warehouse_id'    => $request->warehouse_id ?? 1,
        ]);

        return redirect()->route('admin.products.menu')->with('success', 'Product created successfully!');
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Products $product)
    {
        $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $productBrands = ProductBrand::all();

        return view('admin.products.edit', compact('product', 'categories', 'suppliers', 'productBrands'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Products $product)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => 'required|string|max:100|unique:products,code,' . $product->id,
            'product_category_id' => 'required|exists:product_categories,id',
            'product_brand_id'    => 'nullable|exists:product_brands,id',
            'model'               => 'nullable|string|max:255',
            'supplier_id'         => 'nullable|string|max:255',
            'regular_price'       => 'nullable|numeric',
            'sale_price'          => 'nullable|numeric',
            'wholesale_price'     => 'nullable|numeric',
            'weight'              => 'nullable|numeric',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'available_stock'     => 'nullable|integer|min:0',
            'stock_alert'         => 'nullable|integer|min:0',
            'status'              => 'nullable|string',
            'is_manufactured'     => 'nullable|in:yes,no',
        ]);

        $data = $request->except(['available_stock', 'stock_alert']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        Stocks::updateOrCreate(
            ['product_id' => $product->id],
            [
                'available_stock' => $request->available_stock ?? 0,
                'stock_alert'     => $request->stock_alert ?? 0,
            ]
        );

        return redirect()->route('admin.products.menu')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Products $product)
    {
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.menu')->with('success', 'Product deleted successfully!');
    }
}
