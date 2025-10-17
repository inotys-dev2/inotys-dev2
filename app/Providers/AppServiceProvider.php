<?php

namespace App\Providers;

use App\Listeners\MarkSessionWithUser;
use App\Models\DemandeCeremonie;
use App\Policies\DemandeCeremoniePolicies;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\RateLimiter;          // ✅ le Facade
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $policies = [
        DemandeCeremonie::class => DemandeCeremoniePolicies::class,
    ];

    protected $listen = [
        Login::class => [
            MarkSessionWithUser::class,
        ],
    ];

    public const string HOME = '/dashboard';


    public function boot()
    {
        RateLimiter::for('ceremony', function (Request $request) {
            return [ Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip()) ];
        });

        Blade::component('layouts.app', App::class);

        Blade::directive('activeClass', function ($route) {
            return "<?php echo request()->routeIs({$route}) ? 'active' : ''; ?>";
        });
    }
}
