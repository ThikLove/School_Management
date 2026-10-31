<div class="row g-3">
    <div class="col-12"><h6 class="text-muted text-uppercase small mb-0">Personal details</h6></div>
    <div class="col-md-4">
        <x-form.input name="student_code" label="Student code" :value="$student->student_code" required maxlength="20" placeholder="e.g. S0031" />
    </div>
    <div class="col-md-8">
        <x-form.input name="name" label="Full name" :value="$student->name" required maxlength="150" />
    </div>
    <div class="col-md-4">
        <x-form.select name="gender" label="Gender" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" :value="$student->gender" placeholder="Select gender" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="date_of_birth" type="date" label="Date of birth" :value="$student->date_of_birth?->format('Y-m-d')" required :max="now()->subDay()->format('Y-m-d')" />
    </div>
    <div class="col-md-4">
        <x-form.input name="email" type="email" label="Email" :value="$student->email" maxlength="255" />
    </div>
    <div class="col-md-4">
        <x-form.input name="phone" label="Phone" :value="$student->phone" maxlength="30" />
    </div>
    <div class="col-md-8">
        <x-form.input name="address" label="Address" :value="$student->address" maxlength="255" />
    </div>

    <div class="col-12 mt-4"><h6 class="text-muted text-uppercase small mb-0">School details</h6></div>
    <div class="col-md-4">
        <x-form.select name="class_id" label="Class" :options="$classes" :value="$student->class_id" placeholder="Select class" required
                       :help="$student->exists ? 'Changing the class records a transfer in the enrollment history.' : 'The student is enrolled in this class for the current academic year.'" />
    </div>
    <div class="col-md-4">
        <x-form.input name="enrollment_date" type="date" label="Enrollment date" :value="$student->enrollment_date?->format('Y-m-d')" required />
    </div>
    <div class="col-md-4">
        <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'graduated' => 'Graduated']" :value="$student->status" required />
    </div>
    <div class="col-md-6">
        <x-form.select name="user_id" label="Login account" :options="$accounts" :value="$student->user_id" placeholder="— No login account —"
                       help="Optional. Lets the student log in and see their own records." />
    </div>
</div>
