<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BomStockAdjustment;
use App\Models\BomStock;
use App\Models\BomComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BomStockAdjustmentController extends Controller
{
    /**
     * Display a listing of the adjustments with filters
     */
    public function index(Request $request)
    {
        $query = BomStockAdjustment::with(['bomStock.bomComponent']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bomStock.bomComponent', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('product_code', 'like', "%$search%");
            });
        }

        // Component filter
        if ($request->filled('bom_component_id')) {
            $query->whereHas('bomStock', function ($q) use ($request) {
                $q->where('bom_component_id', $request->bom_component_id);
            });
        }

        $adjustments = $query->latest()->paginate(10);
        $components = BomComponent::all();

        return view('admin.stocks.bom_adjustments.index', compact('adjustments', 'components'));
    }

    public function table(Request $request)
    {
        $query = BomStockAdjustment::with(['bomStock.bomComponent']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bomStock.bomComponent', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")->orWhere('product_code', 'like', "%$search%");
            });
        }

        // Component filter
        if ($request->filled('bom_component_id')) {
            $query->whereHas('bomStock', function ($q) use ($request) {
                $q->where('bom_component_id', $request->bom_component_id);
            });
        }

        $adjustments = $query->latest()->paginate(10);
        $components = BomComponent::all();

        return view('admin.stocks.bom_adjustments.table', compact('adjustments', 'components'));
    }

    /**
     * Show the form for creating a new adjustment
     */
    public function create()
    {
        // Make sure the variable name matches the Blade
        $stocks = BomStock::with('bomComponent')->get();
        return view('admin.stocks.bom_adjustments.create', compact('stocks'));
    }

    /**
     * Store a newly created adjustment in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'bom_stock_id' => 'required|exists:bom_stocks,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason_type' => 'required|in:damage,stock take,correction',
        ]);

        $bomStock = BomStock::findOrFail($request->bom_stock_id);

        // Apply stock adjustment
        if ($request->adjustment_type === 'increase') {
            $bomStock->available_stock += $request->quantity;
        } else {
            $bomStock->available_stock -= $request->quantity;
            if ($bomStock->available_stock < 0) {
                $bomStock->available_stock = 0;
            }
        }
        $bomStock->save();

        // Save adjustment record
        BomStockAdjustment::create([
            'bom_stock_id' => $bomStock->id,
            'adjustment_type' => $request->adjustment_type,
            'quantity' => $request->quantity,
            'reason_type' => $request->reason_type,
            'adjusted_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bom.menu')->with('success', 'BOM stock adjustment created successfully.');
    }

    /**
     * Show the form for editing an adjustment
     */
    public function edit(BomStockAdjustment $bomStockAdjustment)
    {
        $stocks = BomStock::with('bomComponent')->get();
        return view('admin.stocks.bom_adjustments.edit', compact('bomStockAdjustment', 'stocks'));
    }

    /**
     * Update the specified adjustment in storage
     */
    public function update(Request $request, BomStockAdjustment $bomStockAdjustment)
    {
        $request->validate([
            'bom_stock_id' => 'required|exists:bom_stocks,id',
            'adjustment_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason_type' => 'required|in:damage,stock take,correction',
        ]);

        $bomStock = BomStock::findOrFail($request->bom_stock_id);

        // Revert old adjustment first
        if ($bomStockAdjustment->adjustment_type === 'increase') {
            $bomStock->available_stock -= $bomStockAdjustment->quantity;
        } else {
            $bomStock->available_stock += $bomStockAdjustment->quantity;
        }

        // Apply new adjustment
        if ($request->adjustment_type === 'increase') {
            $bomStock->available_stock += $request->quantity;
        } else {
            $bomStock->available_stock -= $request->quantity;
            if ($bomStock->available_stock < 0) {
                $bomStock->available_stock = 0;
            }
        }

        $bomStock->save();

        // Update adjustment record
        $bomStockAdjustment->update([
            'bom_stock_id' => $bomStock->id,
            'adjustment_type' => $request->adjustment_type,
            'quantity' => $request->quantity,
            'reason_type' => $request->reason_type,
            'adjusted_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bom.menu')->with('success', 'BOM stock adjustment updated successfully.');
    }

    /**
     * Remove the specified adjustment from storage (optional)
     */
    public function destroy(BomStockAdjustment $bomStockAdjustment)
    {
        $bomStock = $bomStockAdjustment->bomStock;

        // Revert stock before deleting adjustment
        if ($bomStockAdjustment->adjustment_type === 'increase') {
            $bomStock->available_stock -= $bomStockAdjustment->quantity;
        } else {
            $bomStock->available_stock += $bomStockAdjustment->quantity;
        }

        if ($bomStock->available_stock < 0) {
            $bomStock->available_stock = 0;
        }

        $bomStock->save();

        $bomStockAdjustment->delete();

        return redirect()->route('admin.bom.menu')->with('success', 'BOM stock adjustment deleted and stock reverted.');
    }
}
