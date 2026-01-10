// resources/js/charts/InstrumentChart.jsx
import { useState, useEffect } from 'react';
import { PieChart, Pie, Cell, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const COLORS = ['#377357', '#C2922F', '#E07A5F', '#61677A', '#272829', '#D8D9DA'];

const InstrumentChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/instrument-popularity')
            .then(response => response.json())
            .then(result => {
                setData(result.data);
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

    const renderCustomLabel = ({ cx, cy, midAngle, innerRadius, outerRadius, percent }) => {
        const radius = innerRadius + (outerRadius - innerRadius) * 0.5;
        const x = cx + radius * Math.cos(-midAngle * Math.PI / 180);
        const y = cy + radius * Math.sin(-midAngle * Math.PI / 180);

        return (
            <text 
                x={x} 
                y={y} 
                fill="white" 
                textAnchor={x > cx ? 'start' : 'end'} 
                dominantBaseline="central"
                fontSize="14"
                fontWeight="bold"
            >
                {`${(percent * 100).toFixed(0)}%`}
            </text>
        );
    };

    return (
        <ResponsiveContainer width="100%" height={300}>
            <PieChart>
                <Pie
                    data={data}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={renderCustomLabel}
                    outerRadius={100}
                    fill="#8884d8"
                    dataKey="count"
                >
                    {data.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                </Pie>
                <Tooltip 
                    formatter={(value, name, props) => [value, props.payload.name]}
                    contentStyle={{ 
                        backgroundColor: '#272829', 
                        border: 'none', 
                        borderRadius: '8px',
                        color: '#FFF6E0'
                    }}
                />
                <Legend 
                    verticalAlign="bottom" 
                    height={36}
                    iconType="circle"
                    formatter={(value, entry) => `${entry.payload.name} (${entry.payload.count})`}
                />
            </PieChart>
        </ResponsiveContainer>
    );
};

export default InstrumentChart;