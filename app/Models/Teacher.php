<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable([
    'teacher_code',
    'name',
    'gender',
    'email',
    'phone',
    'address',
    'hire_date',
    'department_id',
])]
class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    public const GENDERS = ['male', 'female', 'other'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    /**
     * The login account linked to this teacher (optional).
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The department this teacher belongs to.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * The classes this teacher supervises as homeroom teacher.
     *
     * @return HasMany<SchoolClass, $this>
     */
    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    /**
     * The classes this teacher teaches a subject in (through class_subject).
     *
     * @return BelongsToMany<SchoolClass, $this, ClassSubject>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'teacher_id', 'class_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * The subjects this teacher teaches (through class_subject).
     *
     * @return BelongsToMany<Subject, $this, ClassSubject>
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'teacher_id', 'subject_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'class_id')
            ->withTimestamps();
    }

    /**
     * The teacher's subject assignments (one row per class + subject).
     *
     * @return HasMany<ClassSubject, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    /**
     * Apply the search and filter values from the teachers list.
     *
     * @param  array{search?: ?string, department_id?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(
                fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('teacher_code', 'like', "%{$search}%")
            ))
            ->when($filters['department_id'] ?? null, fn (Builder $query, string $departmentId) => $query->where('department_id', $departmentId));
    }

    /**
     * IDs of every class this teacher may work with: classes they are homeroom
     * teacher of, plus classes they teach at least one subject in.
     *
     * The result is cached for the lifetime of this model instance, so policies
     * can call it many times per request without repeating the queries.
     *
     * @return Collection<int, int>
     */
    public function accessibleClassIds(): Collection
    {
        return once(fn () => $this->homeroomClasses()->pluck('id')
            ->merge($this->classes()->pluck('school_classes.id'))
            ->unique()
            ->values());
    }

    public function canAccessClass(int $classId): bool
    {
        return $this->accessibleClassIds()->contains($classId);
    }
}
