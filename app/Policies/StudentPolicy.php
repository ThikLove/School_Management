<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * Teachers see students in their classes; students see only their own profile.
 */
class StudentPolicy
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
    public function view(User $user, Student $student): bool
    {
        if ($user->isTeacher()) {
            return (bool) $user->teacher?->canAccessClass($student->class_id);
        }

        return $user->isStudent() && $user->student?->is($student);
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
    public function update(User $user, Student $student): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Student $student): bool
    {
        return false;
    }
}
