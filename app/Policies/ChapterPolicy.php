<?php

namespace App\Policies;

use App\Models\Chapter;
use App\Models\User;

class ChapterPolicy
{
    /**
     * Determine whether the user can view any chapters in Admin CMS.
     */
    public function viewAnyAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can create a chapter in Admin CMS.
     */
    public function createAdmin(User $user): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can update a chapter in Admin CMS.
     */
    public function updateAdmin(User $user, Chapter $chapter): bool
    {
        return in_array($user->role, ['admin', 'moderator']);
    }

    /**
     * Determine whether the user can delete a chapter in Admin CMS.
     */
    public function deleteAdmin(User $user, Chapter $chapter): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the revision history of a chapter.
     */
    public function viewHistory(User $user, Chapter $chapter): bool
    {
        if (in_array($user->role, ['admin', 'moderator'])) {
            return true;
        }

        return $user->role === 'author' && $chapter->novel && $chapter->novel->author_id === $user->id;
    }

    /**
     * Determine whether the user can restore a previous chapter revision.
     */
    public function restoreHistory(User $user, Chapter $chapter): bool
    {
        return $user->role === 'admin';
    }
}
