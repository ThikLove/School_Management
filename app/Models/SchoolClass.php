<?php

namespace App\Models;

use Database\Factories\SchoolClassFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('school_classes')]
#[Fillable(['class_name', 'grade_level', 'room_number', 'homeroom_teacher_id'])]
class SchoolClass extends Model
{
    /** @use HasFactory<SchoolClassFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
        ];
    }

    /**
     * The optional homeroom teacher who supervises this class.
     *
     * @return BelongsTo<Teacher, $this>
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * The students currently placed in this class.
     *
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * The subjects taught in this class, with the assigned teacher on the pivot.
     *
     * @return BelongsToMany<Subject, $this, ClassSubject>
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * The teachers who teach a subject in this class.
     *
     * @return BelongsToMany<Teacher, $this, ClassSubject>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject', 'class_id', 'teacher_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * The subject assignments of this class (one row per subject + teacher).
     *
     * @return HasMany<ClassSubject, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    /**
     * The enrollment history records for this class.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * The attendance records taken in this class.
     *
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Limit the query to the classes the given user is allowed to see.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->whereIn('id', $user->teacher?->accessibleClassIds() ?? []);

            return;
        }

        $query->whereIn('id', $user->student ? [$user->student->class_id] : []);
    }

    /**
     * Apply the search and filter values from the classes list.
     *
     * @param  array{search?: ?string, grade_level?: ?string, homeroom_teacher_id?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where('class_name', 'like', "%{$search}%"))
            ->when($filters['grade_level'] ?? null, fn (Builder $query, string $grade) => $query->where('grade_level', $grade))
            ->when($filters['homeroom_teacher_id'] ?? null, fn (Builder $query, string $teacherId) => $query->where('homeroom_teacher_id', $teacherId));
    }
}
