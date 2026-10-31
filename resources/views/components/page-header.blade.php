@props(['title', 'subtitle' => null])

<div class="page-header">
    <div class="min-w-0">
        <h2>{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
