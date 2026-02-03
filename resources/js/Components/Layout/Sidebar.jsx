import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

const navigationItems = [
    {
        section: 'Main',
        items: [
            { name: 'Dashboard', icon: '📊', href: '/' },
        ]
    },
    {
        section: 'Operations',
        items: [
            {
                name: 'Manufacturing', icon: '⚙️', href: '/manufacturing',
                subItems: [
                    { name: 'BOM', href: '/manufacturing/bom' },
                    { name: 'Workstations', href: '/manufacturing/workstations' },
                    { name: 'Work Orders', href: '/manufacturing/work-orders' },
                    { name: 'Production', href: '/manufacturing/production' },
                    { name: 'Quality', href: '/manufacturing/quality' },
                ]
            },
            {
                name: 'Inventory', icon: '📦', href: '/inventory',
                subItems: [
                    { name: 'Item Master', href: '/inventory/items' },
                    { name: 'Stock Ledger', href: '/inventory/stock-ledger' },
                    { name: 'Warehouses', href: '/inventory/warehouses' },
                    { name: 'Batches', href: '/inventory/batches' },
                ]
            },
            {
                name: 'Procurement', icon: '🛒', href: '/procurement',
                subItems: [
                    { name: 'Vendors', href: '/procurement/vendors' },
                    { name: 'Purchase Orders', href: '/procurement/purchase-orders' },
                    { name: 'GRN', href: '/procurement/grn' },
                ]
            },
            {
                name: 'Sales', icon: '💰', href: '/sales',
                subItems: [
                    { name: 'Customers', href: '/sales/customers' },
                    { name: 'Sales Orders', href: '/sales/orders' },
                    { name: 'Delivery', href: '/sales/delivery' },
                ]
            },
        ]
    },
    {
        section: 'Support',
        items: [
            { name: 'Maintenance', icon: '🔧', href: '/maintenance' },
            { name: 'HR', icon: '👥', href: '/hr' },
            { name: 'Compliance', icon: '📋', href: '/compliance' },
        ]
    },
    {
        section: 'Analytics',
        items: [
            { name: 'Reports', icon: '📈', href: '/reports' },
            { name: 'Settings', icon: '⚙️', href: '/settings' },
            { name: 'Help', icon: '❓', href: '/help' },
        ]
    }
];

function NavItem({ item, currentUrl, collapsed }) {
    const [expanded, setExpanded] = useState(
        currentUrl === item.href || (item.subItems && currentUrl.startsWith(item.href + '/'))
    );
    const isActive = currentUrl === item.href;
    const isChildActive = item.subItems && currentUrl.startsWith(item.href + '/');

    const handleToggle = (e) => {
        e.preventDefault();
        e.stopPropagation();
        setExpanded(!expanded);
    };

    if (collapsed) {
        return (
            <Link
                href={item.href}
                className={`sidebar-nav-item ${isActive || isChildActive ? 'active' : ''}`}
                title={item.name}
                style={{ justifyContent: 'center', padding: '12px' }}
            >
                <span className="sidebar-nav-icon" style={{ fontSize: '20px', marginRight: 0 }}>{item.icon}</span>
            </Link>
        );
    }

    return (
        <div>
            <div style={{ display: 'flex', alignItems: 'center' }}>
                <Link
                    href={item.href}
                    className={`sidebar-nav-item ${isActive || isChildActive ? 'active' : ''}`}
                    style={{ flex: 1 }}
                >
                    <span className="sidebar-nav-icon">{item.icon}</span>
                    <span className="sidebar-nav-text">{item.name}</span>
                </Link>
                {item.subItems && (
                    <button
                        onClick={handleToggle}
                        style={{
                            background: 'none',
                            border: 'none',
                            padding: '8px 12px',
                            cursor: 'pointer',
                            fontSize: '10px',
                            opacity: 0.5,
                            color: 'inherit',
                        }}
                    >
                        {expanded ? '▼' : '▶'}
                    </button>
                )}
            </div>
            {item.subItems && expanded && (
                <div style={{ marginLeft: '32px', marginTop: '4px' }} className="stagger-fade-in">
                    {item.subItems.map((subItem, idx) => (
                        <Link
                            key={idx}
                            href={subItem.href}
                            className={`sidebar-nav-item ${currentUrl === subItem.href ? 'active' : ''}`}
                            style={{ padding: '8px 12px', fontSize: '13px' }}
                        >
                            {subItem.name}
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}

export default function Sidebar({ isOpen, onToggle, isMobile }) {
    const { url } = usePage();

    // On mobile, "isOpen" means visible (translated to 0). On desktop, "isOpen" means full width.
    const sidebarClass = isMobile
        ? `sidebar ${isOpen ? 'open' : ''}`
        : `sidebar ${isOpen ? '' : 'collapsed'}`;

    return (
        <aside className={sidebarClass}>
            {/* Logo */}
            <div className="sidebar-logo">
                <div className="sidebar-logo-icon">S</div>
                {(!isMobile && !isOpen) ? null : <span className="sidebar-logo-text">SME ERP</span>}
            </div>

            {/* Navigation */}
            <nav className="sidebar-nav">
                {navigationItems.map((section, sectionIdx) => (
                    <div key={sectionIdx} className="sidebar-section">
                        {(!isMobile && !isOpen) ? (
                            <div style={{ height: '20px', borderBottom: '1px solid var(--color-gray-100)', marginBottom: '10px' }}></div>
                        ) : (
                            <div className="sidebar-section-title">{section.section}</div>
                        )}
                        {section.items.map((item, itemIdx) => (
                            <NavItem
                                key={itemIdx}
                                item={item}
                                currentUrl={url}
                                collapsed={!isMobile && !isOpen}
                            />
                        ))}
                    </div>
                ))}
            </nav>

            {/* Footer */}
            <div className="sidebar-footer">
                <div className="sidebar-team">
                    <div className="sidebar-team-avatar">M</div>
                    {(!isMobile && !isOpen) ? null : (
                        <>
                            <div className="sidebar-team-info">
                                <div className="sidebar-team-name">Manufacturing Team</div>
                            </div>
                            <span style={{ fontSize: '12px', opacity: 0.5 }}>⌃</span>
                        </>
                    )}
                </div>
                {(!isMobile && !isOpen) ? null : (
                    <button className="sidebar-upgrade-btn">
                        Upgrade plan
                    </button>
                )}
            </div>

            {/* Toggle Button for Desktop */}
            {!isMobile && (
                <button
                    onClick={onToggle}
                    style={{
                        position: 'absolute',
                        bottom: '20px',
                        right: '-12px',
                        width: '24px',
                        height: '24px',
                        borderRadius: '50%',
                        backgroundColor: 'var(--color-white)',
                        border: '1px solid var(--color-gray-200)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        cursor: 'pointer',
                        boxShadow: 'var(--shadow-sm)',
                        zIndex: 10
                    }}
                >
                    {isOpen ? '◀' : '▶'}
                </button>
            )}
        </aside>
    );
}
