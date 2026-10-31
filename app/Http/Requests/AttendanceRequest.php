<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Which class a teacher may use is checked by the class_id rule below.
     */
    public function authorize(): bool
    {
        $attendance = $this->route('attendance');

        return $attendance
            ? $this->user()->can('update', $attendance)
            : $this->user()->can('create', Attendance::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * When editing, the student and class stay fixed; only the date, status and remarks change.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $attendance = $this->route('attendance');
        $studentId = $attendance?->student_id ?? $this->input('student_id');
        $classId = $attendance?->class_id ?? $this->input('class_id');

        $rules = [
            'attendance_date' => [
                'required',
                'date',
                'before_or_equal:today',
                function (string $attribute, mixed $value, Closure $fail) use ($attendance, $studentId, $classId): void {
                    $alreadyRecorded = Attendance::where('student_id', $studentId)
                        ->where('class_id', $classId)
                        ->whereDate('attendance_date', $value)
                        ->when($attendance, fn($query) => $query->whereKeyNot($attendance->id))
                        ->exists();

                    if ($alreadyRecorded) {
                        $fail('Attendance for this student, class and date has already been recorded.');
                    }
                },
            ],
            'status' => ['required', Rule::in(Attendance::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];

        if ($attendance === null) {
            $rules['class_id'] = [
                'required',
                'integer',
                Rule::in(
                    SchoolClass::query()
                        ->visibleTo($this->user())
                        ->pluck('id')
                        ->all()
                ),
            ];
            $rules['student_id'] = [
                'required',
                'integer',
                function (string $attribute, mixed $value, Closure $fail) use ($classId): void {
                    $isEnrolled = Enrollment::where('student_id', $value)
                        ->where('class_id', $classId)
                        ->where('status', 'active')
                        ->exists();

                    if (! $isEnrolled) {
                        $fail('The selected student is not enrolled in this class.');
                    }
                },
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendance_date.before_or_equal' => 'Attendance cannot be recorded for a future date.',
            'class_id.in' => 'You can only record attendance for your own classes.',
        ];
    }
}
