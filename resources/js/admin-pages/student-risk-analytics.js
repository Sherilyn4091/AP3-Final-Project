/*
  resources/js/admin-pages/student-risk-analytics.js

  Student Risk Analytics page controller.
  Responsibilities:
  - Fetch Python-generated JSON through Laravel endpoint
  - Render compact summary cards, HTML bar charts, and tables
  - Filter/search students on the client side
*/

(() => {
    'use strict';

    const config = window.studentRiskAnalyticsConfig || {};
    const state = {
        students: [],
        lastPayload: null,
    };

    const riskClasses = {
        'Low Risk': 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Medium Risk': 'bg-amber-50 text-amber-700 border-amber-200',
        'High Risk': 'bg-red-50 text-red-700 border-red-200',
    };

    const elements = {
        notice: document.getElementById('riskStatusNotice'),
        refreshButton: document.getElementById('riskRefreshButton'),
        total: document.getElementById('riskTotalStudents'),
        low: document.getElementById('riskLowCount'),
        medium: document.getElementById('riskMediumCount'),
        high: document.getElementById('riskHighCount'),
        generatedAt: document.getElementById('riskGeneratedAt'),
        distributionBars: document.getElementById('riskDistributionBars'),
        factorBars: document.getElementById('riskFactorBars'),
        topTable: document.getElementById('topRiskStudentsTable'),
        allTable: document.getElementById('allRiskStudentsTable'),
        searchInput: document.getElementById('riskSearchInput'),
        levelFilter: document.getElementById('riskLevelFilter'),
        clearFiltersButton: document.getElementById('riskClearFiltersButton'),
    };

    document.addEventListener('DOMContentLoaded', initialize);

    function initialize() {
        bindEvents();
        loadAnalytics();

        const refreshMs = Number(config.refreshMs || 60000);
        if (refreshMs > 0) {
            window.setInterval(loadAnalytics, refreshMs);
        }
    }

    function bindEvents() {
        elements.refreshButton?.addEventListener('click', loadAnalytics);
        elements.searchInput?.addEventListener('input', renderFilteredStudents);
        elements.levelFilter?.addEventListener('change', renderFilteredStudents);

        elements.clearFiltersButton?.addEventListener('click', () => {
            if (elements.searchInput) elements.searchInput.value = '';
            if (elements.levelFilter) elements.levelFilter.value = 'all';
            renderFilteredStudents();
        });
    }

    async function loadAnalytics() {
        if (!config.dataUrl) {
            showNotice('Student Risk Analytics URL is missing.', 'error');
            return;
        }

        setLoading(true);

        try {
            const response = await fetch(config.dataUrl, {
                headers: { Accept: 'application/json' },
            });

            const payload = await response.json();

            if (!response.ok || payload.ok !== true) {
                throw new Error(payload.message || 'Unable to load student risk analytics.');
            }

            state.lastPayload = payload;
            state.students = Array.isArray(payload.students) ? payload.students : [];

            renderSummary(payload.summary || {});
            renderGeneratedAt(payload.generated_at);
            renderBarChart(elements.distributionBars, payload.charts?.risk_distribution);
            renderRiskFactors(payload.factor_summary || []);
            renderTopStudents(payload.top_high_risk_students || []);
            renderFilteredStudents();
            showNotice('Student risk analytics updated successfully.', 'success');
        } catch (error) {
            showNotice(error.message || 'Failed to load analytics.', 'error');
        } finally {
            setLoading(false);
        }
    }

    function setLoading(isLoading) {
        if (!elements.refreshButton) return;

        elements.refreshButton.disabled = isLoading;
        elements.refreshButton.textContent = isLoading ? 'Refreshing...' : 'Refresh Analysis';
    }

    function renderSummary(summary) {
        setText(elements.total, summary.total_students_analyzed ?? 0);
        setText(elements.low, summary.low_risk ?? 0);
        setText(elements.medium, summary.medium_risk ?? 0);
        setText(elements.high, summary.high_risk ?? 0);
    }

    function renderGeneratedAt(value) {
        if (!elements.generatedAt) return;

        if (!value) {
            elements.generatedAt.textContent = 'Not yet generated';
            return;
        }

        const date = new Date(value);
        elements.generatedAt.textContent = `Updated ${date.toLocaleString()}`;
    }

    function renderBarChart(container, chartData) {
        if (!container) return;

        const labels = chartData?.labels || [];
        const values = chartData?.values || [];
        const maxValue = Math.max(1, ...values.map(Number));

        if (!labels.length) {
            container.innerHTML = emptyMessage('No chart data available.');
            return;
        }

        container.innerHTML = labels.map((label, index) => {
            const value = Number(values[index] || 0);
            const width = Math.max(4, (value / maxValue) * 100);
            const barClass = riskBarClass(label);

            return `
                <div>
                    <div class="mb-1 flex items-center justify-between text-xs text-gray-600">
                        <span class="font-semibold">${escapeHtml(label)}</span>
                        <span>${value}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full ${barClass}" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderRiskFactors(factors) {
        if (!elements.factorBars) return;

        if (!factors.length) {
            elements.factorBars.innerHTML = emptyMessage('No recurring risk factors detected.');
            return;
        }

        const maxValue = Math.max(1, ...factors.map((item) => Number(item.count || 0)));

        elements.factorBars.innerHTML = factors.map((item) => {
            const width = Math.max(4, (Number(item.count || 0) / maxValue) * 100);

            return `
                <div>
                    <div class="mb-1 flex items-start justify-between gap-3 text-xs text-gray-600">
                        <span class="font-semibold leading-snug">${escapeHtml(item.factor)}</span>
                        <span>${Number(item.count || 0)}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-gray-700" style="width: ${width}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderTopStudents(students) {
        if (!elements.topTable) return;

        if (!students.length) {
            elements.topTable.innerHTML = tableMessage(7, 'No high-risk students detected.');
            return;
        }

        elements.topTable.innerHTML = students.map((student) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900">${escapeHtml(student.student_name)}</div>
                    <div class="text-xs text-gray-500">${escapeHtml(student.instrument_name || 'No instrument')}</div>
                </td>
                <td class="px-4 py-3">${riskBadge(student.risk_level)}</td>
                <td class="px-4 py-3 text-gray-700">${formatPercent(student.attendance_rate)}</td>
                <td class="px-4 py-3 text-gray-700">${formatNumber(student.average_progress_rating)}/10</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.payment_status || 'N/A')}</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.primary_reason || 'No reason')}</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.recommended_action || 'Continue monitoring')}</td>
            </tr>
        `).join('');
    }

    function renderFilteredStudents() {
        if (!elements.allTable) return;

        const search = (elements.searchInput?.value || '').trim().toLowerCase();
        const selectedRisk = elements.levelFilter?.value || 'all';

        const filtered = state.students.filter((student) => {
            const haystack = [
                student.student_name,
                student.instrument_name,
                student.instructor_name,
                student.primary_reason,
                student.payment_status,
            ].join(' ').toLowerCase();

            const matchesSearch = !search || haystack.includes(search);
            const matchesRisk = selectedRisk === 'all' || student.risk_level === selectedRisk;

            return matchesSearch && matchesRisk;
        });

        if (!filtered.length) {
            elements.allTable.innerHTML = tableMessage(6, 'No students match the current filters.');
            return;
        }

        elements.allTable.innerHTML = filtered.map((student) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-900">${escapeHtml(student.student_name)}</div>
                    <div class="text-xs text-gray-500">${escapeHtml(student.email || '')}</div>
                </td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.instrument_name || 'N/A')}</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.instructor_name || 'Unassigned')}</td>
                <td class="px-4 py-3">${riskBadge(student.risk_level)}</td>
                <td class="px-4 py-3 text-gray-700">${formatNumber(student.risk_score)}</td>
                <td class="px-4 py-3 text-gray-700">${escapeHtml(student.primary_reason || 'No major risk detected.')}</td>
            </tr>
        `).join('');
    }

    function riskBadge(riskLevel) {
        const badgeClass = riskClasses[riskLevel] || 'bg-gray-50 text-gray-700 border-gray-200';

        return `<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${badgeClass}">${escapeHtml(riskLevel || 'Unknown')}</span>`;
    }

    function riskBarClass(label) {
        if (label === 'High Risk') return 'bg-red-500';
        if (label === 'Medium Risk') return 'bg-amber-500';
        if (label === 'Low Risk') return 'bg-emerald-500';
        return 'bg-gray-700';
    }

    function showNotice(message, type = 'info') {
        if (!elements.notice) return;

        const typeClass = type === 'error'
            ? 'border-red-200 bg-red-50 text-red-700'
            : type === 'success'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                : 'border-gray-200 bg-white text-gray-700';

        elements.notice.className = `mb-4 rounded-xl border px-4 py-3 text-sm shadow-sm ${typeClass}`;
        elements.notice.textContent = message;
        elements.notice.classList.remove('hidden');
    }

    function tableMessage(colspan, message) {
        return `<tr><td colspan="${colspan}" class="px-4 py-6 text-center text-sm text-gray-500">${escapeHtml(message)}</td></tr>`;
    }

    function emptyMessage(message) {
        return `<p class="rounded-xl bg-gray-50 px-3 py-4 text-center text-sm text-gray-500">${escapeHtml(message)}</p>`;
    }

    function setText(element, value) {
        if (element) element.textContent = String(value);
    }

    function formatPercent(value) {
        return `${formatNumber(value)}%`;
    }

    function formatNumber(value) {
        const number = Number(value || 0);
        return Number.isInteger(number) ? String(number) : number.toFixed(2);
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
