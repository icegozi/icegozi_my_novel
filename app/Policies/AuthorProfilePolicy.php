<?php

namespace App\Policies;

use App\Models\AuthorProfile;
use App\Models\User;

class AuthorProfilePolicy
{
    /**
     * Determine whether the user can view any author profiles in Admin CMS.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can view a specific author profile.
     */
    public function view(User $user, AuthorProfile $profile): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->id === $profile->user_id;
    }

    /**
     * Determine whether the user can review/approve/reject an e-KYC profile.
     */
    public function review(User $user, AuthorProfile $profile): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can update an author profile in Admin CMS.
     */
    public function update(User $user, AuthorProfile $profile): bool
    {
        return $user->role === 'admin';
    }
}
