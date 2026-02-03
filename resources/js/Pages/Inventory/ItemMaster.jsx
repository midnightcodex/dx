import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const itemData = [
    { id: 1, code: 'RM-001', name: 'Steel Sheet 2mm', type: 'Raw Material', category: 'Metals', unit: 'kg', price: 45, tax: 18, stock: 5000, status: 'Active' },
    { id: 2, code: 'RM-002', name: 'Bolts M6', type: 'Raw Material', category: 'Fasteners', unit: 'pcs', price: 2.5, tax: 12, stock: 10000, status: 'Active' },
    { id: 3, code: 'FG-001', name: 'Steel Brackets A1', type: 'Finished Good', category: 'Components', unit: 'pcs', price: 150, tax: 18, stock: 1200, status: 'Active' },
    { id: 4, code: 'PK-001', name: 'Cardboard Box L', type: 'Packaging', category: 'Boxes', unit: 'pcs', price: 15, tax: 12, stock: 3000, status: 'Active' },
    { id: 5, code: 'CN-001', name: 'Lubricant Oil', type: 'Consumable', category: 'Fluids', unit: 'ltr', price: 250, tax: 18, stock: 50, status: 'Low Stock' },
];

const itemColumns = [
    { header: 'Item Code', accessor: 'code' },
    { header: 'Name', accessor: 'name' },
    { header: 'Type', accessor: 'type' },
    { header: 'Unit', accessor: 'unit' },
    { header: 'Std Price', accessor: 'price', render: (val) => `₹${val.toFixed(2)}` },
    { header: 'Stock', accessor: 'stock' },
    {
        header: 'Status', accessor: 'status', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Active' ? '#D1FAE5' : '#FEE2E2', color: val === 'Active' ? '#059669' : '#DC2626' }}>{val}</span>
        )
    },
];

export default function ItemMaster() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({
        code: '', name: '', type: '', category: '', unit: '', price: '', tax: '', description: ''
    });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

    const handleSubmit = () => {
        alert('Item Added!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ code: '', name: '', type: '', category: '', unit: '', price: '', tax: '', description: '' });
    };

    const activeItems = itemData.filter(i => i.status === 'Active');
    const lowStockItems = itemData.filter(i => i.status === 'Low Stock');

    return (
        <MainLayout title="Item Master" subtitle="Manage products, raw materials, and services">
            <div className="stats-grid">
                <StatsCard icon="📦" value={itemData.length} label="Total Items" variant="primary" />
                <StatsCard icon="🏭" value={itemData.filter(i => i.type === 'Raw Material').length} label="Raw Materials" variant="secondary" />
                <StatsCard icon="✨" value={itemData.filter(i => i.type === 'Finished Good').length} label="Finished Goods" variant="success" />
                <StatsCard icon="⚠️" value={lowStockItems.length} label="Low Stock" variant="danger" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add New Item
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Import Items
                </button>
                <button onClick={() => router.visit('/inventory')} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ⬅ Back to Inventory
                </button>
            </div>

            <div className="dashboard-grid">
                <ItemsByType />
                <ItemsByTax />
            </div>

            <DataTable columns={itemColumns} data={itemData} title="Item Directory" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Add New Item" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Save Item</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Item Code" name="code" value={formData.code} onChange={handleInputChange} required placeholder="e.g. RM-001" />
                    <Input label="Item Name" name="name" value={formData.name} onChange={handleInputChange} required placeholder="e.g. Steel Sheet" />

                    <Select label="Item Type" name="type" value={formData.type} onChange={handleInputChange} required options={[
                        { value: 'raw-material', label: 'Raw Material' },
                        { value: 'finished-good', label: 'Finished Good' },
                        { value: 'wip', label: 'Work In Progress' },
                        { value: 'packaging', label: 'Packaging' },
                        { value: 'consumable', label: 'Consumable' },
                        { value: 'service', label: 'Service' },
                    ]} />

                    <Select label="Category" name="category" value={formData.category} onChange={handleInputChange} required options={[
                        { value: 'metals', label: 'Metals' },
                        { value: 'plastics', label: 'Plastics' },
                        { value: 'electronics', label: 'Electronics' },
                        { value: 'fasteners', label: 'Fasteners' },
                        { value: 'fluids', label: 'Fluids' },
                    ]} />

                    <Select label="Unit of Measure" name="unit" value={formData.unit} onChange={handleInputChange} required options={[
                        { value: 'nos', label: 'Numbers (Nos)' },
                        { value: 'kg', label: 'Kilograms (Kg)' },
                        { value: 'mtr', label: 'Meters (Mtr)' },
                        { value: 'ltr', label: 'Liters (Ltr)' },
                        { value: 'box', label: 'Box' },
                    ]} />

                    <Input label="Standard Price" type="number" name="price" value={formData.price} onChange={handleInputChange} required placeholder="0.00" />
                    <Input label="Tax Rate (%)" type="number" name="tax" value={formData.tax} onChange={handleInputChange} placeholder="e.g. 18" />

                    <div style={{ gridColumn: 'span 2' }}>
                        <label style={{ display: 'block', fontSize: '14px', fontWeight: 500, marginBottom: '8px' }}>Description</label>
                        <textarea name="description" value={formData.description} onChange={handleInputChange} rows={2} placeholder="Item details..." style={{ width: '100%', padding: '10px 14px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px', fontFamily: 'inherit' }} />
                    </div>
                </div>
            </Modal>
        </MainLayout>
    );
}

function ItemsByType() {
    const data = [
        { name: 'Raw Material', count: 250 },
        { name: 'Finished Good', count: 45 },
        { name: 'Packaging', count: 12 },
        { name: 'Consumable', count: 30 },
    ];
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Attributes</h3></div>
            <div style={{ padding: '16px' }}>
                {data.map((d, i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                        <span>{d.name}</span>
                        <span style={{ fontWeight: 600 }}>{d.count}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function ItemsByTax() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Tax Classes</h3></div>
            <div style={{ padding: '16px' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                    <span>GST 18%</span>
                    <span style={{ fontWeight: 600 }}>85 items</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                    <span>GST 12%</span>
                    <span style={{ fontWeight: 600 }}>15 items</span>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                    <span>GST 5%</span>
                    <span style={{ fontWeight: 600 }}>4 items</span>
                </div>
            </div>
        </div>
    )
}
