/*
|--------------------------------------------------------------------------
| resources/js/admin-pages/dashboard-charts.js
|--------------------------------------------------------------------------
|
| Admin Dashboard Charts
|
| Purpose:
| - Uses Chart.js through Vite instead of React/Recharts.
| - Fetches real dashboard data from Laravel API routes.
| - Keeps the Music Lab palette for line/bar charts.
| - Uses brighter readable colors only for doughnut charts.
| - Animates dashboard chart values and KPI numbers smoothly.
| - Renders charts only when visible so animations can be seen.
|
*/

import Chart from 'chart.js/auto';
import {
    PALETTE,
    DOUGHNUT_COLORS,
    cleanMojibakeText,
    animateExistingCounters,
    animateElementNumber,
    toNumber,
    formatDateLabel,
} from './chart-utils';

(function () {
    'use strict';

    const CHARTS = {
        enrollmentTrend: {
            canvasId: 'dashboardEnrollmentTrendChart',
            messageId: 'dashboardEnrollmentTrendMessage',
            endpoint: '/api/admin/charts/enrollment-trend',
            type: 'line',
            label: 'Enrollments',
            normalize: normalizeEnrollmentTrend,
        },
        revenueWeekly: {
            canvasId: 'dashboardRevenueChart',
            messageId: 'dashboardRevenueMessage',
            endpoint: '/api/admin/charts/revenue-weekly',
            type: 'bar',
            label: 'Revenue',
            normalize: normalizeWeeklyRevenue,
        },
        instrumentPopularity: {
            canvasId: 'dashboardInstrumentChart',
            messageId: 'dashboardInstrumentMessage',
            endpoint: '/api/admin/charts/instrument-popularity',
            type: 'doughnut',
            label: 'Students',
            normalize: normalizeInstrumentPopularity,
        },
        instructorPerformance: {
            canvasId: 'dashboardInstructorChart',
            messageId: 'dashboardInstructorMessage',
            endpoint: '/api/admin/charts/instructor-performance',
            type: 'bar',
            label: 'Students Taught',
            normalize: normalizeInstructorPerformance,
        },
    };

    const chartInstances = {};
    const loadedChartData = {};

    document.addEventListener('DOMContentLoaded', function () {
        cleanMojibakeText();
        animateExistingCounters();
        renderDashboardChartsWhenVisible();
        bindResponsiveResize();
    });

    function renderDashboardChartsWhenVisible() {
        const chartSection = document.querySelector('[data-dashboard-chart-wrap]');

        if (!chartSection) {
            renderAllDashboardCharts();
            return;
        }

        let hasRendered = false;

        const observer = new IntersectionObserver(function (entries) {
            const isVisible = entries.some(entry => entry.isIntersecting);

            if (!isVisible || hasRendered) {
                return;
            }

            hasRendered = true;
            renderAllDashboardCharts();
            observer.disconnect();
        }, {
            threshold: 0.1,
        });

        observer.observe(chartSection);
    }

    async function renderAllDashboardCharts() {
        await Promise.all(
            Object.entries(CHARTS).map(([chartKey, config]) => renderDashboardChart(chartKey, config))
        );

        updateSummaryCards();
    }

    async function renderDashboardChart(chartKey, config) {
        const canvas = document.getElementById(config.canvasId);

        if (!canvas) {
            return;
        }

        setMessage(config.messageId, 'Loading chart...');

        try {
            const rawData = await fetchJson(config.endpoint);
            const chartData = config.normalize(rawData);

            loadedChartData[chartKey] = chartData;

            if (!hasUsableChartData(chartData)) {
                setMessage(config.messageId, 'No data available yet.');
                clearExistingChart(chartKey);
                return;
            }

            renderChart(chartKey, config, chartData);
            setMessage(config.messageId, '');
        } catch (error) {
            console.error(`Failed to load ${chartKey}:`, error);
            setMessage(config.messageId, 'Unable to load chart data.', true);
            clearExistingChart(chartKey);
        }
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const text = await response.text();
        let data = [];

        try {
            data = text ? JSON.parse(text) : [];
        } catch (error) {
            throw new Error('Invalid JSON response from chart endpoint.');
        }

        if (!response.ok) {
            throw new Error(data.error || data.message || 'Chart endpoint failed.');
        }

        return Array.isArray(data) ? data : [];
    }

    function normalizeEnrollmentTrend(rows) {
        return {
            labels: rows.map(row => formatDateLabel(row.date || row.label)),
            values: rows.map(row => toNumber(row.count || row.value)),
        };
    }

    function normalizeWeeklyRevenue(rows) {
        return {
            labels: rows.map(row => formatDateLabel(row.week_start || row.label)),
            values: rows.map(row => toNumber(row.revenue || row.value)),
        };
    }

    function normalizeInstrumentPopularity(rows) {
        return {
            labels: rows.map(row => row.instrument_name || row.label || 'Unknown'),
            values: rows.map(row => toNumber(row.count || row.value)),
        };
    }

    function normalizeInstructorPerformance(rows) {
        return {
            labels: rows.map(row => row.instructor_name || row.label || 'Unknown'),
            values: rows.map(row => toNumber(row.total_students || row.value)),
        };
    }

    function renderChart(chartKey, config, chartData) {
        const canvas = document.getElementById(config.canvasId);

        if (!canvas) {
            return;
        }

        clearExistingChart(chartKey);

        chartInstances[chartKey] = new Chart(canvas, {
            type: config.type,
            data: {
                labels: chartData.labels,
                datasets: [buildDataset(config, chartData.values)],
            },
            options: getChartOptions(config.type),
        });
    }

    function buildDataset(config, values) {
        const isCircular = config.type === 'doughnut' || config.type === 'pie';
        const isLine = config.type === 'line';
        const isBar = config.type === 'bar';

        return {
            label: config.label,
            data: values,
            backgroundColor: isCircular
                ? DOUGHNUT_COLORS
                : isBar
                    ? 'rgba(47, 79, 79, 0.68)'
                    : 'rgba(47, 79, 79, 0.14)',
            borderColor: isCircular ? '#FFFFFF' : PALETTE.primary,
            borderWidth: isCircular ? 2 : 2,
            borderRadius: isBar ? 8 : 0,
            tension: 0.35,
            pointRadius: isLine ? 3 : 0,
            pointHoverRadius: isLine ? 6 : 0,
            hoverOffset: isCircular ? 8 : 0,
        };
    }

    function getChartOptions(type) {
        const isCircularChart = type === 'doughnut' || type === 'pie';

        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1800,
                easing: 'easeOutQuart',
                animateRotate: true,
                animateScale: true,
            },
            plugins: {
                title: { display: false },
                legend: {
                    display: isCircularChart,
                    position: 'bottom',
                    labels: {
                        color: PALETTE.slate,
                        usePointStyle: false,
                        boxWidth: 18,
                        boxHeight: 10,
                        padding: 14,
                        font: {
                            family: 'Inter',
                            size: 12,
                            weight: '500',
                        },
                    },
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: '#223030',
                    titleColor: '#FFFFFF',
                    bodyColor: '#FFFFFF',
                    padding: 10,
                    titleFont: {
                        family: 'Inter',
                        size: 12,
                        weight: '600',
                    },
                    bodyFont: {
                        family: 'Inter',
                        size: 12,
                    },
                },
            },
            scales: isCircularChart ? {} : {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: PALETTE.dim,
                        font: {
                            family: 'JetBrains Mono',
                            size: 11,
                        },
                    },
                    grid: { color: 'rgba(216, 221, 216, 0.75)' },
                },
                x: {
                    ticks: {
                        maxRotation: 35,
                        minRotation: 0,
                        color: PALETTE.dim,
                        font: {
                            family: 'Inter',
                            size: 11,
                        },
                    },
                    grid: { color: 'rgba(216, 221, 216, 0.45)' },
                },
            },
        };
    }

    function clearExistingChart(chartKey) {
        if (chartInstances[chartKey]) {
            chartInstances[chartKey].destroy();
            chartInstances[chartKey] = null;
        }
    }

    function updateSummaryCards() {
        const enrollmentTotal = sumValues(loadedChartData.enrollmentTrend);
        const revenueTotal = sumValues(loadedChartData.revenueWeekly);
        const topInstrument = topLabel(loadedChartData.instrumentPopularity);
        const topInstructor = topLabel(loadedChartData.instructorPerformance);

        animateText('dashboardSummaryEnrollments', enrollmentTotal, { decimals: 0 });
        animateText('dashboardSummaryRevenue', revenueTotal, { prefix: '\u20B1', decimals: 2 });
        setText('dashboardSummaryInstrument', topInstrument || 'No Data');
        setText('dashboardSummaryInstructor', topInstructor || 'No Data');
    }

    function animateText(id, target, options = {}) {
        animateElementNumber(document.getElementById(id), target, options);
    }

    function setMessage(messageId, message, isError = false) {
        const element = document.getElementById(messageId);

        if (!element) {
            return;
        }

        element.textContent = message;
        element.style.color = isError ? PALETTE.darkBrown : PALETTE.dim;
        element.classList.toggle('hidden', message === '');
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    function bindResponsiveResize() {
        const resizeObserver = new ResizeObserver(function () {
            Object.values(chartInstances).forEach(chart => chart && chart.resize());
        });

        document.querySelectorAll('[data-dashboard-chart-wrap]').forEach(wrap => resizeObserver.observe(wrap));

        window.addEventListener('resize', function () {
            Object.values(chartInstances).forEach(chart => chart && chart.resize());
        });
    }

    function hasUsableChartData(chartData) {
        return Boolean(
            chartData &&
            Array.isArray(chartData.labels) &&
            Array.isArray(chartData.values) &&
            chartData.labels.length > 0 &&
            chartData.values.some(value => Number(value) > 0)
        );
    }

    function sumValues(chartData) {
        if (!chartData || !Array.isArray(chartData.values)) {
            return 0;
        }

        return chartData.values.reduce((sum, value) => sum + toNumber(value), 0);
    }

    function topLabel(chartData) {
        if (!chartData || !Array.isArray(chartData.labels) || !Array.isArray(chartData.values)) {
            return '';
        }

        let bestIndex = -1;
        let bestValue = -Infinity;

        chartData.values.forEach((value, index) => {
            const numericValue = toNumber(value);

            if (numericValue > bestValue) {
                bestValue = numericValue;
                bestIndex = index;
            }
        });

        return bestValue > 0 && bestIndex >= 0 ? chartData.labels[bestIndex] : '';
    }
})();