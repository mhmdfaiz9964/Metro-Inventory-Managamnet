<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Stocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustments with search and filter.
     */
    public function index(Request $request)
    {
        $query = StockAdjustment::with('stock.product', 'adjustedByUser');

        // Search by product name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('stock.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by adjustment type
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        // Filter by reason type
        if ($request->filled('reason_type')) {
            $query->where('reason_type', $request->reason_type);
        }

        $adjustments = $query->latest()->paginate(10);
        $stocks = Stocks::with('product')->get();

        return view('admin.stocks.adjustments.index', compact('adjustments', 'stocks'));
    }

    public function table(Request $request)
    {
        $query = StockAdjustment::with('stock.product', 'adjustedByUser');

        // Search by product name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('stock.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by adjustment type
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }

        // Filter by reason type
        if ($request->filled('reason_type')) {
            $query->where('reason_type', $request->reason_type);
        }

        $adjustments = $query->latest()->paginate(10);
        $stocks = Stocks::with('product')->get();

        return view('admin.stocks.adjustments.table', compact('adjustments', 'stocks'));
    }

    /**
     * Show the form for creating a new adjustment.
     */
    public function create()
    {
        $stocks = Stocks::with('product')->get();
        return view('admin.stocks.adjustments.create', compact('stocks'));
    }

    /**
     * Store a newly created adjustment in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason_type' => 'required|in:damage,stock take,correction',
        ]);

        $data = $request->all();
        $data['adjusted_by'] = Auth::id();

        // Create the stock adjustment
        $adjustment = StockAdjustment::create($data);

        // Update stock quantity
        $stock = Stocks::find($data['stock_id']);
        if ($data['adjustment_type'] === 'increase') {
            $stock->available_stock += $data['quantity'];
        } else {
            $stock->available_stock -= $data['quantity'];
            if ($stock->available_stock < 0) $stock->available_stock = 0;
        }
        $stock->save();

        return redirect()->route('admin.stock-adjustments.index')
                         ->with('success', 'Stock adjustment created successfully!');
    }

    /**
     * Show the form for editing an adjustment.
     */
    public function edit(StockAdjustment $stockAdjustment)
    {
        $stocks = Stocks::with('product')->get();
        return view('admin.stocks.adjustments.edit', compact('stockAdjustment', 'stocks'));
    }

    /**
     * Update the specified adjustment.
     */
    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $request->validate([
            'stock_id' => 'required|exists:stocks,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason_type' => 'required|in:damage,stock take,correction',
        ]);

        // Reverse previous adjustment
        $oldStock = Stocks::find($stockAdjustment->stock_id);
        if ($stockAdjustment->adjustment_type === 'increase') {
            $oldStock->available_stock -= $stockAdjustment->quantity;
        } else {
            $oldStock->available_stock += $stockAdjustment->quantity;
        }
        $oldStock->save();

        $data = $request->all();
        $data['adjusted_by'] = Auth::id();

        $stockAdjustment->update($data);

        // Apply new adjustment
        $newStock = Stocks::find($data['stock_id']);
        if ($data['adjustment_type'] === 'increase') {
            $newStock->available_stock += $data['quantity'];
        } else {
            $newStock->available_stock -= $data['quantity'];
            if ($newStock->available_stock < 0) $newStock->available_stock = 0;
        }
        $newStock->save();

        return redirect()->route('admin.stock-adjustments.index')
                         ->with('success', 'Stock adjustment updated successfully!');
    }
}
