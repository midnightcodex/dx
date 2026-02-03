import React from 'react';
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

export default function RevenueChart({ data }) {
    // Default data if none provided
    const chartData = data || [
        { name: 'Jan', value: 4000 },
        { name: 'Feb', value: 3000 },
        { name: 'Mar', value: 5000 },
        { name: 'Apr', value: 4500 },
        { name: 'May', value: 6000 },
        { name: 'Jun', value: 5500 },
        { name: 'Jul', value: 7000 },
    ];

    return (
        <div className="chart-container" style={{ width: '100%', height: '350px' }}>
            <div className="chart-header">
                <h3 className="chart-title">Revenue Trend</h3>
            </div>
            <ResponsiveContainer width="100%" height="100%">
                <AreaChart
                    data={chartData}
                    margin={{ top: 10, right: 10, left: 0, bottom: 0 }}
                >
                    <defs>
                        <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="5%" stopColor="var(--color-primary)" stopOpacity={0.8} />
                            <stop offset="95%" stopColor="var(--color-primary)" stopOpacity={0} />
                        </linearGradient>
                    </defs>
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
                        tickFormatter={(value) => `₹${value / 1000}k`}
                    />
                    <Tooltip
                        contentStyle={{
                            backgroundColor: 'var(--color-white)',
                            border: '1px solid var(--color-gray-200)',
                            borderRadius: '8px',
                            boxShadow: 'var(--shadow-md)'
                        }}
                        itemStyle={{ color: 'var(--color-gray-900)', fontWeight: 600 }}
                        formatter={(value) => [`₹${value.toLocaleString()}`, 'Revenue']}
                    />
                    <Area
                        type="monotone"
                        dataKey="value"
                        stroke="var(--color-primary)"
                        fillOpacity={1}
                        fill="url(#colorRevenue)"
                        strokeWidth={3}
                        animationDuration={1500}
                    />
                </AreaChart>
            </ResponsiveContainer>
        </div>
    );
}
