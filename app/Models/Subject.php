<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_code', 'name', 'description'])]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    /**
     * The classes this subject is taught in, with the assigned teacher on the pivot.
     *
     * @return BelongsToMany<SchoolClass, $this, ClassSubject>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject', 'subject_id', 'class_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'teacher_id')
            ->withTimestamps();
    }

    /**
     * The teachers who teach this subject.
     *
     * @return BelongsToMany<Teacher, $this, ClassSubject>
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject', 'subject_id', 'teacher_id')
            ->using(ClassSubject::class)
            ->withPivot('id', 'class_id')
            ->withTimestamps();
    }

    /**
     * The class assignments of this subject (one row per class + teacher).
     *
     * @return HasMany<ClassSubject, $this>
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ClassSubject::class);
    }

    /**
     * Limit the query to the subjects the given user teaches or studies.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($user->isTeacher()) {
            $query->whereHas('assignments', fn (Builder $query) => $query->where('teacher_id', $user->teacher?->id ?? 0));

            return;
        }

        $query->whereHas('assignments', fn (Builder $query) => $query->where('class_id', $user->student?->class_id ?? 0));
    }

    /**
     * Apply the search value from the subjects list.
     *
     * @param  array{search?: ?string}  $filters
     */
    #[Scope]
    protected function filter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(
            fn (Builder $query) => $query->where('name', 'like', "%{$search}%")->orWhere('subject_code', 'like', "%{$search}%")
        ));
    }
}
