<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Products;
use App\Models\Stocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * List all transfers
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $transfers = Transfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('fromWarehouse', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('toWarehouse', fn($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('admin.tog.index', compact('transfers', 'search'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $products = Products::all();
        return view('admin.tog.create', compact('products'));
    }

    /**
     * Store transfer
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request) {
            $transfer = Transfer::create([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'status' => 'completed',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->products as $item) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Update stocks
                $fromStock = Stocks::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->from_warehouse_id,
                ]);
                $toStock = Stocks::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->to_warehouse_id,
                ]);

                $fromStock->available_stock -= $item['quantity'];
                $fromStock->save();

                $toStock->available_stock += $item['quantity'];
                $toStock->save();
            }
        });

        return redirect()->route('admin.transfers.index')
            ->with('success', 'Transfer created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $transfer = Transfer::with('items')->findOrFail($id);
        $products = Products::all();
        return view('admin.tog.edit', compact('transfer', 'products'));
    }

    /**
     * Update transfer
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'transfer_date' => 'required|date',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($request, $id) {
            $transfer = Transfer::with('items')->findOrFail($id);

            // Reverse previous stocks
            foreach ($transfer->items as $item) {
                $fromStock = Stocks::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                ]);
                $toStock = Stocks::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                ]);

                $fromStock->available_stock += $item->quantity;
                $fromStock->save();

                $toStock->available_stock -= $item->quantity;
                $toStock->save();
            }

            // Delete old items
            $transfer->items()->delete();

            // Update transfer
            $transfer->update([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
            ]);

            // Add new items and update stocks
            foreach ($request->products as $item) {
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);

                $fromStock = Stocks::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->from_warehouse_id,
                ]);
                $toStock = Stocks::firstOrCreate([
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $request->to_warehouse_id,
                ]);

                $fromStock->available_stock -= $item['quantity'];
                $fromStock->save();

                $toStock->available_stock += $item['quantity'];
                $toStock->save();
            }
        });

        return redirect()->route('admin.transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    /**
     * Delete transfer
     */
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $transfer = Transfer::with('items')->findOrFail($id);

            // Reverse stock
            foreach ($transfer->items as $item) {
                $fromStock = Stocks::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                ]);
                $toStock = Stocks::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                ]);

                $fromStock->available_stock += $item->quantity;
                $fromStock->save();

                $toStock->available_stock -= $item->quantity;
                $toStock->save();
            }

            $transfer->items()->delete();
            $transfer->delete();
        });

        return redirect()->route('admin.transfers.index')
            ->with('success', 'Transfer deleted successfully.');
    }
}
