<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReceivingCheque;
use App\Models\Cheques;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Cheques that are 1-2 days before due
        $upcomingCheques = Cheques::where('status', '!=', 'approved')
            ->whereBetween('cheque_date', [$today, $today->copy()->addDays(2)])
            ->get();

        $upcomingReceivings = ReceivingCheque::where('status', '!=', 'paid')
            ->whereBetween('cheque_date', [$today, $today->copy()->addDays(2)])
            ->get();

        return view('dashboard', compact('upcomingCheques', 'upcomingReceivings'));
    }
}
