<?php

namespace App\Policies;

use App\Models\Novel;
use App\Models\User;

class NovelPolicy
{
    /**
     * Determine whether the user can view any novels in Admin CMS.
     */
    public function viewAnyAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can create a novel via Admin CMS.
     */
    public function createAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update a novel in Admin CMS.
     */
    public function updateAdmin(User $user, Novel $novel): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can delete a novel in Admin CMS.
     */
    public function deleteAdmin(User $user, Novel $novel): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can manage (author or admin) a specific novel.
     */
    public function manage(User $user, Novel $novel): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'author' && $novel->author_id === $user->id;
    }
}
