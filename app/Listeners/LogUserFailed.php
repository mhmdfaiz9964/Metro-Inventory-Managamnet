<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Models\UserLog;

class LogUserFailed
{
    public function handle(Failed $event): void
    {
        UserLog::create([
            'user_id' => $event->user?->id,
            'event' => 'failed',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
