@props(['label', 'value', 'icon', 'tone' => 'green', 'hint' => null])

<div {{ $attributes->class('card') }}>
    <div class="card-body stat-card">
        <div class="stat-icon tone-{{ $tone }}"><i class="bi {{ $icon }}"></i></div>
        <div class="min-w-0">
            <div class="stat-value">{{ $value }}</div>
            <div class="stat-label">{{ $label }}</div>
            @if ($hint)
                <div class="small text-muted mt-1">{{ $hint }}</div>
            @endif
        </div>
    </div>
</div>
