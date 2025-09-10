<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Inertia\Inertia::share([
            'auth' => fn () => [
                'user' => auth()->user()
                    ? ['id' => auth()->id(), 'name' => auth()->user()->name]
                    : null,
            ],
        ]);
    }
}
