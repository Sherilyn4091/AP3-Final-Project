// resources/js/charts/RevenueChart.jsx
import { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const RevenueChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/revenue-weekly')
            .then(response => response.json())
            .then(result => {
                setData(result.data);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching revenue data:', error);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <div className="h-64 flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-golden-yellow"></div>
            </div>
        );
    }

    return (
        <ResponsiveContainer width="100%" height={300}>
            <BarChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#D8D9DA" />
                <XAxis 
                    dataKey="week_label" 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    angle={-45}
                    textAnchor="end"
                    height={80}
                />
                <YAxis 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    tickFormatter={(value) => `₱${value.toLocaleString()}`}
                />
                <Tooltip 
                    formatter={(value) => [`₱${value.toLocaleString()}`, 'Revenue']}
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
                <Bar 
                    dataKey="revenue" 
                    fill="#C2922F" 
                    name="Weekly Revenue"
                    radius={[8, 8, 0, 0]}
                />
            </BarChart>
        </ResponsiveContainer>
    );
};

export default RevenueChart;