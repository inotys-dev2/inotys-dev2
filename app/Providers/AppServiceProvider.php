<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\MarkSessionWithUser::class,
        ],
    ];

    public const string HOME = '/dashboard';


    public function boot()
    {
        Blade::component('layouts.app', App::class);

        Blade::directive('activeClass', function ($route) {
            return "<?php echo request()->routeIs({$route}) ? 'active' : ''; ?>";
        });
    }
}
