{{-- Expects: $summaryDate (Carbon|null), $daySummary (Collection status => total) --}}
@php
    $dayTotal = $daySummary->sum();
    $dayAttended = ($daySummary['present'] ?? 0) + ($daySummary['late'] ?? 0);
    $isToday = $summaryDate?->isToday();
@endphp

<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ $isToday ? "Today's attendance" : 'Latest attendance' }}</span>
        @if ($summaryDate)
            <span class="small text-muted fw-normal">{{ $summaryDate->format('D, d M Y') }}</span>
        @endif
    </div>
    @if ($dayTotal === 0)
        <x-empty-state title="No attendance recorded yet" icon="bi-calendar" class="py-4" />
    @else
        <div class="card-body">
            @unless ($isToday)
                <div class="alert alert-info small py-2 mb-3"><i class="bi bi-info-circle"></i><div>Nothing recorded today yet, showing the most recent school day.</div></div>
            @endunless
            <div class="d-flex align-items-baseline gap-2 mb-3">
                <span class="stat-value fs-2 fw-bold">{{ round($dayAttended / $dayTotal * 100) }}%</span>
                <span class="text-muted small">attended ({{ $dayAttended }} of {{ $dayTotal }} students)</span>
            </div>
            <div class="row g-2">
                @foreach (['present' => ['bi-check2-circle', 'green'], 'late' => ['bi-clock', 'amber'], 'absent' => ['bi-x-circle', 'red'], 'excused' => ['bi-file-earmark-medical', 'blue']] as $status => [$icon, $tone])
                    <div class="col-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded-3 border">
                            <span class="stat-icon tone-{{ $tone }} rounded-3 d-inline-flex align-items-center justify-content-center" style="width:2rem;height:2rem;"><i class="bi {{ $icon }}"></i></span>
                            <div>
                                <div class="fw-bold lh-1">{{ $daySummary[$status] ?? 0 }}</div>
                                <div class="small text-muted text-capitalize">{{ $status }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
