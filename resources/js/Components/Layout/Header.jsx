import React from 'react';
import Button from '../UI/Button';
import Avatar from '../UI/Avatar';
import ThemeToggle from '../UI/ThemeToggle';

export default function Header({ title, subtitle, onMenuClick }) {
    const today = new Date();
    const options = { month: 'short', day: 'numeric' };
    const dateStart = new Date(today.getFullYear(), today.getMonth(), 1).toLocaleDateString('en-US', options);
    const dateEnd = today.toLocaleDateString('en-US', options);

    return (
        <header className="header">
            <div className="header-left">
                <h1 className="header-title">{title || 'Dashboard'}</h1>
                {subtitle && <p className="header-subtitle">{subtitle}</p>}
            </div>

            <div className="header-right">
                <div className="header-controls">
                    {/* Date Picker */}
                    <div className="header-date-picker">
                        <span>📅</span>
                        <span>{dateStart} — {dateEnd}</span>
                        <span style={{ fontSize: '10px' }}>▼</span>
                    </div>

                    {/* Filter Button */}
                    <Button variant="outline" size="sm">
                        <span>⚙️</span> Filter
                    </Button>

                    {/* Export Button */}
                    <Button variant="outline" size="sm">
                        <span>📤</span> Export
                    </Button>

                    {/* Theme Toggle */}
                    <ThemeToggle />
                </div>

                {/* User Profile */}
                <div className="header-user">
                    <div className="header-user-info">
                        <div className="header-user-name">Admin User</div>
                        <div className="header-user-role">System Admin</div>
                    </div>
                    <Avatar
                        src="https://api.dicebear.com/7.x/avataaars/svg?seed=admin"
                        alt="Admin User"
                        size="md"
                    />
                </div>
            </div>
        </header>
    );
}
