/*
|--------------------------------------------------------------------------
| resources/js/admin-pages/reports-chart.js
|--------------------------------------------------------------------------
|
| Monthly Reports Charts
|
| Purpose:
| - Uses Vite-installed Chart.js instead of CDN Chart.js.
| - Reads report data injected from ReportsController through Blade.
| - Keeps the Music Lab palette for line/bar charts.
|
*/

import Chart from 'chart.js/auto';
import {
    PALETTE,
    DOUGHNUT_COLORS,
    cleanMojibakeText,
    animateExistingCounters,
    toNumber,
} from './chart-utils';

(function () {
    'use strict';

    const chartInstances = {};
    const chartData = window.__monthlyReportChartData || {};

    document.addEventListener('DOMContentLoaded', function () {
        cleanMojibakeText();
        animateExistingCounters();
        renderChartsWhenVisible();
        bindResizeObserver();
    });

    function renderChartsWhenVisible() {
        const chartSection = document.querySelector('.chart-wrap');

        if (!chartSection) {
            renderAllCharts();
            return;
        }

        let hasRendered = false;

        const observer = new IntersectionObserver(function (entries) {
            const isVisible = entries.some(entry => entry.isIntersecting);

            if (!isVisible || hasRendered) {
                return;
            }

            hasRendered = true;
            renderAllCharts();
            observer.disconnect();
        }, {
            threshold: 0.1,
        });

        observer.observe(chartSection);
    }

    function renderAllCharts() {
        buildLineChart('revenueTrendChart', 'revenueTrendEmpty', chartData.revenueTrend, 'Revenue');
        buildLineChart('enrollmentTrendChart', 'enrollmentTrendEmpty', chartData.enrollmentTrend, 'Enrollments');
        buildDoughnutChart('revenueByMethodChart', 'revenueByMethodEmpty', chartData.revenueByMethod, 'Revenue by Payment Method');
        buildBarChart('packagePopularityChart', 'packagePopularityEmpty', chartData.packagePopularity, 'Package Popularity');
    }

    function buildLineChart(canvasId, emptyId, data, label) {
        const normalized = normalizeChartData(data, emptyId);

        if (!normalized) {
            return;
        }

        buildChart(canvasId, {
            type: 'line',
            labels: normalized.labels,
            values: normalized.values,
            label,
            datasetOptions: {
                backgroundColor: 'rgba(47, 79, 79, 0.15)',
                borderColor: PALETTE.primary,
                borderWidth: 2,
                tension: 0.35,
                pointRadius: normalized.labels.length <= 2 ? 5 : 2,
                pointHoverRadius: 6,
            },
            options: {
                ...baseOptions(),
                scales: defaultScales(),
            },
        });
    }

    function buildBarChart(canvasId, emptyId, data, label) {
        const normalized = normalizeChartData(data, emptyId);

        if (!normalized) {
            return;
        }

        buildChart(canvasId, {
            type: 'bar',
            labels: normalized.labels,
            values: normalized.values,
            label,
            datasetOptions: {
                backgroundColor: 'rgba(47, 79, 79, 0.68)',
                borderColor: PALETTE.primary,
                borderWidth: 1,
                borderRadius: 8,
            },
            options: {
                ...baseOptions(),
                scales: defaultScales(),
            },
        });
    }

    function buildDoughnutChart(canvasId, emptyId, data, label) {
        const normalized = normalizeChartData(data, emptyId);

        if (!normalized) {
            return;
        }

        buildChart(canvasId, {
            type: 'doughnut',
            labels: normalized.labels,
            values: normalized.values,
            label,
            datasetOptions: {
                backgroundColor: DOUGHNUT_COLORS,
                borderColor: '#FFFFFF',
                borderWidth: 2,
                hoverOffset: 8,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1800,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true,
                },
                plugins: {
                    legend: {
                        display: true,
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
                    tooltip: tooltipOptions(),
                },
            },
        });
    }

    function normalizeChartData(data, emptyId) {
        const emptyEl = document.getElementById(emptyId);
        const isEmpty = !data ||
            !Array.isArray(data.labels) ||
            !Array.isArray(data.values) ||
            data.labels.length === 0 ||
            !data.values.some(value => Number(value) > 0);

        if (emptyEl) {
            emptyEl.classList.toggle('hidden', !isEmpty);
        }

        if (isEmpty) {
            return null;
        }

        return {
            labels: data.labels,
            values: data.values.map(value => Number(value) || 0),
        };
    }

    function buildChart(canvasId, config) {
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            return;
        }

        if (chartInstances[canvasId]) {
            chartInstances[canvasId].destroy();
        }

        chartInstances[canvasId] = new Chart(canvas, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: [
                    {
                        label: config.label,
                        data: config.values,
                        ...config.datasetOptions,
                    },
                ],
            },
            options: config.options,
        });
    }

    function baseOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1800,
                easing: 'easeOutQuart',
            },
            plugins: {
                legend: { display: false },
                tooltip: tooltipOptions(),
            },
        };
    }

    function tooltipOptions() {
        return {
            enabled: true,
            backgroundColor: '#223030',
            titleColor: '#FFFFFF',
            bodyColor: '#FFFFFF',
            titleFont: {
                family: 'Inter',
                size: 12,
                weight: '600',
            },
            bodyFont: {
                family: 'Inter',
                size: 12,
            },
            padding: 10,
        };
    }

    function defaultScales() {
        return {
            y: {
                beginAtZero: true,
                ticks: {
                    color: PALETTE.dim,
                    font: {
                        family: 'JetBrains Mono',
                        size: 11,
                    },
                },
                grid: { color: 'rgba(216, 221, 216, 0.8)' },
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 0,
                    color: PALETTE.dim,
                    font: {
                        family: 'Inter',
                        size: 11,
                    },
                },
                grid: { color: 'rgba(216, 221, 216, 0.5)' },
            },
        };
    }

    function bindResizeObserver() {
        const resizeObserver = new ResizeObserver(resizeAllCharts);
        document.querySelectorAll('.chart-wrap').forEach(wrap => resizeObserver.observe(wrap));
        window.addEventListener('resize', resizeAllCharts);
    }

    function resizeAllCharts() {
        Object.values(chartInstances).forEach(chart => chart && chart.resize());
    }
})();