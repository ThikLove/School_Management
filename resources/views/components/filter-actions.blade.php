@props(['reset'])

<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary grow"><i class="bi bi-funnel me-1"></i> Filter</button>
    <a href="{{ $reset }}" class="btn btn-light" title="Clear filters" aria-label="Clear filters"><i class="bi bi-x-lg"></i></a>
</div>
