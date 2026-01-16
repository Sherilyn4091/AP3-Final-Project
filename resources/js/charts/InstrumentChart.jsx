// resources/js/charts/InstrumentChart.jsx
import { useState, useEffect } from 'react';
import { PieChart, Pie, Cell, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const COLORS = ['#377357', '#C2922F', '#E07A5F', '#61677A', '#272829', '#D8D9DA'];

const InstrumentChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/instrument-popularity', {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(result => {
                setData(Array.isArray(result) ? result : []);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching instrument data:', error);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <div className="h-64 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-warm-coral"></div>
            </div>
        );
    }

    if (!data || data.length === 0) {
        return (
            <div className="h-64 flex items-center justify-center text-gray-500">
                No instrument data available
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={300}>
            <PieChart>
                <Pie
                    data={data}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ genre_name, count }) => `${genre_name}: ${count}`}
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="count"
                >
                    {data.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                </Pie>
                <Tooltip />
                <Legend />
            </PieChart>
        </ResponsiveContainer>
    );
};

export default InstrumentChart;
