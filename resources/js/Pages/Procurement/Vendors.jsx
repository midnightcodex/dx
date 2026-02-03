import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const vendorData = [
    { id: 1, code: 'V-001', name: 'Steel Corp Ltd', category: 'Raw Materials', contact: 'Rajesh Kumar', phone: '+91 98765 43210', email: 'rajesh@steelcorp.com', rating: 4.5, status: 'Active' },
    { id: 2, code: 'V-002', name: 'Aluminum Traders', category: 'Raw Materials', contact: 'Priya Sharma', phone: '+91 98765 43211', email: 'priya@aluminumtraders.com', rating: 4.2, status: 'Active' },
    { id: 3, code: 'V-003', name: 'Copper Wire Inc', category: 'Raw Materials', contact: 'Amit Patel', phone: '+91 98765 43212', email: 'amit@copperwire.com', rating: 3.8, status: 'Active' },
    { id: 4, code: 'V-004', name: 'Packaging Solutions', category: 'Packaging', contact: 'Sneha Reddy', phone: '+91 98765 43213', email: 'sneha@packagingsol.com', rating: 4.7, status: 'Active' },
];

const vendorColumns = [
    { header: 'Code', accessor: 'code' },
    { header: 'Vendor Name', accessor: 'name' },
    { header: 'Category', accessor: 'category' },
    { header: 'Contact', accessor: 'contact' },
    { header: 'Phone', accessor: 'phone' },
    {
        header: 'Rating', accessor: 'rating', render: (val) => (
            <div style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
                <span style={{ color: '#F59E0B' }}>★</span>
                <span style={{ fontWeight: 500 }}>{val}</span>
            </div>
        )
    },
    {
        header: 'Status', accessor: 'status', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Active' ? '#D1FAE5' : '#F3F4F6', color: val === 'Active' ? '#059669' : '#6B7280' }}>{val}</span>
        )
    },
];

export default function Vendors() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ name: '', category: '', contact: '', phone: '', email: '', address: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Vendor Added!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ name: '', category: '', contact: '', phone: '', email: '', address: '' });
    };

    const activeVendors = vendorData.filter(v => v.status === 'Active');
    const avgRating = (vendorData.reduce((sum, v) => sum + v.rating, 0) / vendorData.length).toFixed(1);

    return (
        <MainLayout title="Vendors" subtitle="Manage supplier relationships">
            <div className="stats-grid">
                <StatsCard icon="👥" value={vendorData.length} label="Total Vendors" variant="primary" />
                <StatsCard icon="✅" value={activeVendors.length} label="Active Vendors" variant="success" />
                <StatsCard icon="⭐" value={avgRating} label="Avg Rating" variant="warning" />
                <StatsCard icon="📦" value={42} label="Orders This Month" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Vendor
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <div className="dashboard-grid">
                <TopVendors vendors={vendorData} />
                <VendorCategories />
            </div>

            <DataTable columns={vendorColumns} data={vendorData} title="Vendor Directory" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Add New Vendor" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Vendor</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Vendor Name" name="name" value={formData.name} onChange={handleInputChange} required placeholder="e.g. ABC Supplies Ltd" />
                    <Select label="Category" name="category" value={formData.category} onChange={handleInputChange} required options={[
                        { value: 'raw-materials', label: 'Raw Materials' },
                        { value: 'packaging', label: 'Packaging' },
                        { value: 'consumables', label: 'Consumables' },
                        { value: 'services', label: 'Services' },
                    ]} />
                    <Input label="Contact Person" name="contact" value={formData.contact} onChange={handleInputChange} placeholder="Primary contact name" />
                    <Input label="Phone" name="phone" value={formData.phone} onChange={handleInputChange} placeholder="+91 98765 43210" />
                    <Input label="Email" type="email" name="email" value={formData.email} onChange={handleInputChange} placeholder="vendor@example.com" />
                    <Input label="Address" name="address" value={formData.address} onChange={handleInputChange} placeholder="Office address" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function TopVendors({ vendors }) {
    const sorted = [...vendors].sort((a, b) => b.rating - a.rating).slice(0, 4);
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Top Rated Vendors</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {sorted.map((v, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px', fontWeight: 600, color: 'var(--color-primary)' }}>{idx + 1}</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{v.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{v.category}</div>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '4px', color: '#F59E0B' }}>★ <span style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>{v.rating}</span></div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function VendorCategories() {
    const categories = [
        { name: 'Raw Materials', count: 3, spend: '₹4.2L' },
        { name: 'Packaging', count: 1, spend: '₹65K' },
        { name: 'Consumables', count: 1, spend: '₹45K' },
    ];
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Vendors by Category</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {categories.map((c, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{c.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{c.count} vendors</div>
                        </div>
                        <div style={{ fontWeight: 600, color: 'var(--color-primary)' }}>{c.spend}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
