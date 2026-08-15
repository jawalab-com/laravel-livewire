<?php

namespace App\Policies;

use App\Enums\Permission as AppPermission;
use App\Models\User;

class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($ability === 'delete') {
            return null;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(AppPermission::ViewUsers->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(AppPermission::ViewUsers->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(AppPermission::CreateUsers->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->can(AppPermission::EditUsers->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete their own account from admin user management
        if ($user->id === $model->id) {
            return false;
        }

        // Prevent deleting the last Super Admin
        if ($model->hasRole('Super Admin') && User::role('Super Admin')->count() <= 1) {
            return false;
        }

        return $user->can(AppPermission::DeleteUsers->value);
    }
}
