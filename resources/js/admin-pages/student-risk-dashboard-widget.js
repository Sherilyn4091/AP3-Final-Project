/*
  resources/js/admin-pages/student-risk-dashboard-widget.js

  Compact Student Risk widget for dashboard and reports page.
  Uses the same Python-powered Laravel endpoint, but renders only summary cards,
  risk distribution bars, and a small high-risk student table when present.
*/

(() => {
    'use strict';

    const config = window.studentRiskWidgetConfig || {};
    const widgets = document.querySelectorAll('[data-risk-widget]');

    if (!widgets.length || !config.dashboardDataUrl) {
        return;
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadWidgetData();

        const refreshMs = Number(config.refreshMs || 60000);
        if (refreshMs > 0) {
            window.setInterval(loadWidgetData, refreshMs);
        }
    });

    async function loadWidgetData() {
        try {
            const response = await fetch(config.dashboardDataUrl, {
                headers: { Accept: 'application/json' },
            });

            const payload = await response.json();

            if (!response.ok || payload.ok !== true) {
                throw new Error(payload.message || 'Unable to load risk widget.');
            }

            widgets.forEach((widget) => renderWidget(widget, payload));
        } catch (error) {
            widgets.forEach((widget) => renderError(widget, error.message));
        }
    }

    function renderWidget(widget, payload) {
        const summary = payload.summary || {};

        setCard(widget, 'total_students_analyzed', summary.total_students_analyzed ?? 0);
        setCard(widget, 'low_risk', summary.low_risk ?? 0);
        setCard(widget, 'medium_risk', summary.medium_risk ?? 0);
        setCard(widget, 'high_risk', summary.high_risk ?? 0);
        renderBars(widget.querySelector('[data-risk-widget-bars]'), payload.charts?.risk_distribution);
        renderStudents(widget.querySelector('[data-risk-widget-students]'), payload.top_high_risk_students || []);
    }

    function setCard(widget, key, value) {
        const element = widget.querySelector(`[data-risk-card="${key}"]`);
        if (element) element.textContent = String(value);
    }

    function renderBars(container, chartData) {
        if (!container) return;

        const labels = chartData?.labels || [];
        const values = chartData?.values || [];
        const maxValue = Math.max(1, ...values.map(Number));

        if (!labels.length) {
            container.innerHTML = '<p class="text-xs text-gray-500">No risk distribution data yet.</p>';
            return;
        }

        container.innerHTML = labels.map((label, index) => {
            const value = Number(values[index] || 0);
            const width = Math.max(4, (value / maxValue) * 100);

            return `
                <div class="mb-2">
                    <div class="mb-1 flex items-center justify-between text-xs text-gray-600">
                        <span class="font-semibold">${escapeHtml(label)}</span>
                        <span>${value}</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full ${barClass(label)}" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderStudents(container, students) {
        if (!container) return;

        if (!students.length) {
            container.innerHTML = '<tr><td colspan="3" class="py-4 text-center text-gray-500">No high-risk students detected.</td></tr>';
            return;
        }

        container.innerHTML = students.slice(0, 5).map((student) => `
            <tr class="border-t border-gray-200">
                <td class="py-2 pr-3 font-semibold text-gray-800">${escapeHtml(student.student_name)}</td>
                <td class="py-2 pr-3 text-gray-700">${escapeHtml(student.risk_score)}</td>
                <td class="py-2 pr-3 text-gray-600">${escapeHtml(student.primary_reason)}</td>
            </tr>
        `).join('');
    }

    function renderError(widget, message) {
        const target = widget.querySelector('[data-risk-widget-bars]');
        if (target) {
            target.innerHTML = `<p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">${escapeHtml(message || 'Risk widget unavailable.')}</p>`;
        }
    }

    function barClass(label) {
        if (label === 'High Risk') return 'bg-red-500';
        if (label === 'Medium Risk') return 'bg-amber-500';
        if (label === 'Low Risk') return 'bg-emerald-500';
        return 'bg-gray-700';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
