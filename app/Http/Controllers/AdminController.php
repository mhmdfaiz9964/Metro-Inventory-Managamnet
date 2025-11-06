<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cheques;
use App\Models\ReceivingCheque;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized access');
        }

        $today = Carbon::today();

        // Cheques 1-2 days before due date
        $upcomingCheques = Cheques::where('status', '!=', 'approved')
            ->whereBetween('cheque_date', [$today, $today->copy()->addDays(2)])
            ->get();

        // Receiving cheques 1-2 days before cheque_date
        $upcomingReceivings = ReceivingCheque::where('status', '!=', 'paid')
            ->whereBetween('cheque_date', [$today, $today->copy()->addDays(2)])
            ->get();

        return view('admin.dashboard', compact('upcomingCheques', 'upcomingReceivings'));
    }
}
