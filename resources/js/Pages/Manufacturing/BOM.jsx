import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const bomData = [
    { id: 1, code: 'BOM-001', product: 'Steel Brackets A1', components: 5, revision: 'Rev 3', status: 'Active', lastUpdated: '2026-01-28' },
    { id: 2, code: 'BOM-002', product: 'Aluminum Plates B2', components: 8, revision: 'Rev 2', status: 'Active', lastUpdated: '2026-01-25' },
    { id: 3, code: 'BOM-003', product: 'Copper Connectors C1', components: 3, revision: 'Rev 1', status: 'Draft', lastUpdated: '2026-02-01' },
    { id: 4, code: 'BOM-004', product: 'Plastic Housings D1', components: 6, revision: 'Rev 4', status: 'Active', lastUpdated: '2026-01-20' },
    { id: 5, code: 'BOM-005', product: 'Electronic PCB E1', components: 12, revision: 'Rev 2', status: 'Under Review', lastUpdated: '2026-01-30' },
];

const bomColumns = [
    { header: 'BOM Code', accessor: 'code' },
    { header: 'Product', accessor: 'product' },
    { header: 'Components', accessor: 'components' },
    { header: 'Revision', accessor: 'revision' },
    { header: 'Last Updated', accessor: 'lastUpdated' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = {
                'Active': { bg: '#D1FAE5', color: '#059669' },
                'Draft': { bg: '#F3F4F6', color: '#6B7280' },
                'Under Review': { bg: '#FEF3C7', color: '#D97706' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return (
                <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>
                    {val}
                </span>
            );
        }
    },
];

export default function BOM() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ productName: '', productCode: '', revision: '' });
    const [components, setComponents] = useState([{ item: '', quantity: '', unit: '' }]);

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleComponentChange = (idx, field, value) => {
        const updated = [...components];
        updated[idx][field] = value;
        setComponents(updated);
    };
    const addComponent = () => setComponents([...components, { item: '', quantity: '', unit: '' }]);
    const removeComponent = (idx) => components.length > 1 && setComponents(components.filter((_, i) => i !== idx));

    const handleSubmit = () => {
        alert('BOM Created!\n' + JSON.stringify({ ...formData, components }, null, 2));
        setShowModal(false);
        setFormData({ productName: '', productCode: '', revision: '' });
        setComponents([{ item: '', quantity: '', unit: '' }]);
    };

    return (
        <MainLayout title="Bill of Materials" subtitle="Manage product structures and components">
            <div className="stats-grid">
                <StatsCard icon="📋" value={42} label="Total BOMs" trend="3 new this month" trendDirection="up" variant="primary" />
                <StatsCard icon="✅" value={38} label="Active BOMs" trend="All validated" trendDirection="up" variant="success" />
                <StatsCard icon="📝" value={3} label="Under Review" trend="Pending approval" trendDirection="up" variant="warning" />
                <StatsCard icon="🔧" value={1} label="Drafts" trend="In progress" trendDirection="up" variant="primary" />
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create BOM
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Import BOM
                </button>
            </div>

            <BOMTreePreview />
            <DataTable columns={bomColumns} data={bomData} title="BOM List" />

            {/* Create BOM Modal */}
            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create New BOM" size="xl" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create BOM</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Input label="Product Name" name="productName" value={formData.productName} onChange={handleInputChange} required placeholder="e.g. Steel Brackets A1" />
                    <Input label="Product Code" name="productCode" value={formData.productCode} onChange={handleInputChange} required placeholder="e.g. FG-001" />
                    <Input label="Revision" name="revision" value={formData.revision} onChange={handleInputChange} placeholder="e.g. Rev 1" />
                </div>
                <div>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
                        <h4 style={{ margin: 0 }}>Components</h4>
                        <button onClick={addComponent} style={{ padding: '6px 12px', borderRadius: '6px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '12px', cursor: 'pointer' }}>+ Add Component</button>
                    </div>
                    {components.map((c, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr auto', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Select label={idx === 0 ? "Raw Material" : ""} value={c.item} onChange={(e) => handleComponentChange(idx, 'item', e.target.value)} options={[
                                { value: 'rm-001', label: 'Steel Sheet 2mm (RM-001)' },
                                { value: 'rm-002', label: 'Bolts M6 (RM-002)' },
                                { value: 'rm-003', label: 'Nuts M6 (RM-003)' },
                            ]} />
                            <Input label={idx === 0 ? "Qty" : ""} type="number" value={c.quantity} onChange={(e) => handleComponentChange(idx, 'quantity', e.target.value)} placeholder="Qty" />
                            <Select label={idx === 0 ? "Unit" : ""} value={c.unit} onChange={(e) => handleComponentChange(idx, 'unit', e.target.value)} options={[
                                { value: 'pcs', label: 'Pieces' }, { value: 'kg', label: 'Kg' }, { value: 'm', label: 'Meters' }
                            ]} />
                            <button onClick={() => removeComponent(idx)} style={{ padding: '10px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', cursor: 'pointer' }}>🗑</button>
                        </div>
                    ))}
                </div>
            </Modal>
        </MainLayout>
    );
}

function BOMTreePreview() {
    return (
        <div className="chart-container" style={{ marginBottom: '24px' }}>
            <div className="chart-header">
                <h3 className="chart-title">BOM Structure Preview: Steel Brackets A1</h3>
            </div>
            <div style={{ padding: '16px', fontFamily: 'monospace', fontSize: '14px' }}>
                <div style={{ color: 'var(--color-primary)', fontWeight: 600 }}>📦 Steel Brackets A1 (FG-001)</div>
                <div style={{ marginLeft: '24px', marginTop: '8px' }}>
                    <div>├── 🔩 Steel Sheet 2mm (RM-001) × 2 units</div>
                    <div>├── 🔩 Bolts M6 (RM-002) × 4 units</div>
                    <div>├── 🔩 Nuts M6 (RM-003) × 4 units</div>
                    <div>├── 🔩 Washers M6 (RM-004) × 8 units</div>
                    <div>└── 🏷️ Label Sticker (PK-001) × 1 unit</div>
                </div>
            </div>
        </div>
    );
}
