import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const grnData = [
    { id: 1, grnNumber: 'GRN-2026-042', poNumber: 'PO-2026-040', vendor: 'Steel Corp Ltd', receiptDate: '2026-02-02', items: 1, status: 'Completed', qcStatus: 'Passed' },
    { id: 2, grnNumber: 'GRN-2026-041', poNumber: 'PO-2026-041', vendor: 'Aluminum Traders', receiptDate: '2026-02-01', items: 2, status: 'Pending QC', qcStatus: 'Pending' },
    { id: 3, grnNumber: 'GRN-2026-040', poNumber: 'PO-2026-039', vendor: 'Packaging Solutions', receiptDate: '2026-01-30', items: 5, status: 'Completed', qcStatus: 'Passed' },
];

const grnColumns = [
    { header: 'GRN Number', accessor: 'grnNumber' },
    { header: 'PO Number', accessor: 'poNumber' },
    { header: 'Vendor', accessor: 'vendor' },
    { header: 'Receipt Date', accessor: 'receiptDate' },
    { header: 'Items', accessor: 'items' },
    {
        header: 'QC Status', accessor: 'qcStatus', render: (val) => {
            const colors = { Passed: '#059669', Pending: '#D97706', Failed: '#DC2626' };
            return <span style={{ color: colors[val], fontWeight: 500 }}>● {val}</span>;
        }
    },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Completed: { bg: '#D1FAE5', color: '#059669' }, 'Pending QC': { bg: '#FEF3C7', color: '#D97706' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function GRN() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ poNumber: '', warehouse: '', receivedBy: '' });
    const [items, setItems] = useState([{ item: '', ordered: '', received: '' }]);

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleItemChange = (idx, field, value) => { const updated = [...items]; updated[idx][field] = value; setItems(updated); };

    const handleSubmit = () => {
        alert('GRN Created!\n' + JSON.stringify({ ...formData, items }, null, 2));
        setShowModal(false);
        setFormData({ poNumber: '', warehouse: '', receivedBy: '' });
        setItems([{ item: '', ordered: '', received: '' }]);
    };

    const pendingQC = grnData.filter(g => g.qcStatus === 'Pending');

    return (
        <MainLayout title="Goods Receipt Notes" subtitle="Track incoming material receipts">
            <div className="stats-grid">
                <StatsCard icon="📥" value={grnData.length} label="Total GRNs" trend="This month" variant="primary" />
                <StatsCard icon="📦" value={1} label="Received Today" variant="success" />
                <StatsCard icon="🔍" value={pendingQC.length} label="Pending QC" variant="warning" />
                <StatsCard icon="✅" value="98%" label="QC Pass Rate" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create GRN
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <GRNDetailPreview />
            <DataTable columns={grnColumns} data={grnData} title="GRN List" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Goods Receipt Note" size="xl" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create GRN</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginBottom: '24px' }}>
                    <Select label="Purchase Order" name="poNumber" value={formData.poNumber} onChange={handleInputChange} required options={[
                        { value: 'po-041', label: 'PO-2026-041 - Aluminum Traders' },
                        { value: 'po-042', label: 'PO-2026-042 - Steel Corp Ltd' },
                    ]} />
                    <Select label="Warehouse" name="warehouse" value={formData.warehouse} onChange={handleInputChange} required options={[
                        { value: 'main', label: 'Main Warehouse' },
                        { value: 'production', label: 'Production Floor' },
                    ]} />
                    <Input label="Received By" name="receivedBy" value={formData.receivedBy} onChange={handleInputChange} placeholder="Your name" />
                </div>
                <div>
                    <h4 style={{ margin: '0 0 12px 0' }}>Items Received</h4>
                    {items.map((item, idx) => (
                        <div key={idx} style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr', gap: '12px', marginBottom: '12px', alignItems: 'end' }}>
                            <Input label={idx === 0 ? "Item" : ""} value={item.item} onChange={(e) => handleItemChange(idx, 'item', e.target.value)} placeholder="Item name" />
                            <Input label={idx === 0 ? "Ordered" : ""} type="number" value={item.ordered} onChange={(e) => handleItemChange(idx, 'ordered', e.target.value)} placeholder="Ordered qty" />
                            <Input label={idx === 0 ? "Received" : ""} type="number" value={item.received} onChange={(e) => handleItemChange(idx, 'received', e.target.value)} placeholder="Received qty" />
                        </div>
                    ))}
                </div>
            </Modal>
        </MainLayout>
    );
}

function GRNDetailPreview() {
    return (
        <div className="chart-container" style={{ marginBottom: '24px' }}>
            <div className="chart-header">
                <h3 className="chart-title">GRN-2026-041 - Pending Quality Check</h3>
                <div style={{ display: 'flex', gap: '8px' }}>
                    <button style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-success)', backgroundColor: 'transparent', color: 'var(--color-success)', fontWeight: 500, cursor: 'pointer' }}>✓ Approve</button>
                    <button style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-danger)', backgroundColor: 'transparent', color: 'var(--color-danger)', fontWeight: 500, cursor: 'pointer' }}>✗ Reject</button>
                </div>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px', marginBottom: '16px' }}>
                <div style={{ padding: '12px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                    <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>PO Reference</div>
                    <div style={{ fontWeight: 600 }}>PO-2026-041</div>
                </div>
                <div style={{ padding: '12px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                    <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Vendor</div>
                    <div style={{ fontWeight: 600 }}>Aluminum Traders</div>
                </div>
                <div style={{ padding: '12px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                    <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Receipt Date</div>
                    <div style={{ fontWeight: 600 }}>2026-02-01</div>
                </div>
            </div>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Item</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Ordered</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Received</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>QC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                        <td style={{ padding: '12px' }}>Aluminum Sheets 3mm</td>
                        <td style={{ padding: '12px' }}>200 sheets</td>
                        <td style={{ padding: '12px' }}>200 sheets</td>
                        <td style={{ padding: '12px' }}><span style={{ color: '#D97706' }}>● Pending</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    );
}
