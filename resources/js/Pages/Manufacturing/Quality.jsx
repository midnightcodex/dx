import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const ncrData = [
    { id: 1, ncrNumber: 'NCR-2026-015', product: 'Steel Brackets A1', batch: 'B-2026-0234', defectType: 'Dimensional', severity: 'Major', status: 'Open', reportedDate: '2026-02-02' },
    { id: 2, ncrNumber: 'NCR-2026-014', product: 'Aluminum Plates B2', batch: 'B-2026-0228', defectType: 'Surface', severity: 'Critical', status: 'Under Investigation', reportedDate: '2026-02-01' },
    { id: 3, ncrNumber: 'NCR-2026-013', product: 'Electronic PCB E1', batch: 'B-2026-0220', defectType: 'Functional', severity: 'Minor', status: 'Closed', reportedDate: '2026-01-28' },
];

const inspectionData = [
    { id: 1, inspectionId: 'INS-2026-042', workOrder: 'WO-2026-001', product: 'Steel Brackets A1', sampleSize: 50, passed: 48, failed: 2, result: 'Passed', date: '2026-02-02' },
    { id: 2, inspectionId: 'INS-2026-041', workOrder: 'WO-2026-004', product: 'Plastic Housings', sampleSize: 30, passed: 30, failed: 0, result: 'Passed', date: '2026-02-01' },
];

const ncrColumns = [
    { header: 'NCR #', accessor: 'ncrNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Batch', accessor: 'batch' },
    { header: 'Defect Type', accessor: 'defectType' },
    {
        header: 'Severity', accessor: 'severity', render: (val) => {
            const colors = { Critical: 'var(--color-danger)', Major: 'var(--color-warning)', Minor: 'var(--color-gray-500)' };
            return <span style={{ color: colors[val], fontWeight: 600 }}>● {val}</span>;
        }
    },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Open: { bg: '#FEE2E2', color: '#DC2626' }, 'Under Investigation': { bg: '#FEF3C7', color: '#D97706' }, Closed: { bg: '#D1FAE5', color: '#059669' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
    { header: 'Date', accessor: 'reportedDate' },
];

const inspectionColumns = [
    { header: 'Inspection ID', accessor: 'inspectionId' },
    { header: 'Work Order', accessor: 'workOrder' },
    { header: 'Product', accessor: 'product' },
    { header: 'Sample Size', accessor: 'sampleSize' },
    {
        header: 'Pass Rate', accessor: 'passed', render: (val, row) => {
            const rate = Math.round((val / row.sampleSize) * 100);
            return <span style={{ fontWeight: 500 }}>{rate}% ({val}/{row.sampleSize})</span>;
        }
    },
    {
        header: 'Result', accessor: 'result', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Passed' ? '#D1FAE5' : '#FEE2E2', color: val === 'Passed' ? '#059669' : '#DC2626' }}>{val}</span>
        )
    },
    { header: 'Date', accessor: 'date' },
];

