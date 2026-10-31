<div class="row g-3">
    <div class="col-md-4">
        <x-form.input name="teacher_code" label="Teacher code" :value="$teacher->teacher_code" required maxlength="20" placeholder="e.g. T011" />
    </div>
    <div class="col-md-8">
        <x-form.input name="name" label="Full name" :value="$teacher->name" required maxlength="150" />
    </div>
    <div class="col-md-4">
        <x-form.select name="gender" label="Gender" :options="['male' => 'Male', 'female' => 'Female', 'other' => 'Other']" :value="$teacher->gender" placeholder="Select gender" required />
    </div>
    <div class="col-md-4">
        <x-form.input name="email" type="email" label="Email" :value="$teacher->email" required maxlength="255" />
    </div>
    <div class="col-md-4">
        <x-form.input name="phone" label="Phone" :value="$teacher->phone" maxlength="30" />
    </div>
    <div class="col-12">
        <x-form.input name="address" label="Address" :value="$teacher->address" maxlength="255" />
    </div>
    <div class="col-md-4">
        <x-form.input name="hire_date" type="date" label="Hire date" :value="$teacher->hire_date?->format('Y-m-d')" required :max="now()->format('Y-m-d')" />
    </div>
    <div class="col-md-4">
        <x-form.select name="department_id" label="Department" :options="$departments" :value="$teacher->department_id" placeholder="Select department" required />
    </div>
    <div class="col-md-4">
        <x-form.select name="user_id" label="Login account" :options="$accounts" :value="$teacher->user_id" placeholder="— No login account —"
                       help="Optional. Lets this teacher log in; the account gets the teacher role." />
    </div>
</div>
