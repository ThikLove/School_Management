<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

/**
 * Teachers see the subjects they teach; students see the subjects of their class.
 */
class SubjectPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * The controller limits the list to the user's own subjects.
     */
    public function viewAny(User $user): bool
    {
        return ($user->isTeacher() && $user->teacher !== null)
            || ($user->isStudent() && $user->student !== null);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Subject $subject): bool
    {
        if ($user->isTeacher() && $user->teacher !== null) {
            return $subject->teachers()->whereKey($user->teacher->id)->exists();
        }

        if ($user->isStudent() && $user->student !== null) {
            return $subject->classes()->whereKey($user->student->class_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subject $subject): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subject $subject): bool
    {
        return false;
    }
}
