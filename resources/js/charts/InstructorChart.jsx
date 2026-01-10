// resources/js/charts/InstructorChart.jsx
import { useState, useEffect } from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const InstructorChart = () => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/admin/charts/instructor-performance')
            .then(response => response.json())
            .then(result => {
                setData(result.data);
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

    return (
        <ResponsiveContainer width="100%" height={300}>
            <BarChart data={data} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#D8D9DA" />
                <XAxis 
                    dataKey="instructor_name" 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    angle={-45}
                    textAnchor="end"
                    height={100}
                />
                <YAxis 
                    tick={{ fill: '#61677A', fontSize: 12 }}
                    allowDecimals={false}
                />
                <Tooltip 
                    formatter={(value) => [value, 'Students Taught']}
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
                    dataKey="total_students" 
                    fill="#E07A5F" 
                    name="Total Students Taught"
                    radius={[8, 8, 0, 0]}
                />
            </BarChart>
        </ResponsiveContainer>
    );
};

export default InstructorChart;