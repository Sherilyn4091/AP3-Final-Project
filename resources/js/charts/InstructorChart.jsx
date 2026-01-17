// resources/js/charts/InstructorChart.jsx

import React from 'react';
import { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const InstructorChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/instructor-performance', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json())
            .then(result => {
                setData(Array.isArray(result) ? result : []);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching instructor data:', error);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <div className="h-64 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-secondary-blue"></div>
            </div>
        );
    }

    if (!data || data.length === 0) {
        return (
            <div className="h-64 flex items-center justify-center text-gray-500">
                No instructor data available
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={300}>
            <BarChart data={data}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="instructor_name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="total_students" fill="#3B82F6" name="Students Taught" />
            </BarChart>
        </ResponsiveContainer>
    );
};

export default InstructorChart;
