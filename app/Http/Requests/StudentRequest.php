<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $student = $this->route('student');

        return $student
            ? $this->user()->can('update', $student)
            : $this->user()->can('create', Student::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'student_code' => ['required', 'string', 'max:20', Rule::unique('students')->ignore($student)],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', Rule::in(Student::GENDERS)],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('students')->ignore($student)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'enrollment_date' => ['required', 'date', 'after:date_of_birth'],
            'status' => ['required', Rule::in(Student::STATUSES)],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNot('role', 'admin'),
                Rule::unique('students', 'user_id')->ignore($student),
                Rule::unique('teachers', 'user_id'),
            ],
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
            'class_id.required' => 'Please choose a class.',
            'user_id.unique' => 'This login account is already linked to another student or teacher.',
        ];
    }
}
