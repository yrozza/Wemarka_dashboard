<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register product policy
        Gate::policy(Product::class, ProductPolicy::class);

        // Super Admin can bypass all Gates
        Gate::before(function (User $user) {
            return $user->role === 'super_admin' ? true : null;
        });

        // Admins can manage everything
        Gate::define('manage-dashboard', fn(User $user) => $user->hasRole(['admin']));

        // Customer Service can manage orders
        Gate::define('manage-orders', fn(User $user) => $user->hasRole(['admin', 'customer_service']));

        // Warehouse staff can manage products
        Gate::define('manage-products', fn(User $user) => $user->hasRole(['admin', 'warehouse']));

        // Data Analysts can view analytics
        Gate::define('view-analytics', fn(User $user) => $user->hasRole(['admin', 'data_analyst']));

        // Orders are visible to multiple roles
        Gate::define('view-orders', fn(User $user) => $user->hasRole(['admin', 'customer_service', 'data_analyst']));
    }
}
