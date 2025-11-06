<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SalePayment;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['salesperson', 'items.product', 'payments']);

        // Filters
        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->get();

        return view('admin.reports.sales', compact('sales'));
    }

    public function pdf(Request $request)
    {
        $query = Sale::with(['salesperson', 'items.product', 'payments']);

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->get();

        $pdf = \PDF::loadView('admin.reports.sales_pdf', compact('sales'));
        return $pdf->download('sales_report.pdf');
    }
}
