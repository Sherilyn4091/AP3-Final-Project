// resources/js/admin.jsx

import React from 'react';

import { createRoot } from 'react-dom/client';

import EnrollmentChart from './charts/EnrollmentChart';
import RevenueChart from './charts/RevenueChart';
import InstrumentChart from './charts/InstrumentChart';
import InstructorChart from './charts/InstructorChart';

document.addEventListener('DOMContentLoaded', () => {
    const mounts = [
        { id: 'enrollment-trend-chart', Component: EnrollmentChart },
        { id: 'revenue-chart', Component: RevenueChart },
        { id: 'instrument-popularity-chart', Component: InstrumentChart },
        { id: 'instructor-performance-chart', Component: InstructorChart },
    ];

    mounts.forEach(({ id, Component }) => {
        const el = document.getElementById(id);
        if (el) {
            createRoot(el).render(<Component />);
        }
    });
});
