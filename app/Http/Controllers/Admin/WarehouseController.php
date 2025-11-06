<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the warehouses.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $warehouses = Warehouse::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%");
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return view('admin.warehouses.index', compact('warehouses', 'search'));
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'    => 'required|string|max:255',
                'code'    => 'nullable|string|max:100|unique:warehouses,code',
                'type'    => 'required|in:warehouse,store,factory,service_center',
                'address' => 'nullable|string|max:500',
                'status'  => 'boolean',
            ]);

            $validated['created_by'] = Auth::id();

            Warehouse::create($validated);

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Failed to create warehouse. ' . $e->getMessage());
        }
    }

    /**
     * Update the specified warehouse in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            $validated = $request->validate([
                'name'    => 'required|string|max:255',
                'code'    => 'nullable|string|max:100|unique:warehouses,code,' . $warehouse->id,
                'type'    => 'required|in:warehouse,store,factory,service_center',
                'address' => 'nullable|string|max:500',
                'status'  => 'boolean',
            ]);

            $warehouse->update($validated);

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Failed to update warehouse. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified warehouse from storage.
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();

            return redirect()->route('admin.warehouses.index')
                ->with('success', 'Warehouse deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Failed to delete warehouse. ' . $e->getMessage());
        }
    }
}
