/*
|--------------------------------------------------------------------------
| resources/js/admin-pages/chart-utils.js
|--------------------------------------------------------------------------
|
| Shared Chart Utilities for Music Lab Admin Analytics
|
| Purpose:
| - Keeps chart colors consistent across dashboard and reports.
| - Keeps number animation reusable.
| - Cleans common encoding/mojibake symbols safely.
|
*/

export const PALETTE = {
    primary: '#2F4F4F',
    secondary: '#3C4B33',
    brown: '#B4833D',
    sage: '#959D90',
    slate: '#44576D',
    darkBrown: '#523D35',
    dim: '#768A96',
    deepBrown: '#42300B',
    lightBorder: '#D8DDD8',
    background: '#FCFCFA',
};

/*
|--------------------------------------------------------------------------
| Readable Doughnut Colors
|--------------------------------------------------------------------------
|
| The Music Lab palette is intentionally muted. Doughnut charts need stronger
| contrast so adjacent segments remain readable and user-friendly.
|
*/
export const DOUGHNUT_COLORS = [
    '#2563EB', // Blue
    '#16A34A', // Green
    '#F97316', // Orange
    '#9333EA', // Purple
    '#DC2626', // Red
    '#0891B2', // Cyan
    '#CA8A04', // Yellow/Gold
    '#DB2777', // Pink
    '#4F46E5', // Indigo
    '#64748B', // Slate
];

/*
|--------------------------------------------------------------------------
| Mojibake Cleanup
|--------------------------------------------------------------------------
|
| This is only a client-side safety net. The permanent Blade fix is to use:
| - &#8369; for peso signs
| - &bull; for bullet separators
| - &copy; for copyright symbols
|
| Array pairs are used instead of an object so duplicate-looking mojibake
| sequences are not accidentally overwritten.
|
*/
const MOJIBAKE_REPLACEMENTS = [
    ['\u00C3\u00A2\u00E2\u20AC\u0161\u00C2\u00B1', '\u20B1'],
    ['\u00E2\u201A\u00B1', '\u20B1'],
    ['\u00C2\u20B1', '\u20B1'],
    ['\u00E2\u20AC\u00A2', '\u2022'],
    ['\u00C2\u00A9', '\u00A9'],
    ['\u00E2\u20AC\u201D', '\u2014'],
    ['\u00E2\u20AC\u201C', '\u2013'],
    ['\u00E2\u20AC\u02DC', '\u2018'],
    ['\u00E2\u20AC\u2122', '\u2019'],
    ['\u00E2\u20AC\u0153', '\u201C'],
    ['\u00E2\u20AC\u009D', '\u201D'],
];

export function cleanMojibakeText() {
    if (!document.body) {
        return;
    }

    const walker = document.createTreeWalker(
        document.body,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode(node) {
                const parent = node.parentElement;

                if (!parent || ['SCRIPT', 'STYLE', 'TEXTAREA'].includes(parent.tagName)) {
                    return NodeFilter.FILTER_REJECT;
                }

                return NodeFilter.FILTER_ACCEPT;
            },
        }
    );

    while (walker.nextNode()) {
        let text = walker.currentNode.nodeValue;

        MOJIBAKE_REPLACEMENTS.forEach(([badText, goodText]) => {
            text = text.split(badText).join(goodText);
        });

        walker.currentNode.nodeValue = text;
    }
}

export function toNumber(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
}

export function formatNumber(value, options = {}) {
    const prefix = options.prefix || '';
    const suffix = options.suffix || '';
    const decimals = Number(options.decimals || 0);

    return prefix + Number(value).toLocaleString('en-PH', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }) + suffix;
}

export function animateElementNumber(element, target, options = {}) {
    if (!element) {
        return;
    }

    const prefix = options.prefix || '';
    const suffix = options.suffix || '';
    const decimals = Number(options.decimals || 0);
    const duration = Number(options.duration || 1200);
    const startTime = performance.now();

    function step(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = 1 - Math.pow(1 - progress, 4);
        const currentValue = toNumber(target) * easedProgress;

        element.textContent = formatNumber(currentValue, {
            prefix,
            suffix,
            decimals,
        });

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}

export function animateExistingCounters() {
    document.querySelectorAll('[data-count-up]').forEach(element => {
        animateElementNumber(element, toNumber(element.dataset.target), {
            prefix: element.dataset.prefix || '',
            suffix: element.dataset.suffix || '',
            decimals: Number(element.dataset.decimals || 0),
        });
    });
}

export function formatDateLabel(value) {
    if (!value) {
        return 'N/A';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return date.toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
    });
}