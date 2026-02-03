import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const orderData = [
    { id: 1, soNumber: 'SO-2026-085', customer: 'ABC Industries', orderDate: '2026-02-02', deliveryDate: '2026-02-10', totalAmount: 450000, status: 'Confirmed', paymentStatus: 'Pending' },
    { id: 2, soNumber: 'SO-2026-084', customer: 'XYZ Manufacturing', orderDate: '2026-02-01', deliveryDate: '2026-02-08', totalAmount: 280000, status: 'Processing', paymentStatus: 'Partial' },
    { id: 3, soNumber: 'SO-2026-083', customer: 'Tech Solutions Ltd', orderDate: '2026-01-28', deliveryDate: '2026-02-05', totalAmount: 125000, status: 'Shipped', paymentStatus: 'Paid' },
    { id: 4, soNumber: 'SO-2026-082', customer: 'Global Exports', orderDate: '2026-01-25', deliveryDate: '2026-02-01', totalAmount: 750000, status: 'Delivered', paymentStatus: 'Paid' },
];

const orderColumns = [
    { header: 'SO Number', accessor: 'soNumber' },
    { header: 'Customer', accessor: 'customer' },
    { header: 'Order Date', accessor: 'orderDate' },
    { header: 'Delivery', accessor: 'deliveryDate' },
    { header: 'Amount', accessor: 'totalAmount', render: (val) => `₹${val.toLocaleString()}` },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Confirmed: { bg: '#DBEAFE', color: '#1D4ED8' }, Processing: { bg: '#FEF3C7', color: '#D97706' }, Shipped: { bg: '#EDE9FE', color: '#7C3AED' }, Delivered: { bg: '#D1FAE5', color: '#059669' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
    {
        header: 'Payment', accessor: 'paymentStatus', render: (val) => {
            const colors = { Paid: 'var(--color-success)', Partial: 'var(--color-warning)', Pending: 'var(--color-gray-500)', Overdue: 'var(--color-danger)' };
            return <span style={{ color: colors[val], fontWeight: 500 }}>● {val}</span>;
        }
    },
];

export default function SalesOrders() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ customer: '', deliveryDate: '', paymentTerms: '' });
    const [items, setItems] = useState([{ product: '', qty: '', price: '' }]);

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleItemChange = (idx, field, value) => { const updated = [...items]; updated[idx][field] = value; setItems(updated); };
    const addItem = () => setItems([...items, { product: '', qty: '', price: '' }]);
    const removeItem = (idx) => items.length > 1 && setItems(items.filter((_, i) => i !== idx));

    const handleSubmit = () => {
        alert('Sales Order Created!\n' + JSON.stringify({ ...formData, items }, null, 2));
        setShowModal(false);
        setFormData({ customer: '', deliveryDate: '', paymentTerms: '' });
        setItems([{ product: '', qty: '', price: '' }]);
    };

    const totalValue = orderData.reduce((sum, o) => sum + o.totalAmount, 0);
    const processing = orderData.filter(o => ['Confirmed', 'Processing'].includes(o.status));
    const shipped = orderData.filter(o => o.status === 'Shipped');

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={orderColumns} data={orderData} title="All Sales Orders" /> },
        { label: 'Processing', badge: processing.length, content: <DataTable columns={orderColumns} data={processing} title="Processing" /> },
        { label: 'In Transit', badge: shipped.length, content: <DataTable columns={orderColumns} data={shipped} title="In Transit" /> },
    ];

    return (
        <MainLayout title="Sales Orders" subtitle="Manage customer orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={orderData.length} label="Total Orders" variant="primary" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Order Value" variant="success" />
                <StatsCard icon="🔄" value={processing.length} label="Processing" variant="warning" />
                <StatsCard icon="🚚" value={shipped.length} label="In Transit" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create Sales Order
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <Tabs tabs={tabs} />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Sales Order" size="xl" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Order</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Select label="Customer" name="customer" value={formData.customer} onChange={handleInputChange} required options={[
                        { value: 'c-001', label: 'Global Exports' },
                        { value: 'c-002', label: 'ABC Industries' },
                        { value: 'c-003', label: 'XYZ Manufacturing' },
                    ]} />
                    <Input label="Delivery Date" type="date" name="deliveryDate" value={formData.deliveryDate} onChange={handleInputChange} required />
                    <Select label="Payment Terms" name="paymentTerms" value={formData.paymentTerms} onChange={handleInputChange} options={[
                        { value: 'net-30', label: 'Net 30' },
                        { value: 'net-60', label: 'Net 60' },
                        { value: 'advance', label: 'Advance Payment' },
                    ]} />
                </div>
                <div>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h4 style={{ margin: 0 }}>Line Items</h4>
                        <button onClick={addItem} style={{ padding: '6px 12px', borderRadius: '6px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '12px', cursor: 'pointer' }}>+ Add Item</button>
                    </div>
                    {items.map((item, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Select label={idx === 0 ? "Product" : ""} value={item.product} onChange={(e) => handleItemChange(idx, 'product', e.target.value)} options={[
                                { value: 'fg-001', label: 'Steel Brackets A1' },
                                { value: 'fg-002', label: 'Aluminum Plates B2' },
                                { value: 'fg-003', label: 'Copper Connectors' },
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
        </MainLayout >
    );
}
