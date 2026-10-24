<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users in Admin CMS.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can view a specific user.
     */
    public function view(User $user, User $model): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can update a specific user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can change status (suspend/ban) of a user.
     */
    public function changeStatus(User $user, User $targetUser): bool
    {
        // Only admin can ban/suspend, and cannot ban self
        return $user->role === 'admin' && $user->id !== $targetUser->id;
    }
}
