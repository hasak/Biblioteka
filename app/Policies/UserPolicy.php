<?php

namespace App\Policies;

use App\Models\User;

/**
 * Panel access is already gated by User::canAccessPanel(), so every admin may
 * manage users. This policy only closes the two ways an admin could lock
 * everybody out of the panel.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->hasRole('admin')) {
            return false;
        }

        // Deleting yourself logs you out of an account you may be the only
        // owner of, and removing the last admin leaves nobody who can log in.
        return ! $user->is($model) && ! $model->isLastAdmin();
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }
}
