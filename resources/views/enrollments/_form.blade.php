<div class="row g-3">
    <div class="col-md-6">
        <x-form.select name="student_id" label="Student" :options="$students" :value="$enrollment->student_id" placeholder="Select student" required />
    </div>
    <div class="col-md-6">
        <x-form.select name="class_id" label="Class" :options="$classes" :value="$enrollment->class_id" placeholder="Select class" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="enrollment_date" type="date" label="Enrollment date" :value="$enrollment->enrollment_date?->format('Y-m-d')" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="academic_year" label="Academic year" :value="$enrollment->academic_year" required placeholder="2026-2027" pattern="\d{4}-\d{4}" />
    </div>
    <div class="col-md-4">
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'completed' => 'Completed', 'transferred' => 'Transferred']" :value="$enrollment->status" required />
    </div>
    <div class="col-12">
        <div class="alert alert-info mb-0 small">
            <i class="bi bi-info-circle"></i>
            <div>A student can have one <strong>active</strong> enrollment per academic year. Saving an active enrollment marks the student's other active enrollment for that year as <em>transferred</em>, and for the current year ({{ App\Models\Enrollment::currentAcademicYear() }}) it also moves the student into the selected class.</div>
        </div>
    </div>
</div>
