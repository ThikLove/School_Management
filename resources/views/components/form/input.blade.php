@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'help' => null])

@php($id = $attributes->get('id', $name))

<label for="{{ $id }}" class="form-label">
    {{ $label }} @if ($required)<span class="text-danger">*</span>@endif
</label>
<input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}" @required($required)
       {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
@error($name)
    <div class="invalid-feedback">{{ $message }}</div>
@else
    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif
@enderror
