<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
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
        // Admins may do everything; for everyone else the model policies decide.
        Gate::before(fn (User $user): ?bool => $user->isAdmin() ? true : null);

        Paginator::useBootstrapFive();
    }
}
