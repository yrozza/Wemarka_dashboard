<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Super Admin & Admin can do everything
        Gate::define('manage-dashboard', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin']);
        });

        // Customer Service can manage orders
        Gate::define('manage-orders', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
        });

        // Warehouse can only manage products
        Gate::define('manage-products', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin', 'warehouse']);
        });
        Gate::define('view-analytics', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin', 'data_analyst']);
        });

        Gate::define('view-orders', function (User $user) {
            return in_array($user->role, ['super_admin', 'admin', 'customer_service', 'data_analyst']);
        });
    }
}
