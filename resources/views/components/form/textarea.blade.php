@props(['name', 'label', 'value' => null, 'required' => false, 'rows' => 3, 'help' => null])

@php($id = $attributes->get('id', $name))

<label for="{{ $id }}" class="form-label">
    {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
</label>
<textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" @required($required)
          {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@else
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
@enderror
