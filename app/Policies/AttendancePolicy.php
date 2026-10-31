<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\User;

/**
 * Teachers record and update attendance for their own classes; students read their own records.
 * Deleting attendance history is reserved for admins.
 */
class AttendancePolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * The controller limits the list to the user's own classes or records.
     */
    public function viewAny(User $user): bool
    {
        return ($user->isTeacher() && $user->teacher !== null)
            || ($user->isStudent() && $user->student !== null);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isTeacher()) {
            return (bool) $user->teacher?->canAccessClass($attendance->class_id);
        }

        return $user->isStudent() && $user->student?->id === $attendance->student_id;
    }

    /**
     * Determine whether the user can record attendance, optionally for a specific class.
     *
     * Usage: Gate::authorize('create', [Attendance::class, $schoolClass])
     */
    public function create(User $user, ?SchoolClass $schoolClass = null): bool
    {
        if (! $user->isTeacher() || $user->teacher === null) {
            return false;
        }

        return $schoolClass === null || $user->teacher->canAccessClass($schoolClass->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $user->isTeacher() && (bool) $user->teacher?->canAccessClass($attendance->class_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}
