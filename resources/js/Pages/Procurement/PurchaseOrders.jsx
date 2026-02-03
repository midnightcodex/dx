import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const poData = [
    { id: 1, poNumber: 'PO-2026-042', vendor: 'Steel Corp Ltd', orderDate: '2026-02-02', expectedDate: '2026-02-10', totalAmount: 250000, status: 'Pending Approval' },
    { id: 2, poNumber: 'PO-2026-041', vendor: 'Aluminum Traders', orderDate: '2026-02-01', expectedDate: '2026-02-08', totalAmount: 175000, status: 'Approved' },
    { id: 3, poNumber: 'PO-2026-040', vendor: 'Copper Wire Inc', orderDate: '2026-01-28', expectedDate: '2026-02-05', totalAmount: 45000, status: 'Partially Received' },
    { id: 4, poNumber: 'PO-2026-039', vendor: 'Packaging Solutions', orderDate: '2026-01-25', expectedDate: '2026-02-01', totalAmount: 32000, status: 'Completed' },
];

const poColumns = [
    { header: 'PO Number', accessor: 'poNumber' },
    { header: 'Vendor', accessor: 'vendor' },
    { header: 'Order Date', accessor: 'orderDate' },
    { header: 'Expected', accessor: 'expectedDate' },
    { header: 'Amount', accessor: 'totalAmount', render: (val) => `₹${val.toLocaleString()}` },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { 'Pending Approval': { bg: '#FEF3C7', color: '#D97706' }, Approved: { bg: '#DBEAFE', color: '#1D4ED8' }, 'Partially Received': { bg: '#EDE9FE', color: '#7C3AED' }, Completed: { bg: '#D1FAE5', color: '#059669' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function PurchaseOrders() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ vendor: '', expectedDate: '', paymentTerms: '' });
    const [items, setItems] = useState([{ item: '', qty: '', price: '' }]);

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleItemChange = (idx, field, value) => { const updated = [...items]; updated[idx][field] = value; setItems(updated); };
    const addItem = () => setItems([...items, { item: '', qty: '', price: '' }]);
    const removeItem = (idx) => items.length > 1 && setItems(items.filter((_, i) => i !== idx));

    const handleSubmit = () => {
        alert('Purchase Order Created!\n' + JSON.stringify({ ...formData, items }, null, 2));
        setShowModal(false);
        setFormData({ vendor: '', expectedDate: '', paymentTerms: '' });
        setItems([{ item: '', qty: '', price: '' }]);
    };

    const pending = poData.filter(po => po.status === 'Pending Approval');
    const inProgress = poData.filter(po => ['Approved', 'Partially Received'].includes(po.status));
    const completed = poData.filter(po => po.status === 'Completed');
    const totalValue = poData.reduce((sum, po) => sum + po.totalAmount, 0);

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={poColumns} data={poData} title="All Purchase Orders" /> },
        { label: 'Pending Approval', badge: pending.length, content: <DataTable columns={poColumns} data={pending} title="Pending Approval" /> },
        { label: 'In Progress', content: <DataTable columns={poColumns} data={inProgress} title="In Progress" /> },
        { label: 'Completed', content: <DataTable columns={poColumns} data={completed} title="Completed Orders" /> },
    ];

    return (
        <MainLayout title="Purchase Orders" subtitle="Manage procurement orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={poData.length} label="Total POs" variant="primary" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Total Value" variant="success" />
                <StatsCard icon="⏳" value={pending.length} label="Pending Approval" variant="warning" />
                <StatsCard icon="✅" value={completed.length} label="Completed" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create PO
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <Tabs tabs={tabs} />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Purchase Order" size="xl" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create PO</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Select label="Vendor" name="vendor" value={formData.vendor} onChange={handleInputChange} required options={[
                        { value: 'v-001', label: 'Steel Corp Ltd' },
                        { value: 'v-002', label: 'Aluminum Traders' },
                        { value: 'v-003', label: 'Copper Wire Inc' },
                    ]} />
                    <Input label="Expected Date" type="date" name="expectedDate" value={formData.expectedDate} onChange={handleInputChange} required />
                    <Select label="Payment Terms" name="paymentTerms" value={formData.paymentTerms} onChange={handleInputChange} options={[
                        { value: 'net-30', label: 'Net 30' },
                        { value: 'net-60', label: 'Net 60' },
                        { value: 'cod', label: 'Cash on Delivery' },
                    ]} />
                </div>
                <div>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h4 style={{ margin: 0 }}>Line Items</h4>
                        <button onClick={addItem} style={{ padding: '6px 12px', borderRadius: '6px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '12px', cursor: 'pointer' }}>+ Add Item</button>
                    </div>
                    {items.map((item, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Select label={idx === 0 ? "Item" : ""} value={item.item} onChange={(e) => handleItemChange(idx, 'item', e.target.value)} options={[
                                { value: 'rm-001', label: 'Raw Steel Sheet 2mm' },
                                { value: 'rm-002', label: 'Aluminum Sheets 3mm' },
                                { value: 'rm-003', label: 'Copper Wire 2mm' },
                            ]} />
                            <Input label={idx === 0 ? "Qty" : ""} type="number" value={item.qty} onChange={(e) => handleItemChange(idx, 'qty', e.target.value)} placeholder="Qty" />
                            <Input label={idx === 0 ? "Unit Price" : ""} type="number" value={item.price} onChange={(e) => handleItemChange(idx, 'price', e.target.value)} placeholder="₹" />
                            <button onClick={() => removeItem(idx)} style={{ padding: '10px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', cursor: 'pointer' }}>🗑</button>
                        </div>
                    ))}

                    {/* Totals Section */}
                    <div style={{ marginTop: '24px', paddingTop: '16px', borderTop: '1px solid var(--color-gray-200)', display: 'flex', justifyContent: 'flex-end' }}>
                        <div style={{ width: '250px' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '14px' }}>
                                <span style={{ color: 'var(--color-gray-500)' }}>Subtotal:</span>
                                <span>₹{items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price) || 0), 0).toLocaleString()}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px', fontSize: '14px' }}>
                                <span style={{ color: 'var(--color-gray-500)' }}>Tax (18%):</span>
                                <span>₹{(items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price) || 0), 0) * 0.18).toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: '12px', paddingTop: '12px', borderTop: '1px dashed var(--color-gray-300)', fontSize: '16px', fontWeight: 700 }}>
                                <span>Total:</span>
                                <span style={{ color: 'var(--color-primary)' }}>₹{(items.reduce((sum, i) => sum + (Number(i.qty) * Number(i.price) || 0), 0) * 1.18).toLocaleString(undefined, { maximumFractionDigits: 2 })}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </MainLayout>
    );
}
