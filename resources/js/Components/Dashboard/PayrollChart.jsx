import React from 'react';

const monthlyData = [
    { month: 'Jan', salary: 45, tax: 25, loan: 15 },
    { month: 'Feb', salary: 50, tax: 30, loan: 10 },
    { month: 'Mar', salary: 40, tax: 20, loan: 18 },
    { month: 'Apr', salary: 55, tax: 35, loan: 12 },
    { month: 'May', salary: 60, tax: 28, loan: 8 },
    { month: 'Jun', salary: 48, tax: 32, loan: 20 },
    { month: 'Jul', salary: 52, tax: 26, loan: 14 },
    { month: 'Aug', salary: 58, tax: 38, loan: 16 },
    { month: 'Sep', salary: 65, tax: 30, loan: 10 },
    { month: 'Oct', salary: 70, tax: 42, loan: 12 },
    { month: 'Nov', salary: 62, tax: 35, loan: 18 },
    { month: 'Dec', salary: 75, tax: 45, loan: 15 },
];

export default function PayrollChart({ title = 'Annual Summary' }) {
    const maxValue = Math.max(
        ...monthlyData.map(d => Math.max(d.salary, d.tax, d.loan))
    );

    return (
        <div className="chart-container">
            <div className="chart-header">
                <h3 className="chart-title">{title}</h3>
                <div className="chart-legend">
                    <div className="chart-legend-item">
                        <div className="chart-legend-dot" style={{ backgroundColor: '#6366F1' }}></div>
                        <span>Production</span>
                    </div>
                    <div className="chart-legend-item">
                        <div className="chart-legend-dot" style={{ backgroundColor: '#F59E0B' }}></div>
                        <span>Quality</span>
                    </div>
                    <div className="chart-legend-item">
                        <div className="chart-legend-dot" style={{ backgroundColor: '#EC4899' }}></div>
                        <span>Maintenance</span>
                    </div>
                </div>
            </div>

            <div className="bar-chart">
                {monthlyData.map((data, index) => (
                    <div key={data.month} className="bar-chart-group">
                        <div className="bar-chart-bars">
                            <div
                                className="bar-chart-bar salary"
                                style={{
                                    height: `${(data.salary / maxValue) * 160}px`,
                                    animationDelay: `${index * 50}ms`
                                }}
                            />
                            <div
                                className="bar-chart-bar tax"
                                style={{
                                    height: `${(data.tax / maxValue) * 160}px`,
                                    animationDelay: `${index * 50 + 100}ms`
                                }}
                            />
                            <div
                                className="bar-chart-bar loan"
                                style={{
                                    height: `${(data.loan / maxValue) * 160}px`,
                                    animationDelay: `${index * 50 + 200}ms`
                                }}
                            />
                        </div>
                        <span className="bar-chart-label">{data.month}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}
