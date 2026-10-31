/**
 * Shared Chart.js options for the green theme: recessive grid and axes,
 * theme font, hover tooltips. Page-specific options are merged on top.
 */
window.schoolChartDefaults = (overrides = {}) => {
    const merge = (base, extra) => {
        const result = { ...base };

        Object.entries(extra).forEach(([key, value]) => {
            const isObject = value && typeof value === 'object' && !Array.isArray(value);
            result[key] = isObject && base[key] && typeof base[key] === 'object' ? merge(base[key], value) : value;
        });

        return result;
    };

    return merge({
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'nearest', intersect: true },
        scales: {
            x: { grid: { color: '#EEF3F0' }, border: { display: false }, ticks: { color: '#6B7280' } },
            y: { grid: { color: '#EEF3F0' }, border: { display: false }, ticks: { color: '#6B7280' } },
        },
        plugins: {
            legend: { labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, color: '#1F2937' } },
            tooltip: {
                backgroundColor: '#1F2937',
                padding: 10,
                cornerRadius: 8,
                boxPadding: 4,
                usePointStyle: true,
            },
        },
    }, overrides);
};

if (window.Chart) {
    Chart.defaults.font.family = '"Inter", system-ui, sans-serif';
    Chart.defaults.color = '#6B7280';
}

document.addEventListener('DOMContentLoaded', () => {
    /**
     * Action menus inside scrollable tables use "fixed" positioning, so the
     * dropdown is not cut off by the table's overflow container.
     */
    document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]').forEach((toggle) => {
        new bootstrap.Dropdown(toggle, {
            popperConfig: (config) => ({ ...config, strategy: 'fixed' }),
        });
    });

    /**
     * Confirmation dialog for destructive actions.
     *
     * Any <form data-confirm="Message"> shows a Bootstrap modal before it is submitted.
     * Optional: data-confirm-title="..." and data-confirm-button="Delete".
     */
    const modalElement = document.getElementById('confirmModal');

    if (!modalElement) {
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    const titleElement = modalElement.querySelector('[data-confirm-title]');
    const messageElement = modalElement.querySelector('[data-confirm-message]');
    const confirmButton = modalElement.querySelector('[data-confirm-accept]');
    let pendingForm = null;

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!form.matches('form[data-confirm]') || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();
        pendingForm = form;
        titleElement.textContent = form.dataset.confirmTitle || 'Are you sure?';
        messageElement.textContent = form.dataset.confirm;
        confirmButton.textContent = form.dataset.confirmButton || 'Delete';
        modal.show();
    });

    confirmButton.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.dataset.confirmed = 'true';
            confirmButton.disabled = true;
            pendingForm.submit();
        }
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        pendingForm = null;
        confirmButton.disabled = false;
    });
});
