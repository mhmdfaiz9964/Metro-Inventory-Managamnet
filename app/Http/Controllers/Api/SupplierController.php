<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * List all suppliers
     */
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Store a new supplier
     */
    public function store(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'balance_due' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create supplier
            $supplier = Supplier::create($request->only([
                'name', 'mobile_number', 'email', 'notes', 'status', 'balance_due', 'total_paid'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully',
                'data' => $supplier
            ], 201);
        } catch (\Exception $e) {
            // Log for debugging
            \Log::error('Supplier creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the supplier.'
            ], 500);
        }
    }
}