<div class="row g-3">
    <div class="col-md-6">
        <x-form.input name="class_name" label="Class name" :value="$schoolClass->class_name" required maxlength="50" placeholder="e.g. Grade 10A" />
    </div>
    <div class="col-md-3">
        <x-form.input name="grade_level" type="number" label="Grade level" :value="$schoolClass->grade_level" required min="1" max="12" />
    </div>
    <div class="col-md-3">
        <x-form.input name="room_number" label="Room number" :value="$schoolClass->room_number" maxlength="20" placeholder="e.g. R-101" />
    </div>
    <div class="col-md-6">
        <x-form.select name="homeroom_teacher_id" label="Homeroom teacher" :options="$teachers" :value="$schoolClass->homeroom_teacher_id" placeholder="— No homeroom teacher —" />
    </div>
</div>
