<?php

namespace App\Policies;

use App\Models\Source;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SourcePolicy
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
    public function view(User $user, Source $source): bool
    {
        return in_array($user->role, ['super_admin', 'admin', 'customer_service']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Source $source): bool
    {
        return in_array($user->role, ['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Source $source): bool
    {
        return in_array($user->role, ['super_admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Source $source): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Source $source): bool
    {
        return false;
    }
}
