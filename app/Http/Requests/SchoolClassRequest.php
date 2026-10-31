<?php

namespace App\Http\Requests;

use App\Models\SchoolClass;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $schoolClass = $this->route('schoolClass');

        return $schoolClass
            ? $this->user()->can('update', $schoolClass)
            : $this->user()->can('create', SchoolClass::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_name' => ['required', 'string', 'max:50', Rule::unique('school_classes')->ignore($this->route('schoolClass'))],
            'grade_level' => ['required', 'integer', 'between:1,12'],
            'room_number' => ['nullable', 'string', 'max:20'],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('teachers', 'id')],
        ];
    }
}
