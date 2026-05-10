/*
|--------------------------------------------------------------------------
| Admin UI Animations
|--------------------------------------------------------------------------
|
| Purpose:
| - Adds smooth page animations to Admin pages.
| - Animates cards, filters, tables, rows, buttons, and modals.
| - Animates card numbers from 0 up to their real count.
|
| Notes:
| - This file is reusable.
| - It does not change backend data.
| - It only improves display and user experience.
| - It respects users who prefer reduced motion.
|
*/

document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    injectAdminAnimationStyles();

    if (prefersReducedMotion) {
        return;
    }

    animatePageEntrance();
    animateCards();
    animateTables();
    animateButtons();
    observeDynamicModals();
});

/*
|--------------------------------------------------------------------------
| Inject Animation Styles
|--------------------------------------------------------------------------
|
| Keeps the animation styles in one JS file so you do not need to edit
| every Blade file again.
|
*/
function injectAdminAnimationStyles() {
    if (document.getElementById('admin-ui-animation-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'admin-ui-animation-styles';

    style.textContent = `
        :root {
            --admin-ease-soft: cubic-bezier(0.22, 1, 0.36, 1);
            --admin-ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes adminFadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes adminFadeSlideDown {
            from {
                opacity: 0;
                transform: translateY(-12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes adminSoftPop {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(12px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes adminRowReveal {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes adminModalReveal {
            from {
                opacity: 0;
                transform: scale(0.96) translateY(12px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .admin-animate-header {
            animation: adminFadeSlideDown 520ms var(--admin-ease-soft) both;
        }

        .admin-animate-card {
            opacity: 0;
            animation: adminSoftPop 650ms var(--admin-ease-bounce) both;
            will-change: transform, opacity;
        }

        .admin-animate-panel {
            opacity: 0;
            animation: adminFadeSlideUp 560ms var(--admin-ease-soft) both;
            will-change: transform, opacity;
        }

        .admin-animate-table-row {
            opacity: 0;
            animation: adminRowReveal 460ms var(--admin-ease-soft) both;
        }

        .admin-animated-button {
            transition:
                transform 180ms ease,
                box-shadow 180ms ease,
                filter 180ms ease;
        }

        .admin-animated-button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .admin-animated-button:active {
            transform: translateY(0) scale(0.98);
        }

        .admin-animated-modal > * {
            animation: adminModalReveal 280ms var(--admin-ease-soft) both;
        }

        .admin-counting-number {
            font-variant-numeric: tabular-nums;
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-animate-header,
            .admin-animate-card,
            .admin-animate-panel,
            .admin-animate-table-row,
            .admin-animated-modal > * {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }

            .admin-animated-button {
                transition: none !important;
            }
        }
    `;

    document.head.appendChild(style);
}

/*
|--------------------------------------------------------------------------
| Page Entrance Animation
|--------------------------------------------------------------------------
|
| Smoothly reveals the page header and main panels.
|
*/
function animatePageEntrance() {
    const header = document.querySelector('main > header');

    if (header) {
        header.classList.add('admin-animate-header');
    }

    const panels = document.querySelectorAll('main form, main .overflow-x-auto, main footer');

    panels.forEach((panel, index) => {
        panel.classList.add('admin-animate-panel');
        panel.style.animationDelay = `${120 + index * 70}ms`;
    });
}

/*
|--------------------------------------------------------------------------
| Card Animation + Count Up
|--------------------------------------------------------------------------
|
| Cards will softly appear, then their visible numeric values will count
| from 0 to the real value.
|
*/
function animateCards() {
    const cards = document.querySelectorAll('.card');

    cards.forEach((card, index) => {
        card.classList.add('admin-animate-card');
        card.style.animationDelay = `${80 + index * 80}ms`;

        const numberElement = findCardNumberElement(card);

        if (numberElement) {
            animateNumber(numberElement, 1000 + index * 80);
        }
    });
}

/*
|--------------------------------------------------------------------------
| Find Card Number
|--------------------------------------------------------------------------
|
| Finds the main number inside a card without touching labels.
|
*/
function findCardNumberElement(card) {
    const candidates = card.querySelectorAll(
        '.text-3xl, .text-2xl, .text-xl, [data-count], [data-animate-count]'
    );

    for (const element of candidates) {
        const text = element.textContent.trim();

        if (hasNumericValue(text)) {
            return element;
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| Number Counter
|--------------------------------------------------------------------------
|
| Supports:
| - 102
| - 1,250
| - 4.8
| - ₱12,500
| - 75%
|
*/
function animateNumber(element, duration = 1100) {
    if (element.dataset.countAnimated === 'true') {
        return;
    }

    const originalText = element.textContent.trim();
    const numericValue = extractNumericValue(originalText);

    if (numericValue === null) {
        return;
    }

    const prefix = originalText.match(/^[^\d.-]*/)?.[0] || '';
    const suffix = originalText.match(/[^\d.]*$/)?.[0] || '';
    const hasDecimal = originalText.includes('.');
    const decimalPlaces = hasDecimal ? getDecimalPlaces(originalText) : 0;

    element.dataset.countAnimated = 'true';
    element.classList.add('admin-counting-number');

    const startTime = performance.now();

    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeOutCubic(progress);
        const currentValue = numericValue * easedProgress;

        element.textContent = formatAnimatedValue(
            currentValue,
            numericValue,
            prefix,
            suffix,
            decimalPlaces
        );

        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = originalText;
        }
    }

    requestAnimationFrame(updateCounter);
}

/*
|--------------------------------------------------------------------------
| Table Animation
|--------------------------------------------------------------------------
|
| Adds a light stagger effect to table rows.
|
*/
function animateTables() {
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach((row, index) => {
        row.classList.add('admin-animate-table-row');
        row.style.animationDelay = `${220 + index * 28}ms`;
    });
}

/*
|--------------------------------------------------------------------------
| Button Animation
|--------------------------------------------------------------------------
|
| Adds smooth hover/press behavior to buttons and action links.
|
*/
function animateButtons() {
    const buttons = document.querySelectorAll(
        'button, a.bg-forest-green, a.bg-secondary-blue, a.bg-gray-200, .action-btn'
    );

    buttons.forEach((button) => {
        button.classList.add('admin-animated-button');
    });
}

/*
|--------------------------------------------------------------------------
| Dynamic Modal Animation
|--------------------------------------------------------------------------
|
| Watches modal containers populated by JS and animates them when shown.
|
*/
function observeDynamicModals() {
    const modalSelectors = [
        '#student-detail-modal',
        '#bulk-status-modal',
        '#instructor-detail-modal',
        '#specialization-modal',
        '#performance-modal',
        '#reset-password-modal'
    ];

    modalSelectors.forEach((selector) => {
        const modal = document.querySelector(selector);

        if (!modal) {
            return;
        }

        const observer = new MutationObserver(() => {
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('admin-animated-modal');
            }
        });

        observer.observe(modal, {
            attributes: true,
            childList: true,
            subtree: true,
            attributeFilter: ['class']
        });
    });
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function hasNumericValue(text) {
    return /-?\d/.test(text);
}

function extractNumericValue(text) {
    const cleaned = text.replace(/,/g, '');
    const match = cleaned.match(/-?\d+(\.\d+)?/);

    if (!match) {
        return null;
    }

    return Number(match[0]);
}

function getDecimalPlaces(text) {
    const match = text.match(/\.(\d+)/);
    return match ? match[1].length : 0;
}

function formatAnimatedValue(value, finalValue, prefix, suffix, decimalPlaces) {
    const isFinalInteger = Number.isInteger(finalValue);
    const displayValue = isFinalInteger
        ? Math.round(value)
        : value.toFixed(decimalPlaces);

    const formattedValue = Number(displayValue).toLocaleString(undefined, {
        minimumFractionDigits: decimalPlaces,
        maximumFractionDigits: decimalPlaces
    });

    return `${prefix}${formattedValue}${suffix}`;
}

function easeOutCubic(value) {
    return 1 - Math.pow(1 - value, 3);
}