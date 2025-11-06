<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\UserLog;

class LogUserLogin
{
    public function handle(Login $event): void
    {
        UserLog::create([
            'user_id' => $event->user->id,
            'event' => 'login',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
