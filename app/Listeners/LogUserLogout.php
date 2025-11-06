<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\UserLog;

class LogUserLogout
{
    public function handle(Logout $event): void
    {
        UserLog::create([
            'user_id' => $event->user->id,
            'event' => 'logout',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
