<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cheques;
use App\Models\ReceivingCheque;
use Carbon\Carbon;

class CheckChequesNotification extends Command
{
    protected $signature = 'check:cheques-notification';
    protected $description = 'Check cheques and receiving cheques for upcoming dates';

    public function handle()
    {
        $today = Carbon::today();
        $twoDaysBefore = $today->copy()->addDays(2);
        $oneDayBefore = $today->copy()->addDay();

        // Outgoing cheques
        $cheques = Cheques::whereIn('status', ['pending', 'draft'])
            ->whereDate('cheque_date', [$oneDayBefore, $twoDaysBefore])
            ->get();

        // Receiving cheques
        $receivingCheques = ReceivingCheque::where('status', 'pending')
            ->whereDate('cheque_date', [$oneDayBefore, $twoDaysBefore])
            ->get();

        return ['cheques' => $cheques, 'receiving' => $receivingCheques];
    }
}
