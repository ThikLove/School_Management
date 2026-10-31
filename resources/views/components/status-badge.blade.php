@props(['status'])

@php
    $tone = match ($status) {
        'active', 'present' => 'success',
        'inactive', 'absent' => 'danger',
        'late', 'transferred' => 'warning',
        'graduated', 'completed', 'excused' => 'info',
        default => 'secondary',
    };
@endphp

<span {{ $attributes->class(['badge', "badge-soft-{$tone}", 'text-capitalize']) }}>{{ $status }}</span>
