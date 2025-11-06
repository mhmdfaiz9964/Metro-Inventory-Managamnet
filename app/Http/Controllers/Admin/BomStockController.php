<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BomStock;
use Illuminate\Http\Request;

class BomStockController extends Controller
{
    /**
     * Display a listing of BOM stocks.
     */
    public function index(Request $request)
    {
        $query = BomStock::with('bomComponent.product');

        // Optional search by component name or product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bomComponent', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $stocks = $query->orderBy('id', 'desc')->get();

        return view('admin.stocks.bom', compact('stocks'));
    }
        public function table(Request $request)
    {
        $query = BomStock::with('bomComponent.product');

        // Optional search by component name or product name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('bomComponent', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $stocks = $query->orderBy('id', 'desc')->get();

        return view('admin.stocks.bom_table', compact('stocks'));
    }
}
