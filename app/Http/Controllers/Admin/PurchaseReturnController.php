<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Supplier;
use App\Models\BomComponent;
use App\Models\BomStock;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index()
    {
        $purchaseReturns = PurchaseReturn::with(['supplier', 'items.bomComponent'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.purchase.return.index', compact('purchaseReturns'));
    }

    public function table()
    {
        $purchaseReturns = PurchaseReturn::with(['supplier', 'items.bomComponent'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.purchase.return.table', compact('purchaseReturns'));
    }

    public function create()
    {
        $purchases = Purchase::with('items.bomComponent', 'supplier')->get();
        $allBoms = BomComponent::select('id', 'name')->get();
        $action = route('admin.purchase-returns.store');
        $buttonText = 'Save Return';
        return view('admin.purchase.return.create', compact('purchases', 'allBoms', 'action', 'buttonText'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items.*.bom_component_id' => 'required|exists:bom_components,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $purchase = Purchase::findOrFail($request->purchase_id);

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $bomComponent = BomComponent::findOrFail($item['bom_component_id']);
                $lineTotal = $bomComponent->price * $item['quantity'];
                $totalAmount += $lineTotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'bom_component_id' => $bomComponent->id,
                    'quantity' => $item['quantity'],
                    'total_amount' => $bomComponent->price,
                    'total' => $lineTotal,
                ]);

                // Update stock
                $bomStock = BomStock::firstOrNew(['bom_component_id' => $bomComponent->id]);
                $bomStock->available_stock = max(($bomStock->available_stock ?? 0) - $item['quantity'], 0);
                $bomStock->save();
            }

            $purchaseReturn->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.purchase-returns.index')->with('success', 'Purchase return created successfully.');
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load('items');
        $purchases = Purchase::with('items.bomComponent', 'supplier')->get();
        $allBoms = BomComponent::select('id', 'name')->get();
        $action = route('admin.purchase-returns.update', $purchaseReturn);
        $method = 'PUT';
        $buttonText = 'Update Return';
        return view('admin.purchase.return.edit', compact('purchaseReturn', 'purchases', 'allBoms', 'action', 'method', 'buttonText'));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items.*.bom_component_id' => 'required|exists:bom_components,id',
            'items.*.quantity' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseReturn) {
            $purchase = Purchase::findOrFail($request->purchase_id);

            // Restore previous stock
            foreach ($purchaseReturn->items as $oldItem) {
                $stock = BomStock::firstOrNew(['bom_component_id' => $oldItem->bom_component_id]);
                $stock->available_stock = ($stock->available_stock ?? 0) + $oldItem->quantity;
                $stock->save();
            }

            $purchaseReturn->update([
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'total_amount' => 0,
            ]);

            $purchaseReturn->items()->delete();

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $bomComponent = BomComponent::findOrFail($item['bom_component_id']);
                $lineTotal = $bomComponent->price * $item['quantity'];
                $totalAmount += $lineTotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'bom_component_id' => $bomComponent->id,
                    'quantity' => $item['quantity'],
                    'total_amount' => $bomComponent->price,
                    'total' => $lineTotal,
                ]);

                $bomStock = BomStock::firstOrNew(['bom_component_id' => $bomComponent->id]);
                $bomStock->available_stock = max(($bomStock->available_stock ?? 0) - $item['quantity'], 0);
                $bomStock->save();
            }

            $purchaseReturn->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('admin.purchase-returns.index')->with('success', 'Purchase return updated successfully.');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {
            foreach ($purchaseReturn->items as $item) {
                $stock = BomStock::firstOrNew(['bom_component_id' => $item->bom_component_id]);
                $stock->available_stock = ($stock->available_stock ?? 0) + $item->quantity;
                $stock->save();
            }
            $purchaseReturn->delete();
        });

        return redirect()->route('admin.purchase-returns.index')->with('success', 'Purchase return deleted successfully.');
    }
}