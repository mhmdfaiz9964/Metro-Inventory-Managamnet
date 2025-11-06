<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogUserLogin;
use App\Listeners\LogUserLogout;
use App\Listeners\LogUserFailed;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogUserLogin::class,
        ],
        Logout::class => [
            LogUserLogout::class,
        ],
        Failed::class => [
            LogUserFailed::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
