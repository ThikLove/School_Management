<div class="row g-3">
    <div class="col-md-4">
        <x-form.input name="subject_code" label="Subject code" :value="$subject->subject_code" required maxlength="20" placeholder="e.g. MATH101" />
    </div>
    <div class="col-md-8">
        <x-form.input name="name" label="Subject name" :value="$subject->name" required maxlength="100" placeholder="e.g. Mathematics" />
    </div>
    <div class="col-12">
        <x-form.textarea name="description" label="Description" :value="$subject->description" rows="4" />
    </div>
</div>
