<?php

namespace App\Http\Requests;

use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $teacher = $this->route('teacher');

        return $teacher
            ? $this->user()->can('update', $teacher)
            : $this->user()->can('create', Teacher::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'teacher_code' => ['required', 'string', 'max:20', Rule::unique('teachers')->ignore($teacher)],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', Rule::in(Teacher::GENDERS)],
            'email' => ['required', 'email', 'max:255', Rule::unique('teachers')->ignore($teacher)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereNot('role', 'admin'),
                Rule::unique('teachers', 'user_id')->ignore($teacher),
                Rule::unique('students', 'user_id'),
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
            'user_id.unique' => 'This login account is already linked to another teacher or student.',
            'department_id.required' => 'Please choose a department.',
        ];
    }
}
