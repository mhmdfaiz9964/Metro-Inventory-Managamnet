<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseBomProduct;
use App\Models\Supplier;
use App\Models\Products;
use App\Models\BomComponent;

class PurchaseReportController extends Controller
{
    /**
     * Display the purchase report (web view)
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'bomProducts.bomComponent']);

        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->get();

        $suppliers = Supplier::all();
        $products = Products::all();

        return view('admin.reports.purchase_report', compact('purchases', 'suppliers', 'products'));
    }

    /**
     * Generate PDF report
     */
    public function pdf(Request $request)
    {
        $query = Purchase::with(['supplier', 'bomProducts.bomComponent']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }

        $purchases = $query->get();

        $pdf = \PDF::loadView('admin.reports.purchase_pdf', compact('purchases'));
        return $pdf->download('purchase_report.pdf');
    }

    /**
     * Fetch BOM components dynamically for a product (AJAX)
     */
    public function getBomComponents($productId)
    {
        $components = BomComponent::where('product_id', $productId)->get(['id', 'name', 'price']);
        return response()->json($components);
    }
}
