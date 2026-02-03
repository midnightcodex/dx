import React from 'react';

export default function StatsCard({
    icon,
    value,
    label,
    trend,
    trendDirection = 'up',
    variant = 'primary',
    animationDelay = 0
}) {
    const formattedValue = typeof value === 'number'
        ? value.toLocaleString('en-US')
        : value;

    return (
        <div
            className="stats-card"
            style={{ animationDelay: `${animationDelay}ms` }}
        >
            <div className="stats-card-header">
                <div className={`stats-card-icon ${variant}`}>
                    {icon}
                </div>
            </div>
            <div className="stats-card-value">{formattedValue}</div>
            <div className="stats-card-label">{label}</div>
            {trend && (
                <div className={`stats-card-trend ${trendDirection}`}>
                    <span>{trendDirection === 'up' ? '↑' : '↓'}</span>
                    <span>{trend}</span>
                </div>
            )}
        </div>
    );
}
