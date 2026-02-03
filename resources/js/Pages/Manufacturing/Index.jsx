import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import ProductionBarChart from '../../Components/Charts/ProductionBarChart';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const workOrderData = [
    { id: 1, woNumber: 'WO-2024-001', product: 'Gear Assembly A', quantity: 500, status: 'In Progress', priority: 'High', dueDate: '2026-02-15', progress: 65 },
    { id: 2, woNumber: 'WO-2024-002', product: 'Shaft Component B', quantity: 1000, status: 'Pending', priority: 'Normal', dueDate: '2026-02-20', progress: 0 },
    { id: 3, woNumber: 'WO-2024-003', product: 'Motor Housing C', quantity: 250, status: 'Completed', priority: 'Low', dueDate: '2026-02-10', progress: 100 },
];

const productionSchedule = [
    { time: '06:00 - 14:00', machine: 'CNC-01', product: 'Gear Assembly A', operator: 'Ramesh K.' },
    { time: '14:00 - 22:00', machine: 'CNC-02', product: 'Shaft Component B', operator: 'Vikram S.' },
];

const qualityAlerts = [
    { id: 1, type: 'NCR', description: 'Surface finish out of spec', severity: 'High', date: '2026-02-01' },
    { id: 2, type: 'Hold', description: 'Material certification pending', severity: 'Medium', date: '2026-02-02' },
];

const columns = [
    { header: 'WO #', accessor: 'woNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Qty', accessor: 'quantity' },
    {
        header: 'Progress',
        accessor: 'progress',
        render: (val) => (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <div style={{ width: '60px', height: '6px', backgroundColor: 'var(--color-gray-200)', borderRadius: '3px' }}>
                    <div style={{ width: `${val}%`, height: '100%', backgroundColor: val === 100 ? 'var(--color-success)' : 'var(--color-primary)', borderRadius: '3px' }} />
                </div>
                <span style={{ fontSize: '12px' }}>{val}%</span>
            </div>
        )
    },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = { 'In Progress': '#3B82F6', 'Pending': '#F59E0B', 'Completed': '#10B981' };
            return <span style={{ color: colors[val], fontWeight: 500 }}>● {val}</span>;
        }
    },
    { header: 'Due Date', accessor: 'dueDate' },
];

