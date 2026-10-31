{{-- Expects: $attendanceChart = ['labels' => [...], 'series' => ['present' => [...], ...]] --}}
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Attendance overview</span>
        <span class="small text-muted fw-normal">Last {{ count($attendanceChart['labels']) }} school days</span>
    </div>
    <div class="card-body">
        @if (count($attendanceChart['labels']) === 0)
            <x-empty-state title="No attendance data yet" icon="bi-bar-chart" class="py-4" />
        @else
            <div style="position: relative; height: 280px;">
                <canvas id="attendanceChart" role="img" aria-label="Stacked bar chart of attendance status per school day"></canvas>
            </div>
            <details class="mt-3 small">
                <summary class="text-muted">Show data table</summary>
                <div class="table-responsive mt-2">
                    <table class="table table-sm">
                        <thead><tr><th>Day</th><th class="text-end">Present</th><th class="text-end">Excused</th><th class="text-end">Late</th><th class="text-end">Absent</th></tr></thead>
                        <tbody>
                            @foreach ($attendanceChart['labels'] as $index => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    @foreach (['present', 'excused', 'late', 'absent'] as $status)
                                        <td class="text-end">{{ $attendanceChart['series'][$status][$index] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>
</div>

@if (count($attendanceChart['labels']) > 0)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const data = @json($attendanceChart);
                // Stack order present → excused → late → absent keeps amber and green apart (colour-blind safe).
                const styles = {
                    present: { label: 'Present', color: '#16A34A' },
                    excused: { label: 'Excused', color: '#0EA5E9' },
                    late: { label: 'Late', color: '#F59E0B' },
                    absent: { label: 'Absent', color: '#EF4444' },
                };

                new Chart(document.getElementById('attendanceChart'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: Object.entries(styles).map(([key, style]) => ({
                            label: style.label,
                            data: data.series[key],
                            backgroundColor: style.color,
                            borderColor: '#FFFFFF',
                            borderWidth: { top: 2 },
                            borderSkipped: 'bottom',
                            borderRadius: 4,
                            maxBarThickness: 36,
                        })),
                    },
                    options: window.schoolChartDefaults({
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                        },
                        plugins: {
                            legend: { display: true, position: 'top', align: 'end' },
                            tooltip: { mode: 'index', intersect: false },
                        },
                    }),
                });
            });
        </script>
    @endpush
@endif
