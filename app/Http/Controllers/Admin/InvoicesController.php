<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS2D;

class InvoicesController extends Controller
{
    /**
     * Display all sales invoices with optional filters.
     */
    public function index(Request $request)
    {
        $sales = $this->getSalesQuery($request)->paginate(20);
        $users = User::all();

        return view('admin.invoices.sales', compact('sales', 'users'));
    }

    /**
     * Return sales table view (AJAX / partial).
     */
    public function salestable(Request $request)
    {
        $sales = $this->getSalesQuery($request)->paginate(20);
        $users = User::all();

        return view('admin.invoices.sales_table', compact('sales', 'users'));
    }

    /**
     * Return sales table view for another context (e.g., main table).
     */
    public function table(Request $request)
    {
        $sales = $this->getSalesQuery($request)->paginate(20);
        $users = User::all();

        return view('admin.invoices.table', compact('sales', 'users'));
    }

    /**
     * Display purchases with optional filters.
     */
    public function purchases(Request $request)
    {
        $purchases = $this->getPurchasesQuery($request)->paginate(20);
        $users = User::all();

        return view('admin.invoices.purchase', compact('purchases', 'users'));
    }

    /**
     * Return purchases table view (AJAX / partial).
     */
    public function purchasestable(Request $request)
    {
        $purchases = $this->getPurchasesQuery($request)->paginate(20);
        $users = User::all();

        return view('admin.invoices.purchase_table', compact('purchases', 'users'));
    }

    /**
     * Print a single purchase invoice as PDF with QR code.
     */
    public function printPurchase(Purchase $purchase)
    {
        $purchase->load(['supplier', 'products.product', 'creator']);

        $d2 = new DNS2D();
        $qrCode = $d2->getBarcodePNG(route('admin.invoices.printPurchase', $purchase->id), 'QRCODE');

        $pdf = Pdf::loadView('admin.Purchase.print', compact('purchase', 'qrCode'));

        return $pdf->stream('purchase_' . $purchase->id . '.pdf');
    }

    /**
     * Common function to build sales query with filters.
     */
    private function getSalesQuery(Request $request)
    {
        $query = Sale::with(['salesperson', 'payments.bank']);

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }
        if ($request->filled('search')) {
            $query->where('id', $request->search);
        }

        return $query->orderBy('sale_date', 'desc');
    }

    /**
     * Common function to build purchases query with filters.
     */
    private function getPurchasesQuery(Request $request)
    {
        $query = Purchase::with(['supplier', 'products.product', 'creator']);

        if ($request->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }
        if ($request->filled('search')) {
            $query->where('id', $request->search);
        }

        return $query->orderBy('purchase_date', 'desc');
    }
}
