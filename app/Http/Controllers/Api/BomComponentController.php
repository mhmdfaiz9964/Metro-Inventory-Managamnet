<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BomComponent;
use App\Models\BomStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BomComponentController extends Controller
{
    /**
     * List all BOM components, optionally filter by product_id
     */
    public function index(Request $request)
    {
        $query = BomComponent::query();

        // Optional filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $bomComponents = $query->with(['product', 'brand'])->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $bomComponents
        ], 200);
    }

    /**
     * Store a new BOM component
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'required_bom_qty' => 'required|numeric|min:1',
            'product_code' => 'required|string|max:50|unique:bom_components,product_code',
            'brand_id' => 'nullable|exists:brands,id',
            'model' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'qty_alert' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Set defaults for price and quantity if not provided
        $data = $request->only([
            'product_id',
            'name',
            'required_bom_qty',
            'product_code',
            'brand_id',
            'model',
            'price',
            'quantity',
            'qty_alert',
            'notes'
        ]);
        $data['price'] = $data['price'] ?? 0;
        $data['quantity'] = $data['quantity'] ?? 0;

        $bom = BomComponent::create($data);

        // Automatically create BomStock with available_stock = 0
        BomStock::create([
            'bom_component_id' => $bom->id,
            'available_stock' => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'BOM Component created successfully',
            'data' => $bom
        ], 201);
    }
}