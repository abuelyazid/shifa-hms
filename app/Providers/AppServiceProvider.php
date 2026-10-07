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
        // نفس فحص الصلاحيات يتطبق على طلبات Livewire اللي بعد فتح الصفحة
        \Livewire\Livewire::addPersistentMiddleware([\App\Http\Middleware\EnsureRole::class]);
    }
}
