import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import RevenueChart from '../../Components/Charts/RevenueChart';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const salesOrderData = [
    { id: 1, soNumber: 'SO-2026-089', customer: 'ABC Manufacturing', items: 4, totalAmount: 450000, status: 'Confirmed', orderDate: '2026-02-02', deliveryDate: '2026-02-12' },
    { id: 2, soNumber: 'SO-2026-088', customer: 'XYZ Industries', items: 2, totalAmount: 280000, status: 'Processing', orderDate: '2026-02-01', deliveryDate: '2026-02-10' },
    { id: 3, soNumber: 'SO-2026-087', customer: 'PQR Enterprises', items: 6, totalAmount: 720000, status: 'Pending Payment', orderDate: '2026-01-30', deliveryDate: '2026-02-08' },
    { id: 4, soNumber: 'SO-2026-086', customer: 'LMN Trading', items: 1, totalAmount: 95000, status: 'Shipped', orderDate: '2026-01-28', deliveryDate: '2026-02-05' },
];

const soColumns = [
    { header: 'SO #', accessor: 'soNumber' },
    { header: 'Customer', accessor: 'customer' },
    { header: 'Items', accessor: 'items' },
    { header: 'Amount', accessor: 'totalAmount', render: (val) => `₹${val.toLocaleString()}` },
    { header: 'Order Date', accessor: 'orderDate' },
    { header: 'Delivery', accessor: 'deliveryDate' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = {
                'Confirmed': { bg: '#D1FAE5', color: '#059669' },
                'Processing': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Pending Payment': { bg: '#FEF3C7', color: '#D97706' },
                'Shipped': { bg: '#EDE9FE', color: '#7C3AED' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function SalesIndex() {
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showCustomerModal, setShowCustomerModal] = useState(false);
    const [showDeliveryModal, setShowDeliveryModal] = useState(false);
    const [formData, setFormData] = useState({ customer: '', deliveryDate: '', paymentTerms: '', shippingAddress: '' });
    const [lineItems, setLineItems] = useState([{ product: '', quantity: '', unitPrice: '' }]);
    const [customerFormData, setCustomerFormData] = useState({ name: '', type: '', contact: '', phone: '', email: '', creditLimit: '' });
    const [deliveryFormData, setDeliveryFormData] = useState({ soNumber: '', vehicle: '', driver: '', dispatchDate: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleLineItemChange = (idx, field, value) => { const updated = [...lineItems]; updated[idx][field] = value; setLineItems(updated); };
    const addLineItem = () => setLineItems([...lineItems, { product: '', quantity: '', unitPrice: '' }]);
    const removeLineItem = (idx) => lineItems.length > 1 && setLineItems(lineItems.filter((_, i) => i !== idx));

    const handleSubmit = () => {
        alert('Sales Order Created!\n' + JSON.stringify({ ...formData, lineItems }, null, 2));
        setShowCreateModal(false);
        setFormData({ customer: '', deliveryDate: '', paymentTerms: '', shippingAddress: '' });
        setLineItems([{ product: '', quantity: '', unitPrice: '' }]);
    };

    const handleCustomerSubmit = () => {
        alert('Customer Added!\n' + JSON.stringify(customerFormData, null, 2));
        setShowCustomerModal(false);
        setCustomerFormData({ name: '', type: '', contact: '', phone: '', email: '', creditLimit: '' });
    };

    const handleDeliverySubmit = () => {
        alert('Delivery Note Created!\n' + JSON.stringify(deliveryFormData, null, 2));
        setShowDeliveryModal(false);
        setDeliveryFormData({ soNumber: '', vehicle: '', driver: '', dispatchDate: '' });
    };

    const totalValue = salesOrderData.reduce((sum, so) => sum + so.totalAmount, 0);

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={soColumns} data={salesOrderData} title="Sales Orders" /> },
        { label: 'Pending', badge: 1, content: <DataTable columns={soColumns} data={salesOrderData.filter(s => s.status === 'Pending Payment')} title="Pending Payment" /> },
        { label: 'Ready to Ship', content: <DataTable columns={soColumns} data={salesOrderData.filter(s => s.status === 'Confirmed')} title="Ready to Ship" /> },
    ];

    return (
        <MainLayout title="Sales" subtitle="Orders, customers and revenue">
            <div className="stats-grid">
                <StatsCard icon="📋" value={salesOrderData.length} label="Active Orders" variant="primary" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Order Value" trend={12} trendLabel="vs last month" variant="success" />
                <StatsCard icon="📦" value={2} label="Ready to Ship" variant="primary" />
                <StatsCard icon="👥" value={24} label="Active Customers" variant="primary" />
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowCreateModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create Sales Order
                </button>
                <button onClick={() => setShowCustomerModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    👤 Add Customer
                </button>
                <button onClick={() => setShowDeliveryModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📦 Create Delivery Note
                </button>
            </div>

            {/* Add Customer Modal */}
            <Modal isOpen={showCustomerModal} onClose={() => setShowCustomerModal(false)} title="Add Customer" size="lg" footer={
                <>
                    <button onClick={() => setShowCustomerModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleCustomerSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Customer</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Customer Name" name="name" value={customerFormData.name} onChange={(e) => setCustomerFormData({ ...customerFormData, name: e.target.value })} required placeholder="e.g. ABC Industries" />
                    <Select label="Customer Type" name="type" value={customerFormData.type} onChange={(e) => setCustomerFormData({ ...customerFormData, type: e.target.value })} required options={[{ value: 'enterprise', label: 'Enterprise' }, { value: 'corporate', label: 'Corporate' }, { value: 'sme', label: 'SME' }]} />
                    <Input label="Contact Person" name="contact" value={customerFormData.contact} onChange={(e) => setCustomerFormData({ ...customerFormData, contact: e.target.value })} placeholder="Primary contact" />
                    <Input label="Phone" name="phone" value={customerFormData.phone} onChange={(e) => setCustomerFormData({ ...customerFormData, phone: e.target.value })} placeholder="+91 98765 12345" />
                    <Input label="Email" type="email" name="email" value={customerFormData.email} onChange={(e) => setCustomerFormData({ ...customerFormData, email: e.target.value })} placeholder="customer@company.com" />
                    <Input label="Credit Limit (₹)" type="number" name="creditLimit" value={customerFormData.creditLimit} onChange={(e) => setCustomerFormData({ ...customerFormData, creditLimit: e.target.value })} placeholder="e.g. 1000000" />
                </div>
            </Modal>

            {/* Create Delivery Note Modal */}
            <Modal isOpen={showDeliveryModal} onClose={() => setShowDeliveryModal(false)} title="Create Delivery Note" size="lg" footer={
                <>
                    <button onClick={() => setShowDeliveryModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleDeliverySubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Delivery Note</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Sales Order" name="soNumber" value={deliveryFormData.soNumber} onChange={(e) => setDeliveryFormData({ ...deliveryFormData, soNumber: e.target.value })} required options={salesOrderData.filter(s => s.status === 'Confirmed').map(s => ({ value: s.soNumber, label: `${s.soNumber} - ${s.customer}` }))} />
                    <Input label="Dispatch Date" type="date" name="dispatchDate" value={deliveryFormData.dispatchDate} onChange={(e) => setDeliveryFormData({ ...deliveryFormData, dispatchDate: e.target.value })} required />
                    <Input label="Vehicle Number" name="vehicle" value={deliveryFormData.vehicle} onChange={(e) => setDeliveryFormData({ ...deliveryFormData, vehicle: e.target.value })} placeholder="e.g. MH-12-AB-1234" />
                    <Input label="Driver Name" name="driver" value={deliveryFormData.driver} onChange={(e) => setDeliveryFormData({ ...deliveryFormData, driver: e.target.value })} placeholder="Driver name" />
                </div>
            </Modal>

            <div className="dashboard-grid">
                <RevenueChart />
                <TopCustomers />
            </div>

            <Tabs tabs={tabs} />

            {/* Create Sales Order Modal */}
            <Modal
                isOpen={showCreateModal}
                onClose={() => setShowCreateModal(false)}
                title="Create Sales Order"
                size="xl"
                footer={
                    <>
                        <button onClick={() => setShowCreateModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                        <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Order</button>
                    </>
                }
            >
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Select
                        label="Customer"
                        name="customer"
                        value={formData.customer}
                        onChange={handleInputChange}
                        required
                        options={[
                            { value: 'abc-mfg', label: 'ABC Manufacturing' },
                            { value: 'xyz-ind', label: 'XYZ Industries' },
                            { value: 'pqr-ent', label: 'PQR Enterprises' },
                        ]}
                    />
                    <Input
                        label="Delivery Date"
                        type="date"
                        name="deliveryDate"
                        value={formData.deliveryDate}
                        onChange={handleInputChange}
                        required
                    />
                    <Select
                        label="Payment Terms"
                        name="paymentTerms"
                        value={formData.paymentTerms}
                        onChange={handleInputChange}
                        options={[
                            { value: 'advance', label: '100% Advance' },
                            { value: 'net-30', label: 'Net 30 Days' },
                            { value: 'cod', label: 'Cash on Delivery' },
                        ]}
                    />
                    <Input
                        label="Shipping Address"
                        name="shippingAddress"
                        value={formData.shippingAddress}
                        onChange={handleInputChange}
                        placeholder="Enter shipping address"
                    />
                </div>

                <div style={{ marginBottom: '16px' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h4 style={{ margin: 0 }}>Products</h4>
                        <button onClick={addLineItem} style={{ padding: '6px 12px', borderRadius: '6px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '12px', cursor: 'pointer' }}>+ Add Product</button>
                    </div>
                    {lineItems.map((li, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Select label={idx === 0 ? "Product" : ""} placeholder="Select product" value={li.product} onChange={(e) => handleLineItemChange(idx, 'product', e.target.value)} options={[{ value: 'gear-a', label: 'Gear Assembly A' }, { value: 'shaft-b', label: 'Shaft Component B' }]} />
                            <Input label={idx === 0 ? "Qty" : ""} type="number" placeholder="Qty" value={li.quantity} onChange={(e) => handleLineItemChange(idx, 'quantity', e.target.value)} />
                            <Input label={idx === 0 ? "Unit Price" : ""} type="number" placeholder="₹" value={li.unitPrice} onChange={(e) => handleLineItemChange(idx, 'unitPrice', e.target.value)} />
                            <button onClick={() => removeLineItem(idx)} style={{ padding: '10px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', cursor: 'pointer' }}>🗑</button>
                        </div>
                    ))}
                </div>
            </Modal>
        </MainLayout>
    );
}

function TopCustomers() {
    const customers = [
        { name: 'ABC Manufacturing', value: 1250000, orders: 12 },
        { name: 'XYZ Industries', value: 980000, orders: 8 },
        { name: 'PQR Enterprises', value: 720000, orders: 15 },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Top Customers</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {customers.map((c, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ width: '36px', height: '36px', borderRadius: '50%', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px', fontWeight: 600, color: 'var(--color-primary)' }}>{idx + 1}</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 500 }}>{c.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{c.orders} orders</div>
                        </div>
                        <div style={{ fontWeight: 600, color: 'var(--color-success)' }}>₹{(c.value / 100000).toFixed(1)}L</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
