import React from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import { Link } from '@inertiajs/react';

export default function HelpIndex() {
    return (
        <MainLayout title="User Manual" subtitle="System guide and workflows">
            <div style={{ display: 'grid', gridTemplateColumns: '300px 1fr', gap: '24px' }}>
                {/* Table of Contents */}
                <TableOfContents />

                {/* Content */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
                    <SystemOverview />
                    <NavigationGuide />
                    <ModuleWorkflows />
                    <QuickReference />
                </div>
            </div>
        </MainLayout>
    );
}

function TableOfContents() {
    const sections = [
        { id: 'overview', label: '📊 System Overview' },
        { id: 'navigation', label: '🧭 Navigation Guide' },
        { id: 'workflows', label: '⚙️ Module Workflows' },
        { id: 'reference', label: '📋 Quick Reference' },
    ];

    return (
        <div className="chart-container" style={{ position: 'sticky', top: '24px', height: 'fit-content' }}>
            <div className="chart-header"><h3 className="chart-title">Contents</h3></div>
            <nav style={{ padding: '8px 16px' }}>
                {sections.map((section, idx) => (
                    <a
                        key={idx}
                        href={`#${section.id}`}
                        style={{
                            display: 'block',
                            padding: '12px',
                            marginBottom: '4px',
                            borderRadius: '8px',
                            textDecoration: 'none',
                            color: 'var(--color-gray-700)',
                            fontSize: '14px',
                            transition: 'all 0.15s ease',
                        }}
                        onMouseEnter={(e) => e.target.style.backgroundColor = 'var(--color-gray-100)'}
                        onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
                    >
                        {section.label}
                    </a>
                ))}
            </nav>
        </div>
    );
}

function SystemOverview() {
    return (
        <div className="chart-container" id="overview">
            <div className="chart-header"><h3 className="chart-title">📊 System Overview</h3></div>
            <div style={{ padding: '16px' }}>
                <p style={{ marginBottom: '16px', lineHeight: 1.6, color: 'var(--color-gray-600)' }}>
                    SME ERP is a comprehensive manufacturing resource planning system designed for small and medium enterprises.
                    It covers the complete operational lifecycle from procurement to production to sales.
                </p>

                <h4 style={{ marginBottom: '12px', fontSize: '16px' }}>Core Modules</h4>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '12px' }}>
                    {[
                        { icon: '⚙️', name: 'Manufacturing', desc: 'BOM, Work Orders, Production Planning, Quality Control' },
                        { icon: '📦', name: 'Inventory', desc: 'Stock Ledger, Warehouses, Batch Tracking' },
                        { icon: '🛒', name: 'Procurement', desc: 'Vendors, Purchase Orders, Goods Receipt' },
                        { icon: '💰', name: 'Sales', desc: 'Customers, Sales Orders, Delivery Notes' },
                        { icon: '🔧', name: 'Maintenance', desc: 'Equipment, Preventive Maintenance' },
                        { icon: '👥', name: 'HR', desc: 'Employees, Shifts, Attendance' },
                    ].map((mod, idx) => (
                        <div key={idx} style={{ display: 'flex', gap: '12px', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                            <span style={{ fontSize: '24px' }}>{mod.icon}</span>
                            <div>
                                <div style={{ fontWeight: 600, marginBottom: '4px' }}>{mod.name}</div>
                                <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{mod.desc}</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

function NavigationGuide() {
    return (
        <div className="chart-container" id="navigation">
            <div className="chart-header"><h3 className="chart-title">🧭 Navigation Guide</h3></div>
            <div style={{ padding: '16px' }}>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <div style={{ padding: '16px', backgroundColor: 'var(--color-gray-50)', borderRadius: '12px' }}>
                        <h4 style={{ marginBottom: '8px' }}>📍 Sidebar Navigation</h4>
                        <p style={{ fontSize: '14px', color: 'var(--color-gray-600)', marginBottom: '12px' }}>
                            The left sidebar contains all modules organized by category:
                        </p>
                        <ul style={{ fontSize: '14px', color: 'var(--color-gray-600)', paddingLeft: '20px' }}>
                            <li><strong>Main</strong> - Dashboard overview</li>
                            <li><strong>Operations</strong> - Manufacturing, Inventory, Procurement, Sales</li>
                            <li><strong>Support</strong> - Maintenance, HR, Compliance</li>
                            <li><strong>Analytics</strong> - Reports, Settings</li>
                        </ul>
                    </div>

                    <div style={{ padding: '16px', backgroundColor: 'var(--color-primary-light)', borderRadius: '12px' }}>
                        <h4 style={{ marginBottom: '8px' }}>💡 Expanding Menus</h4>
                        <p style={{ fontSize: '14px', color: 'var(--color-gray-700)' }}>
                            Click on a module with an arrow (▶) to expand its sub-pages. For example, clicking "Manufacturing" reveals BOM, Work Orders, Production, and Quality sub-pages.
                        </p>
                    </div>

                    <div style={{ padding: '16px', backgroundColor: 'var(--color-success-light)', borderRadius: '12px' }}>
                        <h4 style={{ marginBottom: '8px' }}>✨ Quick Actions</h4>
                        <p style={{ fontSize: '14px', color: 'var(--color-gray-700)' }}>
                            Each module page has Quick Action buttons at the top for common tasks like creating new records. Look for the "➕ Create" buttons.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

function ModuleWorkflows() {
    const workflows = [
        {
            module: 'Manufacturing Workflow',
            steps: [
                { step: 1, title: 'Create BOM', desc: 'Define bill of materials for your product' },
                { step: 2, title: 'Create Work Order', desc: 'Schedule production with quantity and due date' },
                { step: 3, title: 'Track Production', desc: 'Monitor progress on Production page' },
                { step: 4, title: 'Quality Check', desc: 'Log inspections and NCRs' },
            ]
        },
        {
            module: 'Procurement Workflow',
            steps: [
                { step: 1, title: 'Add Vendor', desc: 'Register supplier in Vendors page' },
                { step: 2, title: 'Create PO', desc: 'Generate purchase order with items' },
                { step: 3, title: 'Receive Goods', desc: 'Create GRN when materials arrive' },
                { step: 4, title: 'Update Stock', desc: 'Inventory automatically updated' },
            ]
        },
        {
            module: 'Sales Workflow',
            steps: [
                { step: 1, title: 'Add Customer', desc: 'Register customer with credit terms' },
                { step: 2, title: 'Create Order', desc: 'Enter sales order with products' },
                { step: 3, title: 'Dispatch', desc: 'Create delivery note for shipment' },
                { step: 4, title: 'Invoice', desc: 'Generate invoice and track payment' },
            ]
        },
    ];

    return (
        <div className="chart-container" id="workflows">
            <div className="chart-header"><h3 className="chart-title">⚙️ Module Workflows</h3></div>
            <div style={{ padding: '16px' }}>
                {workflows.map((wf, wfIdx) => (
                    <div key={wfIdx} style={{ marginBottom: '24px' }}>
                        <h4 style={{ marginBottom: '16px', color: 'var(--color-primary)' }}>{wf.module}</h4>
                        <div style={{ display: 'flex', gap: '8px' }}>
                            {wf.steps.map((step, idx) => (
                                <div key={idx} style={{ flex: 1, position: 'relative' }}>
                                    <div style={{ display: 'flex', alignItems: 'center', marginBottom: '12px' }}>
                                        <div style={{
                                            width: '32px',
                                            height: '32px',
                                            borderRadius: '50%',
                                            backgroundColor: 'var(--color-primary)',
                                            color: 'white',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            fontWeight: 600,
                                            fontSize: '14px',
                                        }}>
                                            {step.step}
                                        </div>
                                        {idx < wf.steps.length - 1 && (
                                            <div style={{ flex: 1, height: '2px', backgroundColor: 'var(--color-gray-200)', marginLeft: '8px' }} />
                                        )}
                                    </div>
                                    <div style={{ fontWeight: 600, fontSize: '14px', marginBottom: '4px' }}>{step.title}</div>
                                    <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{step.desc}</div>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function QuickReference() {
    const shortcuts = [
        { action: 'Create Work Order', path: '/manufacturing', button: '➕ Create Work Order' },
        { action: 'Add New Vendor', path: '/procurement/vendors', button: '➕ Add Vendor' },
        { action: 'Create Purchase Order', path: '/procurement/purchase-orders', button: '➕ New PO' },
        { action: 'Create Sales Order', path: '/sales/orders', button: '➕ New Order' },
        { action: 'Add Employee', path: '/hr', button: '➕ Add Employee' },
        { action: 'Toggle Dark Mode', path: 'Header', button: '🌙 Theme Toggle' },
    ];

    return (
        <div className="chart-container" id="reference">
            <div className="chart-header"><h3 className="chart-title">📋 Quick Reference</h3></div>
            <div style={{ padding: '16px' }}>
                <h4 style={{ marginBottom: '12px' }}>Common Actions</h4>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                            <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Action</th>
                            <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Location</th>
                            <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Button</th>
                        </tr>
                    </thead>
                    <tbody>
                        {shortcuts.map((s, idx) => (
                            <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                                <td style={{ padding: '12px', fontWeight: 500 }}>{s.action}</td>
                                <td style={{ padding: '12px', color: 'var(--color-primary)' }}>{s.path}</td>
                                <td style={{ padding: '12px' }}><code style={{ padding: '4px 8px', backgroundColor: 'var(--color-gray-100)', borderRadius: '4px', fontSize: '12px' }}>{s.button}</code></td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
