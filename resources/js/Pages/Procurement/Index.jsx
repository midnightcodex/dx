import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const poData = [
    { id: 1, poNumber: 'PO-2026-042', vendor: 'Steel Corp Ltd', items: 3, totalAmount: 250000, status: 'Pending Approval', orderDate: '2026-02-02', expectedDate: '2026-02-10' },
    { id: 2, poNumber: 'PO-2026-041', vendor: 'Aluminum Traders', items: 2, totalAmount: 175000, status: 'Approved', orderDate: '2026-02-01', expectedDate: '2026-02-08' },
    { id: 3, poNumber: 'PO-2026-040', vendor: 'Copper Wire Inc', items: 1, totalAmount: 45000, status: 'Partially Received', orderDate: '2026-01-28', expectedDate: '2026-02-05' },
    { id: 4, poNumber: 'PO-2026-039', vendor: 'Packaging Solutions', items: 5, totalAmount: 32000, status: 'Completed', orderDate: '2026-01-25', expectedDate: '2026-02-01' },
];

const poColumns = [
    { header: 'PO Number', accessor: 'poNumber' },
    { header: 'Vendor', accessor: 'vendor' },
    { header: 'Items', accessor: 'items' },
    { header: 'Amount', accessor: 'totalAmount', render: (val) => `₹${val.toLocaleString()}` },
    { header: 'Order Date', accessor: 'orderDate' },
    { header: 'Expected', accessor: 'expectedDate' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = {
                'Pending Approval': { bg: '#FEF3C7', color: '#D97706' },
                'Approved': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Partially Received': { bg: '#EDE9FE', color: '#7C3AED' },
                'Completed': { bg: '#D1FAE5', color: '#059669' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function ProcurementIndex() {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [formData, setFormData] = useState({
        vendor: '',
        expectedDate: '',
        paymentTerms: '',
        notes: '',
    });
    const [lineItems, setLineItems] = useState([{ item: '', quantity: '', unitPrice: '' }]);

    const handleInputChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleLineItemChange = (idx, field, value) => {
        const updated = [...lineItems];
        updated[idx][field] = value;
        setLineItems(updated);
    };

    const addLineItem = () => {
        setLineItems([...lineItems, { item: '', quantity: '', unitPrice: '' }]);
    };

    const removeLineItem = (idx) => {
        if (lineItems.length > 1) {
            setLineItems(lineItems.filter((_, i) => i !== idx));
        }
    };

    const handleSubmit = () => {
        alert('Purchase Order Created!\n' + JSON.stringify({ ...formData, lineItems }, null, 2));
        setShowCreateModal(false);
        setFormData({ vendor: '', expectedDate: '', paymentTerms: '', notes: '' });
        setLineItems([{ item: '', quantity: '', unitPrice: '' }]);
    };

    const totalValue = poData.reduce((sum, po) => sum + po.totalAmount, 0);
    const pending = poData.filter(po => po.status === 'Pending Approval');

    const tabs = [
        { label: 'Recent POs', content: <DataTable columns={poColumns} data={poData} title="Purchase Orders" /> },
        { label: 'Pending Approval', badge: pending.length, content: <DataTable columns={poColumns} data={pending} title="Pending Approval" /> },
        { label: 'Expected Deliveries', content: <ExpectedDeliveries /> },
    ];

    return (
        <MainLayout title="Procurement" subtitle="Purchase orders and vendor management">
            <div className="stats-grid">
                <StatsCard icon="📋" value={poData.length} label="Active POs" trend="This month" variant="primary" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Total Order Value" variant="success" />
                <StatsCard icon="⏳" value={pending.length} label="Pending Approval" variant="warning" />
                <StatsCard icon="👥" value={12} label="Active Vendors" variant="primary" />
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
                    ➕ Create Purchase Order
                </button>
                <button
                    onClick={() => router.visit('/procurement/vendors')}
                    style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}
                >
                    👥 Add Vendor
                </button>
                <button
                    onClick={() => router.visit('/procurement/grn')}
                    style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}
                >
                    📥 Create GRN
                </button>
            </div>

            <div className="dashboard-grid">
                <QuickActions onCreatePO={() => setShowCreateModal(true)} />
                <SpendAnalysis />
            </div>

            <Tabs tabs={tabs} />

            {/* Create PO Modal */}
            <Modal
                isOpen={showCreateModal}
                onClose={() => setShowCreateModal(false)}
                title="Create Purchase Order"
                size="xl"
                footer={
                    <>
                        <button onClick={() => setShowCreateModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                        <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create PO</button>
                    </>
                }
            >
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Select
                        label="Vendor"
                        name="vendor"
                        value={formData.vendor}
                        onChange={handleInputChange}
                        required
                        options={[
                            { value: 'steel-corp', label: 'Steel Corp Ltd' },
                            { value: 'aluminum-traders', label: 'Aluminum Traders' },
                            { value: 'copper-wire', label: 'Copper Wire Inc' },
                        ]}
                    />
                    <Input
                        label="Expected Delivery Date"
                        type="date"
                        name="expectedDate"
                        value={formData.expectedDate}
                        onChange={handleInputChange}
                        required
                    />
                    <Select
                        label="Payment Terms"
                        name="paymentTerms"
                        value={formData.paymentTerms}
                        onChange={handleInputChange}
                        options={[
                            { value: 'net-30', label: 'Net 30 Days' },
                            { value: 'net-60', label: 'Net 60 Days' },
                            { value: 'cod', label: 'Cash on Delivery' },
                        ]}
                    />
                </div>

                <div style={{ marginBottom: '16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h4 style={{ margin: 0 }}>Line Items</h4>
                        <button onClick={addLineItem} style={{ padding: '6px 12px', borderRadius: '6px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '12px', cursor: 'pointer' }}>+ Add Item</button>
                    </div>
                    {lineItems.map((li, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Input label={idx === 0 ? "Item" : ""} placeholder="Item name" value={li.item} onChange={(e) => handleLineItemChange(idx, 'item', e.target.value)} />
                            <Input label={idx === 0 ? "Qty" : ""} type="number" placeholder="Qty" value={li.quantity} onChange={(e) => handleLineItemChange(idx, 'quantity', e.target.value)} />
                            <Input label={idx === 0 ? "Unit Price" : ""} type="number" placeholder="₹" value={li.unitPrice} onChange={(e) => handleLineItemChange(idx, 'unitPrice', e.target.value)} />
                            <button onClick={() => removeLineItem(idx)} style={{ padding: '10px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', cursor: 'pointer', marginBottom: '0' }}>🗑</button>
                        </div>
                    ))}
                </div>

                <div>
                    <label style={{ display: 'block', fontSize: '14px', fontWeight: 500, marginBottom: '8px' }}>Notes</label>
                    <textarea name="notes" value={formData.notes} onChange={handleInputChange} rows={2} placeholder="Additional instructions..." style={{ width: '100%', padding: '10px 14px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
            </Modal>
        </MainLayout>
    );
}

function QuickActions({ onCreatePO }) {
    const actions = [
        { icon: '📝', title: 'Create PO', desc: 'New purchase order', onClick: onCreatePO },
        { icon: '👥', title: 'Add Vendor', desc: 'Register supplier', onClick: () => router.visit('/procurement/vendors') },
        { icon: '📥', title: 'Receive Goods', desc: 'Create GRN', onClick: () => router.visit('/procurement/grn') },
        { icon: '📊', title: 'Reports', desc: 'Procurement analytics', onClick: () => router.visit('/reports') },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Quick Actions</h3></div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                {actions.map((a, idx) => (
                    <div key={idx} onClick={a.onClick} style={{ padding: '16px', borderRadius: '12px', border: '1px solid var(--color-gray-200)', cursor: 'pointer' }}>
                        <span style={{ fontSize: '24px', display: 'block', marginBottom: '8px' }}>{a.icon}</span>
                        <div style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>{a.title}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{a.desc}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function SpendAnalysis() {
    const categories = [
        { name: 'Raw Materials', amount: 420000, pct: 65 },
        { name: 'Packaging', amount: 65000, pct: 10 },
        { name: 'Consumables', amount: 97500, pct: 15 },
        { name: 'Maintenance', amount: 65000, pct: 10 },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Spend by Category</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {categories.map((c, idx) => (
                    <div key={idx}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                            <span style={{ fontSize: '14px' }}>{c.name}</span>
                            <span style={{ fontSize: '14px', color: 'var(--color-gray-500)' }}>₹{(c.amount / 1000).toFixed(0)}K</span>
                        </div>
                        <div style={{ width: '100%', height: '8px', backgroundColor: 'var(--color-gray-200)', borderRadius: '4px' }}>
                            <div style={{ width: `${c.pct}%`, height: '100%', backgroundColor: 'var(--color-primary)', borderRadius: '4px' }} />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function ExpectedDeliveries() {
    const deliveries = [
        { date: '2026-02-05', vendor: 'Copper Wire Inc', po: 'PO-2026-040', items: 'Copper Wire 2mm', status: 'On Track' },
        { date: '2026-02-08', vendor: 'Aluminum Traders', po: 'PO-2026-041', items: 'Aluminum Sheets', status: 'On Track' },
        { date: '2026-02-10', vendor: 'Steel Corp Ltd', po: 'PO-2026-042', items: 'Steel Sheet, Bolts, Nuts', status: 'Pending Approval' },
    ];

    return (
        <div style={{ padding: '16px' }}>
            {deliveries.map((d, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', border: '1px solid var(--color-gray-200)', borderRadius: '12px', marginBottom: '12px' }}>
                    <div style={{ width: '60px', textAlign: 'center', marginRight: '16px' }}>
                        <div style={{ fontSize: '20px', fontWeight: 700, color: 'var(--color-primary)' }}>{d.date.split('-')[2]}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Feb</div>
                    </div>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 600 }}>{d.vendor}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d.po} • {d.items}</div>
                    </div>
                    <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: d.status === 'On Track' ? '#D1FAE5' : '#FEF3C7', color: d.status === 'On Track' ? '#059669' : '#D97706' }}>{d.status}</span>
                </div>
            ))}
        </div>
    );
}
