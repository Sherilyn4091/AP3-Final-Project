// resources/js/admin/reports-charts.js

(function () {
    // Make sure Chart.js is loaded
    if (typeof Chart === "undefined") return;

    // Read chart data injected by Blade
    const chartData = window.__monthlyReportChartData || {};

    // If chart data is missing, show a safe placeholder (prevents crash)
    function ensureData(key) {
        if (
            !chartData[key] ||
            !Array.isArray(chartData[key].labels) ||
            chartData[key].labels.length === 0 ||
            !Array.isArray(chartData[key].values)
        ) {
            chartData[key] = { labels: ["No Data"], values: [0] };
        }
    }

    ensureData("revenueTrend");
    ensureData("enrollmentTrend");
    ensureData("revenueByMethod");
    ensureData("packagePopularity");

    // Hold chart instances so we can resize/rebuild cleanly
    const charts = {};

    function baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true },
            },
        };
    }

    function buildLineChart(canvasId, labels, values) {
        const el = document.getElementById(canvasId);
        if (!el) return;

        if (charts[canvasId]) charts[canvasId].destroy();

        charts[canvasId] = new Chart(el, {
            type: "line",
            data: {
                labels,
                datasets: [
                    {
                        label: "Value",
                        data: values,
                        tension: 0.25,
                        pointRadius: labels.length <= 2 ? 5 : 2,
                    },
                ],
            },
            options: {
                ...baseOptions(),
                scales: {
                    y: { beginAtZero: true },
                    x: { ticks: { maxRotation: 45, minRotation: 0 } },
                },
            },
        });
    }

    function buildBarChart(canvasId, labels, values) {
        const el = document.getElementById(canvasId);
        if (!el) return;

        if (charts[canvasId]) charts[canvasId].destroy();

        charts[canvasId] = new Chart(el, {
            type: "bar",
            data: {
                labels,
                datasets: [{ label: "Value", data: values }],
            },
            options: {
                ...baseOptions(),
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }

    function buildPieChart(canvasId, labels, values) {
        const el = document.getElementById(canvasId);
        if (!el) return;

        if (charts[canvasId]) charts[canvasId].destroy();

        charts[canvasId] = new Chart(el, {
            type: "pie",
            data: {
                labels,
                datasets: [{ data: values }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: true, position: "top" },
                    tooltip: { enabled: true },
                },
            },
        });
    }

    function renderAllCharts() {
        buildLineChart(
            "revenueTrendChart",
            chartData.revenueTrend.labels,
            chartData.revenueTrend.values
        );

        buildLineChart(
            "enrollmentTrendChart",
            chartData.enrollmentTrend.labels,
            chartData.enrollmentTrend.values
        );

        buildPieChart(
            "revenueByMethodChart",
            chartData.revenueByMethod.labels,
            chartData.revenueByMethod.values
        );

        buildBarChart(
            "packagePopularityChart",
            chartData.packagePopularity.labels,
            chartData.packagePopularity.values
        );
    }

    // Initial draw
    renderAllCharts();

    // Auto-resize when layout changes
    const ro = new ResizeObserver(() => {
        Object.values(charts).forEach((c) => c && c.resize());
    });

    document.querySelectorAll(".chart-wrap").forEach((wrap) => ro.observe(wrap));

    window.addEventListener("resize", () => {
        Object.values(charts).forEach((c) => c && c.resize());
    });
})();
