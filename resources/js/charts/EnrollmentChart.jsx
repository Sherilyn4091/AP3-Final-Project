// resources/js/charts/EnrollmentChart.jsx
import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const EnrollmentChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/enrollment-trend')
            .then(response => response.json())
            .then(result => {
                setData(result.data);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching enrollment data:', error);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <div className="h-64 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-forest-green"></div>
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={300}>
            <LineChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#D8D9DA" />
                <XAxis 
                    dataKey="date_label" 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    angle={-45}
                    textAnchor="end"
                    height={80}
                />
                <YAxis 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    allowDecimals={false}
                />
                <Tooltip 
                    formatter={(value) => [value, 'Enrollments']}
                    contentStyle={{ 
                        backgroundColor: '#272829', 
                        border: 'none', 
                        borderRadius: '8px',
                        color: '#FFF6E0'
                    }}
                />
                <Legend 
                    wrapperStyle={{ paddingTop: '20px' }}
                    iconType="circle"
                />
                <Line 
                    type="monotone" 
                    dataKey="count" 
                    stroke="#377357" 
                    strokeWidth={3}
                    name="Daily Enrollments"
                    dot={{ fill: '#377357', r: 4 }}
                    activeDot={{ r: 6 }}
                />
            </LineChart>
        </ResponsiveContainer>
    );
};

export default EnrollmentChart;