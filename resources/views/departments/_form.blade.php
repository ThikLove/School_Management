<div class="row g-3">
    <div class="col-12">
        <x-form.input name="name" label="Department name" :value="$department->name" required maxlength="100" placeholder="e.g. Science" />
    </div>
    <div class="col-12">
        <x-form.textarea name="description" label="Description" :value="$department->description" rows="4" placeholder="What does this department teach?" />
    </div>
</div>
