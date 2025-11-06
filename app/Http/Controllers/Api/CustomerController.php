<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // List all customers
    public function index()
    {
        $customers = Customer::all(); // Get all customers

        return response()->json([
            'success' => true,
            'data' => $customers
        ], 200);
    }

    // Store new customer
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'mobile_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'balance_due' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        // Validation fail
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create customer
        $customer = Customer::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }
}
