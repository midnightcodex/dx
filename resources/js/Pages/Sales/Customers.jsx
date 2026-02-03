import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const customerData = [
    { id: 1, code: 'C-001', name: 'Global Exports', type: 'Enterprise', contact: 'Ramesh Gupta', phone: '+91 98765 12345', creditLimit: 5000000, outstanding: 1250000, status: 'Active' },
    { id: 2, code: 'C-002', name: 'ABC Industries', type: 'Corporate', contact: 'Priya Menon', phone: '+91 98765 12346', creditLimit: 3000000, outstanding: 450000, status: 'Active' },
    { id: 3, code: 'C-003', name: 'XYZ Manufacturing', type: 'SME', contact: 'Suresh Kumar', phone: '+91 98765 12347', creditLimit: 1500000, outstanding: 280000, status: 'Active' },
    { id: 4, code: 'C-004', name: 'Tech Solutions Ltd', type: 'Corporate', contact: 'Anita Sharma', phone: '+91 98765 12348', creditLimit: 2000000, outstanding: 0, status: 'Active' },
    { id: 5, code: 'C-005', name: 'Metro Traders', type: 'SME', contact: 'Vikash Jain', phone: '+91 98765 12349', creditLimit: 500000, outstanding: 480000, status: 'Over Limit' },
];

const customerColumns = [
    { header: 'Code', accessor: 'code' },
    { header: 'Customer Name', accessor: 'name' },
    { header: 'Type', accessor: 'type' },
    { header: 'Contact', accessor: 'contact' },
    { header: 'Credit Limit', accessor: 'creditLimit', render: (val) => `₹${(val / 100000).toFixed(1)}L` },
    { header: 'Outstanding', accessor: 'outstanding', render: (val) => `₹${(val / 100000).toFixed(1)}L` },
    {
        header: 'Status', accessor: 'status', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Active' ? '#D1FAE5' : '#FEE2E2', color: val === 'Active' ? '#059669' : '#DC2626' }}>{val}</span>
        )
    },
];

export default function Customers() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ name: '', type: '', contact: '', phone: '', email: '', gst: '', creditLimit: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Customer Added!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ name: '', type: '', contact: '', phone: '', email: '', gst: '', creditLimit: '' });
    };

    const totalOutstanding = customerData.reduce((sum, c) => sum + c.outstanding, 0);
    const overLimit = customerData.filter(c => c.status === 'Over Limit');

    return (
        <MainLayout title="Customers" subtitle="Manage customer relationships">
            <div className="stats-grid">
                <StatsCard icon="👥" value={customerData.length} label="Total Customers" variant="primary" />
                <StatsCard icon="✅" value={customerData.filter(c => c.status === 'Active').length} label="Active" variant="success" />
                <StatsCard icon="💰" value={`₹${(totalOutstanding / 100000).toFixed(1)}L`} label="Total Outstanding" variant="warning" />
                <StatsCard icon="⚠️" value={overLimit.length} label="Over Credit Limit" variant="danger" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Customer
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <div className="dashboard-grid">
                <CustomerByType />
                <CreditUtilization customers={customerData} />
            </div>

            <DataTable columns={customerColumns} data={customerData} title="Customer Directory" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Add New Customer" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Customer</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Customer Name" name="name" value={formData.name} onChange={handleInputChange} required placeholder="e.g. ABC Industries" />
                    <Select label="Customer Type" name="type" value={formData.type} onChange={handleInputChange} required options={[
                        { value: 'enterprise', label: 'Enterprise' },
                        { value: 'corporate', label: 'Corporate' },
                        { value: 'sme', label: 'SME' },
                        { value: 'retail', label: 'Retail' },
                    ]} />
                    <Input label="Contact Person" name="contact" value={formData.contact} onChange={handleInputChange} placeholder="Primary contact" />
                    <Input label="Phone" name="phone" value={formData.phone} onChange={handleInputChange} placeholder="+91 98765 12345" />
                    <Input label="Email" type="email" name="email" value={formData.email} onChange={handleInputChange} placeholder="customer@company.com" />
                    <Input label="GST Number" name="gst" value={formData.gst} onChange={handleInputChange} placeholder="GST registration" />
                    <Input label="Credit Limit (₹)" type="number" name="creditLimit" value={formData.creditLimit} onChange={handleInputChange} required placeholder="e.g. 1000000" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function CustomerByType() {
    const types = [
        { name: 'Enterprise', count: 1, revenue: '₹25L' },
        { name: 'Corporate', count: 2, revenue: '₹29.5L' },
        { name: 'SME', count: 2, revenue: '₹21.5L' },
    ];
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Customers by Type</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {types.map((t, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{t.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{t.count} customers</div>
                        </div>
                        <div style={{ fontWeight: 600, color: 'var(--color-primary)' }}>{t.revenue}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function CreditUtilization({ customers }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Credit Utilization</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {customers.slice(0, 4).map((c, idx) => {
                    const pct = Math.round((c.outstanding / c.creditLimit) * 100);
                    return (
                        <div key={idx}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                                <span style={{ fontSize: '14px' }}>{c.name}</span>
                                <span style={{ fontSize: '12px', color: pct > 90 ? 'var(--color-danger)' : 'var(--color-gray-500)' }}>{pct}%</span>
                            </div>
                            <div style={{ width: '100%', height: '8px', backgroundColor: 'var(--color-gray-200)', borderRadius: '4px' }}>
                                <div style={{ width: `${pct}%`, height: '100%', backgroundColor: pct > 90 ? 'var(--color-danger)' : pct > 70 ? 'var(--color-warning)' : 'var(--color-success)', borderRadius: '4px' }} />
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
