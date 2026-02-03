import React from 'react';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';

const COLORS = ['#6366F1', '#10B981', '#F59E0B', '#EF4444'];

export default function StockPieChart({ data }) {
    const chartData = data || [
        { name: 'Raw Materials', value: 452500 },
        { name: 'Finished Goods', value: 117000 },
        { name: 'Packaging', value: 30000 },
        { name: 'Consumables', value: 15000 },
    ];

    // Calculate percentages
    const total = chartData.reduce((sum, item) => sum + item.value, 0);

    return (
        <div className="chart-container" style={{ width: '100%', height: '350px' }}>
            <div className="chart-header">
                <h3 className="chart-title">Stock Valuation by Category</h3>
            </div>
            <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                    <Pie
                        data={chartData}
                        cx="50%"
                        cy="50%"
                        innerRadius={60}
                        outerRadius={100}
                        fill="#8884d8"
                        paddingAngle={5}
                        dataKey="value"
                        animationDuration={1000}
                        animationBegin={200}
                    >
                        {chartData.map((entry, index) => (
                            <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                        ))}
                    </Pie>
                    <Tooltip
                        contentStyle={{
                            backgroundColor: 'var(--color-white)',
                            border: '1px solid var(--color-gray-200)',
                            borderRadius: '8px',
                            boxShadow: 'var(--shadow-md)'
                        }}
                        formatter={(value) => [`₹${value.toLocaleString()}`, 'Value']}
                    />
                    <Legend
                        verticalAlign="bottom"
                        height={36}
                        formatter={(value, entry, index) => {
                            const percent = ((chartData[index].value / total) * 100).toFixed(0);
                            return <span style={{ color: 'var(--color-gray-600)', fontWeight: 500, marginLeft: '8px' }}>{value} ({percent}%)</span>;
                        }}
                    />
                </PieChart>
            </ResponsiveContainer>
        </div>
    );
}
