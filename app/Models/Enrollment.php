<?php

namespace App\Models;

use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_id', 'class_id', 'enrollment_date', 'academic_year', 'status'])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    public const STATUSES = ['active', 'completed', 'transferred'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
        ];
    }

    /**
     * The academic year running today. A new year starts on 1 September, e.g. "2026-2027".
     */
    public static function currentAcademicYear(): string
    {
        $startYear = now()->month >= 9 ? now()->year : now()->year - 1;

        return $startYear.'-'.($startYear + 1);
    }

    /**
     * The student who was enrolled.
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The class the student was enrolled in.
     *
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Keep the rest of the data consistent after this enrollment became active:
     * a student has only one active enrollment per academic year, and an active
     * enrollment for the current year decides which class the student is in now.
     *
     * Call this inside the same database transaction that saved the enrollment.
     */
    public function syncActiveState(): void
    {
        if ($this->status !== 'active') {
            return;
        }

        self::where('student_id', $this->student_id)
            ->where('academic_year', $this->academic_year)
            ->where('status', 'active')
            ->whereKeyNot($this->id)
            ->update(['status' => 'transferred']);

        if ($this->academic_year === self::currentAcademicYear()) {
            Student::whereKey($this->student_id)->update(['class_id' => $this->class_id]);
        }
    }

    /**
     * Apply the search and filter values from the enrollments list.
     *
     * @param  array{search?: ?string, class_id?: ?string, academic_year?: ?string, status?: ?string}  $filters
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
            ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));
    }
}
