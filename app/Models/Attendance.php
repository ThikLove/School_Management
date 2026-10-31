<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'class_id', 'attendance_date', 'status', 'remarks'])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    public const STATUSES = ['present', 'absent', 'late', 'excused'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    /**
     * The student this attendance record is for.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The class the attendance was taken in.
     *
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Limit the query to the attendance records the given user is allowed to see.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->whereIn('class_id', $user->teacher?->accessibleClassIds() ?? []);

            return;
        }

        $query->whereIn('student_id', $user->student ? [$user->student->id] : []);
    }

    /**
     * Apply the search and filter values from the attendance list.
     *
     * @param  array{search?: ?string, class_id?: ?string, status?: ?string, date_from?: ?string, date_to?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->whereHas(
                'student',
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('student_code', 'like', "%{$search}%")
            ))
            ->when($filters['class_id'] ?? null, fn (Builder $query, string $classId) => $query->where('class_id', $classId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('attendance_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('attendance_date', '<=', $to));
    }
}
