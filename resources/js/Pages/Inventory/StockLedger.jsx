import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const ledgerData = [
    { id: 1, date: '2026-02-02', itemCode: 'RM-001', itemName: 'Raw Steel Sheet', type: 'Receipt', reference: 'GRN-2026-042', qtyIn: 500, qtyOut: 0, balance: 5000, warehouse: 'Main' },
    { id: 2, date: '2026-02-02', itemCode: 'FG-001', itemName: 'Steel Brackets A1', type: 'Issue', reference: 'DO-2026-018', qtyIn: 0, qtyOut: 100, balance: 1200, warehouse: 'Finished Goods' },
    { id: 3, date: '2026-02-01', itemCode: 'RM-002', itemName: 'Aluminum Sheets', type: 'Receipt', reference: 'GRN-2026-041', qtyIn: 200, qtyOut: 0, balance: 2500, warehouse: 'Main' },
    { id: 4, date: '2026-02-01', itemCode: 'RM-003', itemName: 'Copper Wire 2mm', type: 'Issue', reference: 'WO-2026-004', qtyIn: 0, qtyOut: 50, balance: 150, warehouse: 'Production' },
];

const ledgerColumns = [
    { header: 'Date', accessor: 'date' },
    { header: 'Item Code', accessor: 'itemCode' },
    { header: 'Item Name', accessor: 'itemName' },
    {
        header: 'Type', accessor: 'type', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Receipt' ? '#D1FAE5' : '#FEE2E2', color: val === 'Receipt' ? '#059669' : '#DC2626' }}>{val}</span>
        )
    },
    { header: 'Reference', accessor: 'reference' },
    { header: 'Qty In', accessor: 'qtyIn', render: (val) => val > 0 ? <span style={{ color: 'var(--color-success)', fontWeight: 500 }}>+{val}</span> : '-' },
    { header: 'Qty Out', accessor: 'qtyOut', render: (val) => val > 0 ? <span style={{ color: 'var(--color-danger)', fontWeight: 500 }}>-{val}</span> : '-' },
    { header: 'Balance', accessor: 'balance', render: (val) => <span style={{ fontWeight: 600 }}>{val.toLocaleString()}</span> },
    { header: 'Warehouse', accessor: 'warehouse' },
];

export default function StockLedger() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ item: '', type: '', quantity: '', reference: '', warehouse: '', notes: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Stock Entry Created!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ item: '', type: '', quantity: '', reference: '', warehouse: '', notes: '' });
    };

    const totalIn = ledgerData.reduce((sum, l) => sum + l.qtyIn, 0);
    const totalOut = ledgerData.reduce((sum, l) => sum + l.qtyOut, 0);

    return (
        <MainLayout title="Stock Ledger" subtitle="Track all inventory movements">
            <div className="stats-grid">
                <StatsCard icon="📥" value={totalIn.toLocaleString()} label="Total Qty Received" trend="This month" variant="success" />
                <StatsCard icon="📤" value={totalOut.toLocaleString()} label="Total Qty Issued" trend="This month" variant="danger" />
                <StatsCard icon="📊" value={ledgerData.length} label="Transactions" trend="Last 7 days" variant="primary" />
                <StatsCard icon="📋" value={5} label="Unique Items Moved" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Stock Entry
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export Ledger
                </button>
            </div>

            <FilterBar />
            <DataTable columns={ledgerColumns} data={ledgerData} title="Stock Ledger" actions={false} />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Stock Entry" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Entry</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Item" name="item" value={formData.item} onChange={handleInputChange} required options={[
                        { value: 'rm-001', label: 'RM-001 - Raw Steel Sheet' },
                        { value: 'rm-002', label: 'RM-002 - Aluminum Sheets' },
                        { value: 'fg-001', label: 'FG-001 - Steel Brackets A1' },
                    ]} />
                    <Select label="Entry Type" name="type" value={formData.type} onChange={handleInputChange} required options={[
                        { value: 'receipt', label: '📥 Receipt' },
                        { value: 'issue', label: '📤 Issue' },
                        { value: 'adjustment', label: '🔄 Adjustment' },
                    ]} />
                    <Input label="Quantity" type="number" name="quantity" value={formData.quantity} onChange={handleInputChange} required placeholder="Enter quantity" />
                    <Input label="Reference" name="reference" value={formData.reference} onChange={handleInputChange} placeholder="e.g. GRN-2026-043" />
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

function FilterBar() {
    return (
        <div style={{ display: 'flex', gap: '12px', marginBottom: '24px', flexWrap: 'wrap' }}>
            <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontSize: '14px' }}>
                <option>All Items</option>
                <option>RM-001 - Raw Steel Sheet</option>
                <option>RM-002 - Aluminum Sheets</option>
            </select>
            <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontSize: '14px' }}>
                <option>All Types</option>
                <option>Receipt</option>
                <option>Issue</option>
            </select>
            <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontSize: '14px' }}>
                <option>All Warehouses</option>
                <option>Main</option>
                <option>Production</option>
            </select>
            <input type="date" defaultValue="2026-01-01" style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
            <input type="date" defaultValue="2026-02-02" style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
        </div>
    );
}