export default function ManufacturingIndex() {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [formData, setFormData] = useState({
        product: '',
        bom: '',
        quantity: '',
        priority: '',
        dueDate: '',
        notes: '',
    });

    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        // In real app, this would call API
        alert('Work Order Created: ' + JSON.stringify(formData, null, 2));
        setShowCreateModal(false);
        setFormData({ product: '', bom: '', quantity: '', priority: '', dueDate: '', notes: '' });
    };

    const tabs = [
        {
            label: 'Work Orders',
            badge: workOrderData.length,
            content: <DataTable columns={columns} data={workOrderData} title="Active Work Orders" />
        },
        { label: 'Schedule', content: <ScheduleView data={productionSchedule} /> },
        { label: 'Alerts', badge: qualityAlerts.length, content: <AlertsView data={qualityAlerts} /> },
    ];

    return (
        <MainLayout title="Manufacturing" subtitle="Production overview and work orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={12} label="Active Work Orders" variant="primary" />
                <StatsCard icon="✅" value={8} label="Completed Today" trend={5} trendLabel="vs yesterday" variant="success" />
                <StatsCard icon="⚠️" value={2} label="Quality Alerts" variant="warning" />
                <StatsCard icon="🏭" value="87%" label="Machine Utilization" variant="primary" />
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button
                    onClick={() => setShowCreateModal(true)}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px',
                        padding: '12px 20px',
                        borderRadius: '8px',
                        border: 'none',
                        backgroundColor: 'var(--color-primary)',
                        color: 'white',
                        fontWeight: 500,
                        cursor: 'pointer',
                        fontSize: '14px',
                    }}
                >
                    ➕ Create Work Order
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📊 View BOM
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔧 Production Plan
                </button>
            </div>

            <div className="dashboard-grid">
                <ProductionBarChart />
                <QuickLinks onCreateWO={() => setShowCreateModal(true)} />
            </div>

            <Tabs tabs={tabs} />

            {/* Create Work Order Modal */}
            <Modal
                isOpen={showCreateModal}
                onClose={() => setShowCreateModal(false)}
                title="Create Work Order"
                size="lg"
                footer={
                    <>
                        <button
                            onClick={() => setShowCreateModal(false)}
                            style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}
                        >
                            Cancel
                        </button>
                        <button
                            onClick={handleSubmit}
                            style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}
                        >
                            Create Work Order
                        </button>
                    </>
                }
            >
                <form onSubmit={handleSubmit}>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                        <Select
                            label="Product"
                            name="product"
                            value={formData.product}
                            onChange={handleInputChange}
                            required
                            options={[
                                { value: 'gear-assembly-a', label: 'Gear Assembly A' },
                                { value: 'shaft-component-b', label: 'Shaft Component B' },
                                { value: 'motor-housing-c', label: 'Motor Housing C' },
                            ]}
                        />
                        <Select
                            label="BOM Version"
                            name="bom"
                            value={formData.bom}
                            onChange={handleInputChange}
                            required
                            options={[
                                { value: 'bom-v1', label: 'BOM v1.0' },
                                { value: 'bom-v2', label: 'BOM v2.0 (Latest)' },
                            ]}
                        />
                        <Input
                            label="Quantity"
                            type="number"
                            name="quantity"
                            value={formData.quantity}
                            onChange={handleInputChange}
                            required
                            placeholder="Enter quantity"
                        />
                        <Select
                            label="Priority"
                            name="priority"
                            value={formData.priority}
                            onChange={handleInputChange}
                            required
                            options={[
                                { value: 'high', label: '🔴 High' },
                                { value: 'normal', label: '🟡 Normal' },
                                { value: 'low', label: '🟢 Low' },
                            ]}
                        />
                        <Input
                            label="Due Date"
                            type="date"
                            name="dueDate"
                            value={formData.dueDate}
                            onChange={handleInputChange}
                            required
                        />
                        <div></div>
                    </div>
                    <div style={{ marginTop: '16px' }}>
                        <label style={{ display: 'block', fontSize: '14px', fontWeight: 500, marginBottom: '8px' }}>Notes</label>
                        <textarea
                            name="notes"
                            value={formData.notes}
                            onChange={handleInputChange}
                            rows={3}
                            placeholder="Additional instructions..."
                            style={{ width: '100%', padding: '10px 14px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px', resize: 'vertical' }}
                        />
                    </div>
                </form>
            </Modal>
        </MainLayout>
    );
}

function QuickLinks({ onCreateWO }) {
    const links = [
        { icon: '➕', label: 'New Work Order', onClick: onCreateWO },
        { icon: '📋', label: 'View BOM List', href: '/manufacturing/bom' },
        { icon: '🏭', label: 'Workstations', href: '/manufacturing/workstations' },
        { icon: '🔧', label: 'Production Plan', href: '/manufacturing/production' },
        { icon: '✅', label: 'Quality Control', href: '/manufacturing/quality' },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Quick Actions</h3></div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', padding: '16px' }}>
                {links.map((link, idx) => (
                    <button
                        key={idx}
                        onClick={() => link.onClick ? link.onClick() : router.visit(link.href)}
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '12px',
                            padding: '16px',
                            borderRadius: '12px',
                            border: '1px solid var(--color-gray-200)',
                            backgroundColor: 'var(--color-white)',
                            cursor: 'pointer',
                            textAlign: 'left',
                            transition: 'all 0.15s ease',
                        }}
                    >
                        <span style={{ fontSize: '24px' }}>{link.icon}</span>
                        <span style={{ fontWeight: 500, fontSize: '14px' }}>{link.label}</span>
                    </button>
                ))}
            </div>
        </div>
    );
}

function ScheduleView({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            {data.map((item, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', border: '1px solid var(--color-gray-200)', borderRadius: '12px', marginBottom: '12px' }}>
                    <div style={{ width: '120px', fontWeight: 600, color: 'var(--color-primary)' }}>{item.time}</div>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 500 }}>{item.product}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{item.machine} • {item.operator}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}

function AlertsView({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            {data.map((alert, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', border: `1px solid ${alert.severity === 'High' ? 'var(--color-danger)' : 'var(--color-warning)'}`, borderRadius: '12px', marginBottom: '12px', backgroundColor: alert.severity === 'High' ? 'var(--color-danger-light)' : 'var(--color-warning-light)' }}>
                    <span style={{ fontSize: '24px', marginRight: '16px' }}>{alert.severity === 'High' ? '🚨' : '⚠️'}</span>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 600 }}>{alert.type}: {alert.description}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{alert.date}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}
