<?php

namespace Happytodev\Blogr\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny($user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view($user, $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create($user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update($user, $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete($user, $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore($user, $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete($user, $model): bool
    {
        return $user->hasRole('admin');
    }
}
