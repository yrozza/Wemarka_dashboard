<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CartPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cart $cart): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cart $cart): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    public function checkout(User $user)
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Cart $cart): bool
    {
        return in_array($user->role, ['super_admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Cart $cart): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Cart $cart): bool
    {
        return false;
    }
}
