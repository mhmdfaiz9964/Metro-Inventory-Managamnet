<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SaleReturn;
use App\Models\Sale;
use App\Models\SaleReturnItem;
use App\Models\Products;
use Illuminate\Http\Request;

class SaleReturnController extends Controller
{
    public function index()
    {
        $saleReturns = SaleReturn::with(['sale', 'customer'])
            ->latest()
            ->paginate(10);
        return view('admin.sales.return.index', compact('saleReturns'));
    }

    public function table()
    {
        $saleReturns = SaleReturn::with(['sale', 'customer'])
            ->latest()
            ->paginate(10);
        return view('admin.sales.return.table', compact('saleReturns'));
    }

    public function create()
    {
        $sales = Sale::with('items.product')->get();
        $action = route('admin.sales-returns.store');
        $buttonText = 'Save';
        return view('admin.sales.return.create', compact('sales', 'action', 'buttonText'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        $sale = Sale::find($request->sale_id);

        $saleReturn = SaleReturn::create([
            'sale_id' => $request->sale_id,
            'customer_id' => $sale->customer_id,
            'return_date' => $request->return_date,
            'reason' => $request->reason,
            'total_amount' => 0, // Will calculate
        ]);

        $totalAmount = 0;

        foreach ($request->items as $item) {
            $product = Products::find($item['product_id']);
            $total = $item['quantity'] * $product->sale_price;

            SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'sale_price' => $product->sale_price,
                'total' => $total,
            ]);

            $totalAmount += $total;
        }

        $saleReturn->update(['total_amount' => $totalAmount]);

        return redirect()->route('admin.sales-returns.index')->with('success', 'Sale return created successfully.');
    }

    public function edit(SaleReturn $saleReturn)
    {
        $sales = Sale::with('items.product')->get();
        $action = route('admin.sales-returns.update', $saleReturn->id);
        $method = 'PUT';
        $buttonText = 'Update';
        return view('admin.sales.return.edit', compact('sales', 'saleReturn', 'action', 'method', 'buttonText'));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        $sale = Sale::find($request->sale_id);

        $saleReturn->update([
            'sale_id' => $request->sale_id,
            'customer_id' => $sale->customer_id,
            'return_date' => $request->return_date,
            'reason' => $request->reason,
        ]);

        $saleReturn->items()->delete();

        $totalAmount = 0;

        foreach ($request->items as $item) {
            $product = Products::find($item['product_id']);
            $total = $item['quantity'] * $product->sale_price;

            SaleReturnItem::create([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'sale_price' => $product->sale_price,
                'total' => $total,
            ]);

            $totalAmount += $total;
        }

        $saleReturn->update(['total_amount' => $totalAmount]);

        return redirect()->route('admin.sales-returns.index')->with('success', 'Sale return updated successfully.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        $saleReturn->delete();
        return redirect()->route('admin.sales-returns.index')->with('success', 'Sale return deleted successfully.');
    }
}