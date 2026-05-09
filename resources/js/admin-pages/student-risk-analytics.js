/*
  resources/js/admin-pages/student-risk-analytics.js

  Student Risk Analytics page controller.
  Responsibilities:
  - Fetch Python-generated JSON through Laravel endpoint
  - Render compact summary cards, animated bars, and tables
  - Search/filter students on the client side
  - Provide inline expandable risk reasons

  Code-smell prevention:
  - No global variables except the Laravel config object
  - Small rendering functions
  - No duplicated table-row construction logic
  - Route URLs are read from window.studentRiskAnalyticsConfig
*/

(() => {
    'use strict';

    const config = window.studentRiskAnalyticsConfig || {};

    const state = {
        students: [],
        lastPayload: null,
        expandedStudentIds: new Set(),
        countdownSeconds: 0,
        countdownTimer: null,
        isLoading: false,
    };

    const palette = {
        primary: '#2F4F4F',
        secondary: '#3C4B33',
        gold: '#B4833D',
        sage: '#959D90',
        light: '#D8D9DA',
        dark: '#272829',
        slate: '#394a56',
        high: '#9f1d20',
        warm: '#fcf3e3',
    };

    const riskStyles = {
        'Low Risk': {
            text: 'text-[#2F4F4F]',
            bg: 'bg-[#2F4F4F]/10',
            border: 'border-[#2F4F4F]/30',
            color: palette.primary,
        },
        'Medium Risk': {
            text: 'text-[#B4833D]',
            bg: 'bg-[#B4833D]/10',
            border: 'border-[#B4833D]/30',
            color: palette.gold,
        },
        'High Risk': {
            text: 'text-[#9f1d20]',
            bg: 'bg-[#9f1d20]/10',
            border: 'border-[#9f1d20]/30',
            color: palette.high,
        },
    };

    const elements = {
        notice: document.getElementById('riskStatusNotice'),
        refreshButton: document.getElementById('riskRefreshButton'),
        exportButton: document.getElementById('riskExportButton'),
        exportPdfButton: document.getElementById('riskExportPdfButton'),
        countdown: document.getElementById('riskCountdownText'),
        total: document.getElementById('riskTotalStudents'),
        low: document.getElementById('riskLowCount'),
        medium: document.getElementById('riskMediumCount'),
        high: document.getElementById('riskHighCount'),
        generatedAt: document.getElementById('riskGeneratedAt'),
        distributionBars: document.getElementById('riskDistributionBars'),
        factorBars: document.getElementById('riskFactorBars'),
        topTable: document.getElementById('topRiskStudentsTable'),
        allTable: document.getElementById('allRiskStudentsTable'),
        instructorTable: document.getElementById('instructorRiskTable'),
        searchInput: document.getElementById('riskSearchInput'),
        levelFilter: document.getElementById('riskLevelFilter'),
        clearFiltersButton: document.getElementById('riskClearFiltersButton'),
    };

    document.addEventListener('DOMContentLoaded', initialize);

    function initialize() {
        bindEvents();
        startAutoRefreshCountdown();
        loadAnalytics({ resetCountdown: true });
    }

    function bindEvents() {
        elements.refreshButton?.addEventListener('click', () => {
            loadAnalytics({ resetCountdown: true });
        });

        elements.exportButton?.addEventListener('click', () => {
            if (config.exportCsvUrl) {
                window.location.href = config.exportCsvUrl;
            }
        });

        elements.exportPdfButton?.addEventListener('click', () => {
            if (config.exportPdfUrl) {
                window.location.href = config.exportPdfUrl;
            }
        });

        elements.searchInput?.addEventListener('input', renderFilteredStudents);
        elements.levelFilter?.addEventListener('change', renderFilteredStudents);

        elements.clearFiltersButton?.addEventListener('click', () => {
            if (elements.searchInput) elements.searchInput.value = '';
            if (elements.levelFilter) elements.levelFilter.value = 'all';
            renderFilteredStudents();
        });

        elements.allTable?.addEventListener('click', handleStudentRowClick);
    }

    async function loadAnalytics(options = {}) {
        if (!config.dataUrl) {
            showNotice('Student Risk Analytics URL is missing.', 'error');
            return;
        }

        if (state.isLoading) {
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
            renderInstructorTable(payload.instructor_summary || []);
            showNotice('Student risk analytics updated successfully.', 'success');
        } catch (error) {
            showNotice(error.message || 'Failed to load analytics.', 'error');
        } finally {
            setLoading(false);
            if (options.resetCountdown !== false) {
                resetCountdown();
            }
        }
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;

        if (!elements.refreshButton) return;

        elements.refreshButton.disabled = isLoading;
        elements.refreshButton.textContent = isLoading ? 'Refreshing...' : 'Refresh Analysis';
    }

    function startAutoRefreshCountdown() {
        const refreshMs = Number(config.refreshMs || 60000);

        if (!elements.countdown || refreshMs <= 0) {
            return;
        }

        state.countdownSeconds = Math.max(1, Math.floor(refreshMs / 1000));
        updateCountdownText();

        state.countdownTimer = window.setInterval(() => {
            state.countdownSeconds -= 1;

            if (state.countdownSeconds <= 0) {
                loadAnalytics({ resetCountdown: true });
                return;
            }

            updateCountdownText();
        }, 1000);
    }

    function resetCountdown() {
        const refreshMs = Number(config.refreshMs || 60000);
        state.countdownSeconds = Math.max(1, Math.floor(refreshMs / 1000));
        updateCountdownText();
    }

    function updateCountdownText() {
        if (!elements.countdown) return;

        elements.countdown.textContent = `Next refresh in ${state.countdownSeconds}s`;
    }

    function renderSummary(summary) {
        animateCount(elements.total, Number(summary.total_students_analyzed ?? 0));
        animateCount(elements.low, Number(summary.low_risk ?? 0));
        animateCount(elements.medium, Number(summary.medium_risk ?? 0));
        animateCount(elements.high, Number(summary.high_risk ?? 0));
    }

    function animateCount(element, targetValue, durationMs = 800) {
        if (!element) return;

        const startValue = Number(element.dataset.currentValue || parseNumericText(element.textContent) || 0);
        const target = Number.isFinite(targetValue) ? targetValue : 0;
        const startTime = performance.now();

        function step(now) {
            const progress = Math.min((now - startTime) / durationMs, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(startValue + (target - startValue) * eased);

            element.textContent = formatInteger(current);

            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                element.textContent = formatInteger(target);
                element.dataset.currentValue = String(target);
            }
        }

        window.requestAnimationFrame(step);
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
            const color = riskColor(label);

            return `
                <div>
                    <div class="mb-1 flex items-center justify-between gap-3 text-xs text-[#61677A]">
                        <span class="font-semibold">${escapeHtml(label)}</span>
                        <span class="risk-mono text-[#394a56]">${formatInteger(value)}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-[#D8D9DA]/50">
                        <div class="risk-bar-fill h-full rounded-full" data-target-width="${width}" style="background-color: ${color}; width: 0%;"></div>
                    </div>
                </div>
            `;
        }).join('');

        playBarAnimations(container);
    }

    function renderRiskFactors(factors) {
        if (!elements.factorBars) return;

        if (!factors.length) {
            elements.factorBars.innerHTML = emptyMessage('No recurring risk factors detected.');
            return;
        }

        const maxValue = Math.max(1, ...factors.map((item) => Number(item.count || 0)));

        elements.factorBars.innerHTML = factors.map((item) => {
            const count = Number(item.count || 0);
            const width = Math.max(4, (count / maxValue) * 100);
            const factor = item.factor || 'Other risk factor';

            return `
                <div>
                    <div class="mb-1 flex items-start justify-between gap-3 text-xs text-[#61677A]">
                        <span class="max-w-[82%] truncate font-semibold leading-snug" title="${escapeAttribute(factor)}">${escapeHtml(truncateText(factor, 74))}</span>
                        <span class="risk-mono text-[#394a56]">${formatInteger(count)}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-[#D8D9DA]/50">
                        <div class="risk-bar-fill h-full rounded-full" data-target-width="${width}" style="background-color: ${palette.slate}; width: 0%;"></div>
                    </div>
                </div>
            `;
        }).join('');

        playBarAnimations(elements.factorBars);
    }

    function playBarAnimations(container) {
        const bars = container.querySelectorAll('.risk-bar-fill');

        window.setTimeout(() => {
            bars.forEach((bar) => {
                const targetWidth = Number(bar.dataset.targetWidth || 0);
                bar.style.width = `${targetWidth}%`;
            });
        }, 60);
    }

    function renderTopStudents(students) {
        if (!elements.topTable) return;

        if (!students.length) {
            elements.topTable.innerHTML = tableMessage(7, 'No high-risk students detected.');
            return;
        }

        elements.topTable.innerHTML = students.map((student) => `
            <tr class="hover:bg-[#FFF6E0]/50">
                <td class="px-4 py-3">
                    <div class="font-semibold text-[#272829]">${escapeHtml(student.student_name)}</div>
                    <div class="text-xs text-[#61677A]">${escapeHtml(student.instrument_name || 'No instrument')}</div>
                </td>
                <td class="px-4 py-3">${riskBadge(student.risk_level)}</td>
                <td class="risk-mono px-4 py-3 text-[#394a56]">${formatPercent(student.attendance_rate)}</td>
                <td class="risk-mono px-4 py-3 text-[#394a56]">${formatProgressRating(student.average_progress_rating)}</td>
                <td class="px-4 py-3 text-[#394a56]">${escapeHtml(student.payment_status || 'N/A')}</td>
                <td class="px-4 py-3 text-[#394a56]" title="${escapeAttribute(student.primary_reason || '')}">${escapeHtml(truncateText(student.primary_reason || 'No reason', 70))}</td>
                <td class="px-4 py-3 text-[#394a56]" title="${escapeAttribute(student.recommended_action || '')}">${escapeHtml(truncateText(student.recommended_action || 'Continue monitoring', 76))}</td>
            </tr>
        `).join('');
    }

    function renderFilteredStudents() {
        if (!elements.allTable) return;

        const search = (elements.searchInput?.value || '').toLowerCase().trim();
        const selectedLevel = elements.levelFilter?.value || 'all';

        const filtered = state.students.filter((student) => {
            const haystack = [
                student.student_name,
                student.instrument_name,
                student.instructor_name,
                student.primary_reason,
            ].join(' ').toLowerCase();

            const matchesSearch = !search || haystack.includes(search);
            const matchesLevel = selectedLevel === 'all' || student.risk_level === selectedLevel;

            return matchesSearch && matchesLevel;
        });

        if (!filtered.length) {
            elements.allTable.innerHTML = tableMessage(7, 'No students match the current filters.');
            return;
        }

        elements.allTable.innerHTML = filtered.map((student) => renderStudentRows(student)).join('');
    }

    function renderStudentRows(student) {
        const studentId = String(student.student_id);
        const isExpanded = state.expandedStudentIds.has(studentId);
        const reasonList = Array.isArray(student.risk_reasons) ? student.risk_reasons : [];

        return `
            <tr class="cursor-pointer hover:bg-[#FFF6E0]/50 ${isExpanded ? 'risk-row-expanded bg-[#FFF6E0]/30' : ''}" data-student-risk-row="${escapeAttribute(studentId)}">
                <td class="px-4 py-3">
                    <div class="font-semibold text-[#272829]">${escapeHtml(student.student_name)}</div>
                    <div class="text-xs text-[#61677A]">ID: ${escapeHtml(studentId)}</div>
                </td>
                <td class="px-4 py-3 text-[#394a56]">${escapeHtml(student.instrument_name || 'No instrument')}</td>
                <td class="px-4 py-3 text-[#394a56]">${escapeHtml(student.instructor_name || 'Unassigned')}</td>
                <td class="px-4 py-3">${riskBadge(student.risk_level)}</td>
                <td class="risk-mono px-4 py-3 text-[#394a56]">${formatNumber(student.risk_score)}</td>
                <td class="px-4 py-3 text-[#394a56]" title="${escapeAttribute(student.primary_reason || '')}">${escapeHtml(truncateText(student.primary_reason || 'No reason', 78))}</td>
                <td class="px-4 py-3 text-right text-[#61677A]">
                    <span class="risk-row-chevron inline-block text-base">›</span>
                </td>
            </tr>
            ${isExpanded ? renderExpandedReasonRow(student, reasonList) : ''}
        `;
    }

    function renderExpandedReasonRow(student, reasons) {
        const reasonItems = reasons.length
            ? reasons.map((reason) => `<li>${escapeHtml(reason)}</li>`).join('')
            : '<li>No detailed reason recorded.</li>';

        return `
            <tr class="bg-[#fcf3e3]">
                <td colspan="7" class="px-4 py-4">
                    <div class="grid grid-cols-1 gap-4 text-sm text-[#394a56] lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#61677A]">All Risk Reasons</p>
                            <ul class="ml-4 list-disc space-y-1 leading-5">
                                ${reasonItems}
                            </ul>
                        </div>
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-[#61677A]">Recommended Action</p>
                            <p class="leading-5">${escapeHtml(student.recommended_action || 'Continue monitoring.')}</p>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    }

    function handleStudentRowClick(event) {
        const row = event.target.closest('[data-student-risk-row]');
        if (!row) return;

        const studentId = String(row.dataset.studentRiskRow || '');
        if (!studentId) return;

        if (state.expandedStudentIds.has(studentId)) {
            state.expandedStudentIds.delete(studentId);
        } else {
            state.expandedStudentIds.add(studentId);
        }

        renderFilteredStudents();
    }

    function renderInstructorTable(instructorSummary) {
        if (!elements.instructorTable) return;

        if (!Array.isArray(instructorSummary) || !instructorSummary.length) {
            elements.instructorTable.innerHTML = tableMessage(5, 'No instructor risk data available.');
            return;
        }

        const sorted = [...instructorSummary].sort((first, second) => {
            const highDifference = Number(second.high_risk || 0) - Number(first.high_risk || 0);
            if (highDifference !== 0) return highDifference;

            const mediumDifference = Number(second.medium_risk || 0) - Number(first.medium_risk || 0);
            if (mediumDifference !== 0) return mediumDifference;

            return Number(second.total_students || 0) - Number(first.total_students || 0);
        });

        elements.instructorTable.innerHTML = sorted.map((item) => `
            <tr class="hover:bg-[#FFF6E0]/50">
                <td class="px-4 py-3 font-semibold text-[#272829]">${escapeHtml(item.instructor_name || 'Unassigned')}</td>
                <td class="risk-mono px-4 py-3 text-[#394a56]">${formatInteger(item.total_students || 0)}</td>
                <td class="risk-mono px-4 py-3 text-[#2F4F4F]">${formatInteger(item.low_risk || 0)}</td>
                <td class="risk-mono px-4 py-3 text-[#B4833D]">${formatInteger(item.medium_risk || 0)}</td>
                <td class="risk-mono px-4 py-3 text-[#9f1d20]">${formatInteger(item.high_risk || 0)}</td>
            </tr>
        `).join('');
    }

    function riskBadge(level) {
        const style = riskStyles[level] || riskStyles['Low Risk'];

        return `
            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold ${style.bg} ${style.text} ${style.border}">
                <span class="risk-badge-dot"></span>
                ${escapeHtml(level || 'Low Risk')}
            </span>
        `;
    }

    function riskColor(label) {
        return riskStyles[label]?.color || palette.slate;
    }

    function showNotice(message, type) {
        if (!elements.notice) return;

        const isError = type === 'error';
        elements.notice.classList.remove('hidden');
        elements.notice.textContent = message;
        elements.notice.className = isError
            ? 'mb-4 rounded-[20px] border border-[#9f1d20]/20 bg-[#9f1d20]/5 px-4 py-3 text-sm text-[#9f1d20] shadow-sm'
            : 'mb-4 rounded-[20px] border border-[#2F4F4F]/20 bg-[#2F4F4F]/5 px-4 py-3 text-sm text-[#2F4F4F] shadow-sm';
    }

    function tableMessage(colspan, message) {
        return `<tr><td colspan="${colspan}" class="px-4 py-6 text-center text-sm text-[#61677A]">${escapeHtml(message)}</td></tr>`;
    }

    function emptyMessage(message) {
        return `<p class="rounded-2xl bg-[#D8D9DA]/25 px-3 py-2 text-sm text-[#61677A]">${escapeHtml(message)}</p>`;
    }

    function setText(element, value) {
        if (element) element.textContent = String(value);
    }

    function formatProgressRating(value) {
        if (value === null || value === undefined || value === '') {
            return 'N/A';
        }

        return `${formatNumber(value)}/10`;
    }

    function formatPercent(value) {
        return `${formatNumber(value)}%`;
    }

    function formatNumber(value) {
        const number = Number(value || 0);
        return Number.isInteger(number) ? String(number) : number.toFixed(2);
    }

    function formatInteger(value) {
        return new Intl.NumberFormat('en-US').format(Math.round(Number(value || 0)));
    }

    function parseNumericText(value) {
        const parsed = Number(String(value || '').replace(/[^0-9.-]/g, ''));
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function truncateText(value, maxLength) {
        const text = String(value ?? '');
        return text.length > maxLength ? `${text.slice(0, maxLength - 1)}…` : text;
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
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
