<?php

namespace App\Http\Requests;

use App\Models\Enrollment;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return $enrollment
            ? $this->user()->can('update', $enrollment)
            : $this->user()->can('create', Enrollment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('students', 'id'),
                Rule::unique('enrollments')
                    ->where('class_id', $this->input('class_id'))
                    ->where('academic_year', $this->input('academic_year'))
                    ->ignore($this->route('enrollment')),
            ],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'enrollment_date' => ['required', 'date'],
            'academic_year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    [$start, $end] = array_map('intval', explode('-', (string) $value) + [1 => 0]);

                    if ($end !== $start + 1) {
                        $fail('The academic year must be two consecutive years, for example 2026-2027.');
                    }
                },
            ],
            'status' => ['required', Rule::in(Enrollment::STATUSES)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.unique' => 'This student is already enrolled in the selected class for that academic year.',
            'academic_year.regex' => 'Use the format YYYY-YYYY, for example 2026-2027.',
        ];
    }
}
