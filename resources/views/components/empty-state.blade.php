@props(['title', 'icon' => 'bi-inbox'])

<div {{ $attributes->class('empty-state') }}>
    <div class="empty-icon"><i class="bi {{ $icon }}"></i></div>
    <h5 class="mb-1">{{ $title }}</h5>
    @if ($slot->isNotEmpty())
        <div class="small mx-auto" style="max-width: 28rem;">{{ $slot }}</div>
    @endif
</div>
