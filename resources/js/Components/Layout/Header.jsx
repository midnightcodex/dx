import React, { useState } from 'react';
import { usePage, Link } from '@inertiajs/react'; // Import usePage and Link
import Button from '../UI/Button';
import Avatar from '../UI/Avatar';
import ThemeToggle from '../UI/ThemeToggle';

export default function Header({ title, subtitle, onMenuClick }) {
    const { auth } = usePage().props; // Get auth from props
    const user = auth?.user;
    const [userMenuOpen, setUserMenuOpen] = useState(false);

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
                <div
                    className="header-user"
                    onClick={() => setUserMenuOpen(!userMenuOpen)}
                    style={{ cursor: 'pointer', position: 'relative' }}
                >
                    <div className="header-user-info">
                        <div className="header-user-name">{user?.name || 'Guest'}</div>
                        <div className="header-user-role">{user?.roles?.[0]?.name || 'Viewer'}</div>
                    </div>
                    <Avatar
                        src={user?.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(user?.name || 'Guest')}&background=random`}
                        alt={user?.name || 'User'}
                        size="md"
                    />

                    {/* User Dropdown */}
                    {userMenuOpen && (
                        <div className="user-dropdown-menu" style={{
                            position: 'absolute',
                            top: '100%',
                            right: 0,
                            marginTop: '10px',
                            background: 'white',
                            border: '1px solid #e2e8f0',
                            borderRadius: '6px',
                            boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
                            width: '180px',
                            zIndex: 50,
                            padding: '4px 0'
                        }}>
                            <div style={{
                                padding: '8px 16px',
                                borderBottom: '1px solid #f1f5f9',
                                fontSize: '12px',
                                fontWeight: '600',
                                color: '#64748b'
                            }}>
                                {user?.email}
                            </div>
                            <Link
                                href="/profile"
                                className="dropdown-item"
                                style={{ display: 'block', padding: '8px 16px', color: '#334155', textDecoration: 'none', fontSize: '14px' }}
                            >
                                Profile
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="dropdown-item"
                                style={{
                                    display: 'block',
                                    width: '100%',
                                    textAlign: 'left',
                                    padding: '8px 16px',
                                    color: '#ef4444',
                                    textDecoration: 'none',
                                    fontSize: '14px',
                                    background: 'none',
                                    border: 'none',
                                    cursor: 'pointer'
                                }}
                            >
                                Sign out
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}