export default function Quality() {
    const [showNcrModal, setShowNcrModal] = useState(false);
    const [showInspModal, setShowInspModal] = useState(false);
    const [ncrForm, setNcrForm] = useState({ product: '', batch: '', defectType: '', severity: '', description: '' });
    const [inspForm, setInspForm] = useState({ workOrder: '', sampleSize: '', passed: '', notes: '' });

    const handleNcrChange = (e) => setNcrForm({ ...ncrForm, [e.target.name]: e.target.value });
    const handleInspChange = (e) => setInspForm({ ...inspForm, [e.target.name]: e.target.value });

    const openNCRs = ncrData.filter(n => n.status !== 'Closed');

    const tabs = [
        { label: 'NCR Log', badge: openNCRs.length, content: <DataTable columns={ncrColumns} data={ncrData} title="Non-Conformance Reports" /> },
        { label: 'Inspections', content: <DataTable columns={inspectionColumns} data={inspectionData} title="Quality Inspections" /> },
        { label: 'Metrics', content: <QualityMetrics /> },
    ];

    return (
        <MainLayout title="Quality Control" subtitle="Monitor and manage quality standards">
            <div className="stats-grid">
                <StatsCard icon="✅" value="98.5%" label="First Pass Yield" trend="0.5% improvement" trendDirection="up" variant="success" />
                <StatsCard icon="🔍" value={42} label="Inspections This Month" variant="primary" />
                <StatsCard icon="⚠️" value={openNCRs.length} label="Open NCRs" trend="2 critical" trendDirection="down" variant="danger" />
                <StatsCard icon="📊" value="0.8%" label="Defect Rate" trend="Below target" trendDirection="down" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowNcrModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-danger)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ⚠️ Log NCR
                </button>
                <button onClick={() => setShowInspModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔍 Log Inspection
                </button>
            </div>

            <Tabs tabs={tabs} />

            {/* NCR Modal */}
            <Modal isOpen={showNcrModal} onClose={() => setShowNcrModal(false)} title="Log Non-Conformance Report" size="lg" footer={
                <>
                    <button onClick={() => setShowNcrModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={() => { alert('NCR Logged!'); setShowNcrModal(false); }} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-danger)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Log NCR</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Product" name="product" value={ncrForm.product} onChange={handleNcrChange} required options={[
                        { value: 'steel-brackets', label: 'Steel Brackets A1' },
                        { value: 'aluminum-plates', label: 'Aluminum Plates B2' },
                    ]} />
                    <Input label="Batch Number" name="batch" value={ncrForm.batch} onChange={handleNcrChange} required placeholder="e.g. B-2026-0234" />
                    <Select label="Defect Type" name="defectType" value={ncrForm.defectType} onChange={handleNcrChange} required options={[
                        { value: 'dimensional', label: 'Dimensional' },
                        { value: 'surface', label: 'Surface Finish' },
                        { value: 'functional', label: 'Functional' },
                        { value: 'material', label: 'Material' },
                    ]} />
                    <Select label="Severity" name="severity" value={ncrForm.severity} onChange={handleNcrChange} required options={[
                        { value: 'critical', label: '🔴 Critical' },
                        { value: 'major', label: '🟠 Major' },
                        { value: 'minor', label: '⚪ Minor' },
                    ]} />
                </div>
                <div style={{ marginTop: '16px' }}>
                    <label style={{ display: 'block', fontSize: '14px', fontWeight: 500, marginBottom: '8px' }}>Description</label>
                    <textarea name="description" value={ncrForm.description} onChange={handleNcrChange} rows={3} placeholder="Describe the non-conformance..." style={{ width: '100%', padding: '10px 14px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
            </Modal>

            {/* Inspection Modal */}
            <Modal isOpen={showInspModal} onClose={() => setShowInspModal(false)} title="Log Quality Inspection" size="md" footer={
                <>
                    <button onClick={() => setShowInspModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={() => { alert('Inspection Logged!'); setShowInspModal(false); }} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Log Inspection</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Work Order" name="workOrder" value={inspForm.workOrder} onChange={handleInspChange} required options={[
                        { value: 'wo-001', label: 'WO-2026-001 - Steel Brackets' },
                        { value: 'wo-004', label: 'WO-2026-004 - Plastic Housings' },
                    ]} />
                    <Input label="Sample Size" type="number" name="sampleSize" value={inspForm.sampleSize} onChange={handleInspChange} required placeholder="e.g. 50" />
                    <Input label="Passed" type="number" name="passed" value={inspForm.passed} onChange={handleInspChange} required placeholder="Units passed" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function QualityMetrics() {
    const metrics = [
        { name: 'First Pass Yield', value: '98.5%', target: '98%', status: 'Above' },
        { name: 'Defect Rate', value: '0.8%', target: '1%', status: 'Above' },
        { name: 'Customer Returns', value: '0.2%', target: '0.5%', status: 'Above' },
        { name: 'NCR Resolution Time', value: '3.2 days', target: '5 days', status: 'Above' },
    ];

    return (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px', padding: '16px' }}>
            {metrics.map((m, idx) => (
                <div key={idx} style={{ padding: '20px', borderRadius: '12px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)' }}>
                    <div style={{ fontSize: '14px', color: 'var(--color-gray-500)', marginBottom: '8px' }}>{m.name}</div>
                    <div style={{ fontSize: '28px', fontWeight: 700, color: 'var(--color-gray-900)', marginBottom: '8px' }}>{m.value}</div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Target: {m.target}</span>
                        <span style={{ padding: '2px 8px', borderRadius: '8px', fontSize: '11px', fontWeight: 500, backgroundColor: '#D1FAE5', color: '#059669' }}>✓ {m.status}</span>
                    </div>
                </div>
            ))}
        </div>
    );
}
