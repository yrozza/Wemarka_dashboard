<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Cart;
use App\Policies\CartPolicy;
use App\Policies\CartItemPolicy;
use App\Models\User;
use App\Models\Area;
use App\Models\City;
use App\Models\Employee;
use App\Models\Shipping;
use App\Models\Source;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Supplier;
use App\Policies\AreaPolicy;
use App\Policies\BrandPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\CityPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\OrderPolicy;
use App\Policies\ShippingCompanyPolicy;
use App\Policies\SourcePolicy;
use App\Policies\SupplierPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => EmployeePolicy::class, 
        City::class => CityPolicy::class,
        Brand::class=>BrandPolicy::class,
        Category::class=>CategoryPolicy::class,
        CartItem::class => CartItemPolicy::class,
        Cart::class => CartPolicy::class,
        Order::class => OrderPolicy::class,
        Source::class => SourcePolicy::class,
        Shipping::class => ShippingCompanyPolicy::class,
        Supplier::class=>SupplierPolicy::class,
        Area::class => AreaPolicy::class,
    ];

    public function boot(): void
    {
        // Register product policy
        Gate::policy(Product::class, ProductPolicy::class);




        Gate::before(function (User $user,string $ability) {
            if ($user->role === 'super_admin') {
                return true; // Super Admin bypasses all checks
            }

            // Admin can manage most areas but not assign roles or delete users
            if ($user->role === 'admin' && in_array($ability, ['viewAny', 'create', 'update'])) {
                return true;
            }

            return null; // Use policy methods for other checks
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
