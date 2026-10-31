@props(['label'])

<li class="list-group-item d-flex justify-content-between gap-3">
    <span class="text-muted">{{ $label }}</span>
    <span class="text-end fw-medium">{{ $slot->hasActualContent() ? $slot : '—' }}</span>
</li>
