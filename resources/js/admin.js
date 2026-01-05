// resources/js/admin.js
// Mount point for all *****React chart***** components on the Admin Dashboard

import React from 'react';
import { createRoot } from 'react-dom/client';
import EnrollmentChart from './charts/EnrollmentChart.jsx';
import RevenueChart from './charts/RevenueChart.jsx';
import InstrumentChart from './charts/InstrumentChart.jsx';
import InstructorChart from './charts/InstructorChart.jsx';

document.addEventListener('DOMContentLoaded', () => {
    const containers = [
        { id: 'enrollment-trend-chart', component: EnrollmentChart },
        { id: 'revenue-chart', component: RevenueChart },
        { id: 'instrument-popularity-chart', component: InstrumentChart },
        { id: 'instructor-performance-chart', component: InstructorChart },
    ];

    containers.forEach(({ id, component: Component }) => {
        const container = document.getElementById(id);
        if (container) {
            const root = createRoot(container);
            root.render(<Component />);
        }
    });
});