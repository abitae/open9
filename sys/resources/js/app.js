import Chart from 'chart.js/auto';

window.open9Charts = window.open9Charts || {};

window.renderOpen9DashboardCharts = (series) => {
    const isDark = document.documentElement.classList.contains('dark');
    const muted = isDark ? '#A3A3A3' : '#737373';
    const grid = isDark ? 'rgba(229, 229, 229, 0.08)' : 'rgba(26, 26, 26, 0.08)';

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    color: muted,
                    boxWidth: 10,
                    boxHeight: 10,
                    padding: 16,
                    usePointStyle: true,
                },
            },
        },
        scales: {
            x: {
                grid: {
                    display: false,
                },
                ticks: {
                    color: muted,
                },
            },
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    color: muted,
                },
                grid: {
                    color: grid,
                },
                border: {
                    display: false,
                },
            },
        },
    };

    const render = (id, config) => {
        const canvas = document.getElementById(id);

        if (!canvas) {
            return;
        }

        window.open9Charts[id]?.destroy();
        window.open9Charts[id] = new Chart(canvas, config);
    };

    render('activity-chart', {
        type: 'bar',
        data: {
            labels: series.labels,
            datasets: [
                {
                    label: 'Contactos',
                    data: series.contacts,
                    backgroundColor: '#0077FF',
                    borderRadius: 6,
                    maxBarThickness: 18,
                },
                {
                    label: 'Clientes',
                    data: series.clients,
                    backgroundColor: '#0044AA',
                    borderRadius: 6,
                    maxBarThickness: 18,
                },
                {
                    label: 'Pedidos',
                    data: series.orders,
                    backgroundColor: isDark ? '#E5E5E5' : '#1A1A1A',
                    borderRadius: 6,
                    maxBarThickness: 18,
                },
            ],
        },
        options: chartDefaults,
    });

    render('contacts-chart', {
        type: 'bar',
        data: {
            labels: series.labels,
            datasets: [{
                label: 'Contactos',
                data: series.contacts,
                backgroundColor: '#0077FF',
                borderRadius: 6,
            }],
        },
        options: { ...chartDefaults, plugins: { legend: { display: false } } },
    });

    render('clients-chart', {
        type: 'bar',
        data: {
            labels: series.labels,
            datasets: [{
                label: 'Clientes',
                data: series.clients,
                backgroundColor: '#0044AA',
                borderRadius: 6,
            }],
        },
        options: { ...chartDefaults, plugins: { legend: { display: false } } },
    });

    render('orders-chart', {
        type: 'line',
        data: {
            labels: series.labels,
            datasets: [{
                label: 'Pedidos',
                data: series.orders,
                borderColor: '#0077FF',
                backgroundColor: 'rgba(0, 119, 255, 0.14)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
            }],
        },
        options: { ...chartDefaults, plugins: { legend: { display: false } } },
    });
};
