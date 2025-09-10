<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                $u = auth()->user();
                return $u ? [
                    'user' => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'roles' => $u->getRoleNames(),
                    ],
                ] : null;
            },
        ]);
    }
}
