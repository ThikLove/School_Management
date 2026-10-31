@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'required' => false, 'help' => null])

@php
    $id = $attributes->get('id', $name);
    $selected = (string) old($name, $value);
@endphp

<label for="{{ $id }}" class="form-label">
    {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
</label>
<select name="{{ $name }}" id="{{ $id }}" @required($required)
        {{ $attributes->except('id')->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected($selected === (string) $optionValue)>{{ $optionLabel }}</option>
    @endforeach
</select>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@else
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
@enderror
