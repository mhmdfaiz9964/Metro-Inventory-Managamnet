<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacture;
use App\Models\ManufactureItem;
use App\Models\Products;
use App\Models\User;
use App\Models\BomComponent;
use App\Models\BomStock;
use App\Models\Stocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManufactureController extends Controller
{
    /**
     * List all manufactures.
     */
    public function index(Request $request)
    {
        $manufactures = Manufacture::with(['product', 'assignedUser', 'manufactureItems.bomComponent'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.manufactures.index', compact('manufactures'));
    }

    public function table(Request $request)
    {
        $manufactures = Manufacture::with(['product', 'assignedUser', 'manufactureItems.bomComponent'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.manufactures.table', compact('manufactures'));
    }

    /**
     * Show form to create manufacture.
     */
    public function create()
    {
        $products = Products::all();
        $users = User::all();
        $statuses = ['processing', 'manufacturing', 'testing', 'completed', 'added_to_sale'];

        return view('admin.manufactures.create', compact('products', 'users', 'statuses'));
    }

    /**
     * Store manufacture + items.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'assigned_user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:processing,manufacturing,testing,completed,added_to_sale',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:bom_components,id',
            'components.*.required_qty' => 'required|numeric|min:0.01',
            'quantity_to_produce' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $manufacture = Manufacture::create([
                'product_id' => $request->product_id,
                'assigned_user_id' => $request->assigned_user_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'quantity_to_produce' => $request->quantity_to_produce,
                'quantity_produced' => 0,
            ]);

            foreach ($request->components as $index => $component) {
                $bomStock = BomStock::firstOrNew(['bom_component_id' => $component['id']]);
                $available = $bomStock->available_stock ?? 0;

                if ($available < $component['required_qty']) {
                    throw ValidationException::withMessages([
                        "components.{$index}.required_qty" => "Insufficient stock for component. Required: {$component['required_qty']}, Available: {$available}",
                    ]);
                }

                $remaining = $available - $component['required_qty'];
                $bomStock->available_stock = $remaining;
                $bomStock->save();

                ManufactureItem::create([
                    'manufacture_id' => $manufacture->id,
                    'bom_component_id' => $component['id'],
                    'required_qty' => $component['required_qty'],
                    'issued_qty' => $component['required_qty'],
                    'consumed_qty' => 0,
                ]);
            }

            // If manufacture is immediately marked completed, add product stock
            if (in_array($manufacture->status, ['completed', 'added_to_sale'])) {
                $this->increaseProductStock($manufacture);
            }
        });

        return redirect()->route('admin.manufactures.index')->with('success', 'Manufacture created successfully.');
    }

    /**
     * Edit form.
     */
    public function edit(Manufacture $manufacture)
    {
        $products = Products::all();
        $users = User::all();
        $statuses = ['processing', 'manufacturing', 'testing', 'completed', 'added_to_sale'];

        $manufacture->load('items.bomComponent.stock');

        return view('admin.manufactures.edit', compact('manufacture', 'products', 'users', 'statuses'));
    }

    /**
     * Update manufacture + items.
     */
    public function update(Request $request, Manufacture $manufacture)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'assigned_user_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|string|in:processing,manufacturing,testing,completed,added_to_sale',
            'components' => 'required|array|min:1',
            'components.*.id' => 'required|exists:bom_components,id',
            'components.*.required_qty' => 'required|numeric|min:0.01',
            'quantity_to_produce' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $manufacture) {
            $manufacture->update([
                'product_id' => $request->product_id,
                'assigned_user_id' => $request->assigned_user_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => $request->status,
                'quantity_to_produce' => $request->quantity_to_produce,
            ]);

            // Restore old stock before deleting old items
            foreach ($manufacture->items as $oldItem) {
                $bomStock = BomStock::firstOrNew(['bom_component_id' => $oldItem->bom_component_id]);
                $bomStock->available_stock = ($bomStock->available_stock ?? 0) + $oldItem->required_qty;
                $bomStock->save();
            }

            $manufacture->items()->delete();

            foreach ($request->components as $index => $component) {
                $bomStock = BomStock::firstOrNew(['bom_component_id' => $component['id']]);
                $available = $bomStock->available_stock ?? 0;

                if ($available < $component['required_qty']) {
                    throw ValidationException::withMessages([
                        "components.{$index}.required_qty" => "Insufficient stock for component. Required: {$component['required_qty']}, Available: {$available}",
                    ]);
                }

                $remaining = $available - $component['required_qty'];
                $bomStock->available_stock = $remaining;
                $bomStock->save();

                ManufactureItem::create([
                    'manufacture_id' => $manufacture->id,
                    'bom_component_id' => $component['id'],
                    'required_qty' => $component['required_qty'],
                    'issued_qty' => $component['required_qty'],
                    'consumed_qty' => 0,
                ]);
            }

            // If status is completed/added_to_sale → add finished goods stock
            if (in_array($manufacture->status, ['completed', 'added_to_sale'])) {
                $this->increaseProductStock($manufacture);
            }
        });

        return redirect()->route('admin.manufactures.index')->with('success', 'Manufacture updated successfully.');
    }

    /**
     * Delete manufacture.
     */
    public function destroy(Manufacture $manufacture)
    {
        DB::transaction(function () use ($manufacture) {
            // Restore BOM stock before deleting
            foreach ($manufacture->items as $oldItem) {
                $bomStock = BomStock::firstOrNew(['bom_component_id' => $oldItem->bom_component_id]);
                $bomStock->available_stock = ($bomStock->available_stock ?? 0) + $oldItem->required_qty;
                $bomStock->save();
            }

            $manufacture->items()->delete();
            $manufacture->delete();
        });

        return redirect()->route('admin.manufactures.index')->with('success', 'Manufacture deleted successfully.');
    }

    /**
     * AJAX: Load BOM components for a product.
     */
    public function getProductComponents($productId)
    {
        $components = BomComponent::where('product_id', $productId)
            ->with('stock')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'price' => $c->price,
                    'required_bom_qty' => $c->required_bom_qty,
                    'available_stock' => $c->stock->available_stock ?? 0,
                ];
            });

        return response()->json($components);
    }

    /**
     * Increase finished product stock when manufacture is completed
     */
    private function increaseProductStock(Manufacture $manufacture)
    {
        $stock = Stocks::firstOrNew(['product_id' => $manufacture->product_id]);
        $stock->available_stock = ($stock->available_stock ?? 0) + $manufacture->quantity_to_produce;
        $stock->save();

        $manufacture->update([
            'quantity_produced' => $manufacture->quantity_to_produce,
        ]);
    }
}