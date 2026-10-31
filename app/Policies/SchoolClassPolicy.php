<?php

namespace App\Policies;

use App\Models\SchoolClass;
use App\Models\User;

/**
 * Teachers see the classes they supervise or teach; students see their own class.
 */
class SchoolClassPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * Teachers get a list limited to their own classes by the controller.
     */
    public function viewAny(User $user): bool
    {
        return $user->isTeacher() && $user->teacher !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchoolClass $schoolClass): bool
    {
        if ($user->isTeacher()) {
            return (bool) $user->teacher?->canAccessClass($schoolClass->id);
        }

        return $user->isStudent() && $user->student?->class_id === $schoolClass->id;
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
    public function update(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SchoolClass $schoolClass): bool
    {
        return false;
    }
}
