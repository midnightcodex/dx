import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const batchData = [
    { id: 1, batchNo: 'B-2026-0234', item: 'Steel Brackets A1', itemCode: 'FG-001', quantity: 500, manufacturedDate: '2026-02-01', expiryDate: '-', status: 'Active', warehouse: 'Finished Goods' },
    { id: 2, batchNo: 'B-2026-0228', item: 'Aluminum Plates B2', itemCode: 'FG-002', quantity: 300, manufacturedDate: '2026-01-28', expiryDate: '-', status: 'Quality Hold', warehouse: 'Finished Goods' },
    { id: 3, batchNo: 'B-2026-0220', item: 'Electronic PCB E1', itemCode: 'FG-005', quantity: 200, manufacturedDate: '2026-01-25', expiryDate: '-', status: 'Active', warehouse: 'Finished Goods' },
    { id: 4, batchNo: 'B-2026-0210', item: 'Adhesive Type C', itemCode: 'RM-010', quantity: 50, manufacturedDate: '2026-01-15', expiryDate: '2026-07-15', status: 'Active', warehouse: 'Main' },
    { id: 5, batchNo: 'B-2025-1205', item: 'Lubricant Oil', itemCode: 'RM-015', quantity: 20, manufacturedDate: '2025-12-05', expiryDate: '2026-03-05', status: 'Expiring Soon', warehouse: 'Production' },
];

const batchColumns = [
    { header: 'Batch No', accessor: 'batchNo' },
    { header: 'Item', accessor: 'item' },
    { header: 'Item Code', accessor: 'itemCode' },
    { header: 'Quantity', accessor: 'quantity', render: (val) => val.toLocaleString() },
    { header: 'Manufactured', accessor: 'manufacturedDate' },
    { header: 'Expiry', accessor: 'expiryDate' },
    { header: 'Warehouse', accessor: 'warehouse' },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Active: { bg: '#D1FAE5', color: '#059669' }, 'Quality Hold': { bg: '#FEF3C7', color: '#D97706' }, 'Expiring Soon': { bg: '#FEE2E2', color: '#DC2626' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function Batches() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ item: '', quantity: '', manufacturedDate: '', expiryDate: '', warehouse: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Batch Created!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ item: '', quantity: '', manufacturedDate: '', expiryDate: '', warehouse: '' });
    };

    const activeBatches = batchData.filter(b => b.status === 'Active');
    const expiringBatches = batchData.filter(b => b.status === 'Expiring Soon');
    const holdBatches = batchData.filter(b => b.status === 'Quality Hold');

    const tabs = [
        { label: 'All Batches', content: <DataTable columns={batchColumns} data={batchData} title="Batch List" /> },
        { label: 'Active', content: <DataTable columns={batchColumns} data={activeBatches} title="Active Batches" /> },
        { label: 'Expiring Soon', badge: expiringBatches.length, content: <DataTable columns={batchColumns} data={expiringBatches} title="Expiring Batches" /> },
        { label: 'On Hold', badge: holdBatches.length, content: <DataTable columns={batchColumns} data={holdBatches} title="Batches On Hold" /> },
    ];

    return (
        <MainLayout title="Batch Tracking" subtitle="Track lots and batches for traceability">
            <div className="stats-grid">
                <StatsCard icon="📦" value={batchData.length} label="Total Batches" variant="primary" />
                <StatsCard icon="✅" value={activeBatches.length} label="Active Batches" variant="success" />
                <StatsCard icon="⚠️" value={expiringBatches.length} label="Expiring Soon" trend="Within 60 days" variant="warning" />
                <StatsCard icon="🔒" value={holdBatches.length} label="On Quality Hold" variant="danger" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create Batch
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔍 Trace Batch
                </button>
            </div>

            <div className="dashboard-grid">
                <BatchTimeline />
                <TraceabilityTree />
            </div>

            <Tabs tabs={tabs} />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create New Batch" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Batch</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Item" name="item" value={formData.item} onChange={handleInputChange} required options={[
                        { value: 'fg-001', label: 'FG-001 - Steel Brackets A1' },
                        { value: 'fg-002', label: 'FG-002 - Aluminum Plates B2' },
                        { value: 'rm-010', label: 'RM-010 - Adhesive Type C' },
                    ]} />
                    <Input label="Quantity" type="number" name="quantity" value={formData.quantity} onChange={handleInputChange} required placeholder="Enter quantity" />
                    <Input label="Manufactured Date" type="date" name="manufacturedDate" value={formData.manufacturedDate} onChange={handleInputChange} required />
                    <Input label="Expiry Date (if applicable)" type="date" name="expiryDate" value={formData.expiryDate} onChange={handleInputChange} />
                    <Select label="Warehouse" name="warehouse" value={formData.warehouse} onChange={handleInputChange} required options={[
                        { value: 'main', label: 'Main Warehouse' },
                        { value: 'production', label: 'Production Floor' },
                        { value: 'fg', label: 'Finished Goods' },
                    ]} />
                </div>
            </Modal>
        </MainLayout>
    );
}

function BatchTimeline() {
    const timeline = [
        { date: '2026-02-02', event: 'Batch B-2026-0234 created', type: 'create' },
        { date: '2026-02-01', event: 'Batch B-2026-0228 put on quality hold', type: 'hold' },
        { date: '2026-01-28', event: 'Batch B-2026-0220 released', type: 'release' },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Recent Activity</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                {timeline.map((t, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                        <div style={{ width: '8px', height: '8px', borderRadius: '50%', backgroundColor: t.type === 'hold' ? 'var(--color-warning)' : t.type === 'create' ? 'var(--color-primary)' : 'var(--color-success)' }} />
                        <div style={{ flex: 1 }}>
                            <div style={{ fontSize: '14px' }}>{t.event}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{t.date}</div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function TraceabilityTree() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Traceability: B-2026-0234</h3></div>
            <div style={{ padding: '16px', fontFamily: 'monospace', fontSize: '13px' }}>
                <div style={{ color: 'var(--color-primary)', fontWeight: 600 }}>📦 Steel Brackets A1 (B-2026-0234)</div>
                <div style={{ marginLeft: '24px', marginTop: '8px' }}>
                    <div style={{ color: 'var(--color-gray-600)' }}>├── 📋 Work Order: WO-2026-001</div>
                    <div style={{ color: 'var(--color-gray-600)' }}>├── 🏭 Machine: CNC-01</div>
                    <div style={{ color: 'var(--color-gray-600)' }}>├── 👤 Operator: John Smith</div>
                    <div style={{ color: 'var(--color-gray-600)' }}>└── 📦 Raw Materials:</div>
                    <div style={{ marginLeft: '24px' }}>
                        <div>├── Steel Sheet (B-2026-0200)</div>
                        <div>└── Bolts M6 (B-2026-0185)</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
