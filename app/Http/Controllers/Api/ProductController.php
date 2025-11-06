<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Products;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Products::with(['category', 'stock', 'supplier', 'brand']);

        if ($request->has('category_id')) {
            $query->where('product_category_id', $request->category_id);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate(15);

        $transformedData = $products->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'supplier' => $product->supplier ? [
                    'id' => $product->supplier->id,
                    'name' => $product->supplier->name,
                ] : null,
                'status' => $product->status,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'description' => $product->description,
                'regular_price' => $product->regular_price,
                'wholesale_price' => $product->wholesale_price,
                'sale_price' => $product->sale_price,
                'warranty' => $product->warranty,
                'weight' => $product->weight,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                ] : null,
                'model' => $product->model,
                'is_manufactured' => $product->is_manufactured,
                'stock' => $product->stock ? [
                    'quantity' => $product->stock->quantity,
                ] : [
                    'quantity' => 0,
                ],
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'data' => $transformedData,
            'links' => [
                'first' => $products->url(1),
                'last' => $products->lastPageUrl(),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            'filters' => $request->only(['category_id', 'supplier_id', 'status']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:products,code',
                'product_category_id' => 'required|exists:product_categories,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'status' => 'required|in:active,inactive,draft',
                'image' => 'nullable|image|max:2048',
                'description' => 'nullable|string',
                'regular_price' => 'required|numeric|min:0',
                'wholesale_price' => 'nullable|numeric|min:0',
                'sale_price' => 'nullable|numeric|min:0',
                'warranty' => 'required|in:No warranty,1 month,3 months,6 months,12 months,24 months',
                'weight' => 'nullable|numeric|min:0',
                'product_brand_id' => 'nullable|exists:product_brands,id',
                'model' => 'nullable|string|max:255',
                'is_manufactured' => 'nullable|in:yes,no',
            ]);

            // Default is_manufactured to 'yes' if not provided
            $validated['is_manufactured'] = $validated['is_manufactured'] ?? 'yes';

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('products', 'public');
            }

            $product = Products::create($validated);
            $product->load(['category', 'stock', 'supplier', 'brand']);

            $data = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'supplier' => $product->supplier ? [
                    'id' => $product->supplier->id,
                    'name' => $product->supplier->name,
                ] : null,
                'status' => $product->status,
                'image' => $product->image ? asset('storage/' . $product->image) : null,
                'description' => $product->description,
                'regular_price' => $product->regular_price,
                'wholesale_price' => $product->wholesale_price,
                'sale_price' => $product->sale_price,
                'warranty' => $product->warranty,
                'weight' => $product->weight,
                'brand' => $product->brand ? [
                    'id' => $product->brand->id,
                    'name' => $product->brand->name,
                ] : null,
                'model' => $product->model,
                'is_manufactured' => $product->is_manufactured,
                'stock' => $product->stock ? [
                    'quantity' => $product->stock->quantity,
                ] : [
                    'quantity' => 0,
                ],
                'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $product->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $data
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }
    }
}
