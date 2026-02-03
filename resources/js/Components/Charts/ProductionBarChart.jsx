import React from 'react';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export default function ProductionBarChart({ data }) {
    const chartData = data || [
        { name: 'Mon', target: 500, produced: 480 },
        { name: 'Tue', target: 500, produced: 510 },
        { name: 'Wed', target: 500, produced: 450 },
        { name: 'Thu', target: 500, produced: 490 },
        { name: 'Fri', target: 550, produced: 530 },
        { name: 'Sat', target: 400, produced: 410 },
    ];

    return (
        <div className="chart-container" style={{ width: '100%', height: '350px' }}>
            <div className="chart-header">
                <h3 className="chart-title">Production: Target vs Actual</h3>
            </div>
            <ResponsiveContainer width="100%" height="100%">
                <BarChart
                    data={chartData}
                    margin={{ top: 20, right: 30, left: 0, bottom: 5 }}
                    barGap={4}
                >
                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--color-gray-200)" />
                    <XAxis
                        dataKey="name"
                        axisLine={false}
                        tickLine={false}
                        tick={{ fill: 'var(--color-gray-500)', fontSize: 12 }}
                        dy={10}
                    />
                    <YAxis
                        axisLine={false}
                        tickLine={false}
                        tick={{ fill: 'var(--color-gray-500)', fontSize: 12 }}
                    />
                    <Tooltip
                        cursor={{ fill: 'var(--color-gray-50)' }}
                        contentStyle={{
                            backgroundColor: 'var(--color-white)',
                            border: '1px solid var(--color-gray-200)',
                            borderRadius: '8px',
                            boxShadow: 'var(--shadow-md)'
                        }}
                    />
                    <Legend
                        wrapperStyle={{ paddingTop: '20px' }}
                        formatter={(value) => <span style={{ color: 'var(--color-gray-600)', fontWeight: 500 }}>{value}</span>}
                    />
                    <Bar
                        dataKey="target"
                        fill="var(--color-gray-300)"
                        name="Target Output"
                        radius={[4, 4, 0, 0]}
                        animationDuration={1500}
                    />
                    <Bar
                        dataKey="produced"
                        fill="var(--color-success)"
                        name="Actual Output"
                        radius={[4, 4, 0, 0]}
                        animationDuration={1500}
                    />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}
