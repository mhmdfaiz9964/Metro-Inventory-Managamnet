<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stocks;
use App\Models\Products;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StocksController extends Controller
{
    /**
     * Display a listing of stocks with search and filter.
     */
    public function index(Request $request)
    {
        $query = Stocks::with(['product', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $stocks = $query->latest()->paginate(10);
        $products = Products::all();
        $warehouses = Warehouse::all();

        return view('admin.stocks.index', compact('stocks', 'products', 'warehouses'));
    }

    /**
     * Show form to create a new stock record
     */
    public function create()
    {
        $products = Products::all();
        $warehouses = Warehouse::all();
        return view('admin.stocks.create', compact('products', 'warehouses'));
    }

    /**
     * Store a newly created stock record
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id|unique:stocks,product_id,NULL,id,warehouse_id,' . $request->warehouse_id,
            'warehouse_id' => 'required|exists:warehouses,id',
            'available_stock' => 'required|numeric|min:0',
            'stock_alert' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        Stocks::create($request->all());

        return redirect()->route('admin.stocks.index')
            ->with('success', 'Stock record created successfully!');
    }

    /**
     * Show form to edit an existing stock record
     */
    public function edit(Stocks $stock)
    {
        $products = Products::all();
        $warehouses = Warehouse::all();
        return view('admin.stocks.edit', compact('stock', 'products', 'warehouses'));
    }

    /**
     * Update an existing stock record
     */
    public function update(Request $request, Stocks $stock)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id|unique:stocks,product_id,' . $stock->id . ',id,warehouse_id,' . $request->warehouse_id,
            'warehouse_id' => 'required|exists:warehouses,id',
            'available_stock' => 'required|numeric|min:0',
            'stock_alert' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $stock->update($request->all());

        return redirect()->route('admin.stocks.index')
            ->with('success', 'Stock record updated successfully!');
    }

    /**
     * Delete a stock record
     */
    public function destroy(Stocks $stock)
    {
        $stock->delete();

        return redirect()->route('admin.stocks.index')
            ->with('success', 'Stock record deleted successfully!');
    }
}
