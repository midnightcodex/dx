import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const workOrderData = [
    { id: 1, woNumber: 'WO-2026-001', product: 'Steel Brackets A1', bom: 'BOM-001', quantity: 500, completed: 320, status: 'In Progress', priority: 'High', startDate: '2026-02-01', dueDate: '2026-02-05' },
    { id: 2, woNumber: 'WO-2026-002', product: 'Aluminum Plates B2', bom: 'BOM-002', quantity: 1000, completed: 0, status: 'Pending', priority: 'Medium', startDate: '2026-02-03', dueDate: '2026-02-08' },
    { id: 3, woNumber: 'WO-2026-003', product: 'Copper Connectors', bom: 'BOM-003', quantity: 250, completed: 250, status: 'Completed', priority: 'Low', startDate: '2026-01-28', dueDate: '2026-02-01' },
    { id: 4, woNumber: 'WO-2026-004', product: 'Plastic Housings', bom: 'BOM-004', quantity: 750, completed: 450, status: 'In Progress', priority: 'High', startDate: '2026-02-01', dueDate: '2026-02-06' },
];

const columns = [
    { header: 'WO Number', accessor: 'woNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Quantity', accessor: 'quantity', render: (val) => val.toLocaleString() },
    {
        header: 'Progress',
        accessor: 'completed',
        render: (val, row) => {
            const pct = Math.round((val / row.quantity) * 100);
            return (
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <div style={{ width: '60px', height: '6px', backgroundColor: 'var(--color-gray-200)', borderRadius: '3px', overflow: 'hidden' }}>
                        <div style={{ width: `${pct}%`, height: '100%', backgroundColor: pct === 100 ? 'var(--color-success)' : 'var(--color-primary)' }} />
                    </div>
                    <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{pct}%</span>
                </div>
            );
        }
    },
    {
        header: 'Priority',
        accessor: 'priority',
        render: (val) => {
            const colors = { 'Critical': '#DC2626', 'High': '#F59E0B', 'Medium': '#3B82F6', 'Low': '#6B7280' };
            return <span style={{ color: colors[val], fontWeight: 500 }}>● {val}</span>;
        }
    },
    { header: 'Due Date', accessor: 'dueDate' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = {
                'In Progress': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Pending': { bg: '#FEF3C7', color: '#D97706' },
                'Completed': { bg: '#D1FAE5', color: '#059669' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function WorkOrders() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ product: '', bom: '', quantity: '', priority: '', startDate: '', dueDate: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Work Order Created!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ product: '', bom: '', quantity: '', priority: '', startDate: '', dueDate: '' });
    };

    const inProgress = workOrderData.filter(wo => wo.status === 'In Progress');
    const pending = workOrderData.filter(wo => wo.status === 'Pending');
    const completed = workOrderData.filter(wo => wo.status === 'Completed');

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={columns} data={workOrderData} /> },
        { label: 'In Progress', badge: inProgress.length, content: <DataTable columns={columns} data={inProgress} /> },
        { label: 'Pending', badge: pending.length, content: <DataTable columns={columns} data={pending} /> },
        { label: 'Completed', content: <DataTable columns={columns} data={completed} /> },
    ];

    return (
        <MainLayout title="Work Orders" subtitle="Manage production work orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={workOrderData.length} label="Total Work Orders" variant="primary" />
                <StatsCard icon="🔄" value={inProgress.length} label="In Progress" variant="primary" />
                <StatsCard icon="⏳" value={pending.length} label="Pending Start" variant="warning" />
                <StatsCard icon="✅" value={completed.length} label="Completed" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create Work Order
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <Tabs tabs={tabs} />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Work Order" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Work Order</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Product" name="product" value={formData.product} onChange={handleInputChange} required options={[
                        { value: 'steel-brackets', label: 'Steel Brackets A1' },
                        { value: 'aluminum-plates', label: 'Aluminum Plates B2' },
                        { value: 'copper-connectors', label: 'Copper Connectors' },
                    ]} />
                    <Select label="BOM Version" name="bom" value={formData.bom} onChange={handleInputChange} required options={[
                        { value: 'bom-001', label: 'BOM-001 (Rev 3)' },
                        { value: 'bom-002', label: 'BOM-002 (Rev 2)' },
                    ]} />
                    <Input label="Quantity" type="number" name="quantity" value={formData.quantity} onChange={handleInputChange} required placeholder="Enter quantity" />
                    <Select label="Priority" name="priority" value={formData.priority} onChange={handleInputChange} required options={[
                        { value: 'critical', label: '🔴 Critical' },
                        { value: 'high', label: '🟠 High' },
                        { value: 'medium', label: '🔵 Medium' },
                        { value: 'low', label: '⚪ Low' },
                    ]} />
                    <Input label="Start Date" type="date" name="startDate" value={formData.startDate} onChange={handleInputChange} required />
                    <Input label="Due Date" type="date" name="dueDate" value={formData.dueDate} onChange={handleInputChange} required />
                </div>
            </Modal>
        </MainLayout>
    );
}
