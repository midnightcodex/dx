import React from 'react';
import { Link } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';

export default function GRN({ grns, stats, filters }) {

    const grnColumns = [
        { header: 'GRN Number', accessor: 'grn_number', render: (val, row) => <Link href={route('procurement.grn.show', row.id)} className="text-blue-600 hover:underline">{val}</Link> },
        { header: 'PO Number', accessor: 'purchase_order.po_number' },
        { header: 'Vendor', accessor: 'vendor.name' },
        { header: 'Receipt Date', accessor: 'receipt_date' },
        { header: 'Warehouse', accessor: 'warehouse.name' },
        {
            header: 'Status', accessor: 'status', render: (val) => {
                const colors = {
                    DRAFT: { bg: '#F3F4F6', color: '#6B7280' },
                    INSPECTING: { bg: '#FEF3C7', color: '#D97706' },
                    APPROVED: { bg: '#DBEAFE', color: '#1D4ED8' },
                    POSTED: { bg: '#D1FAE5', color: '#059669' },
                    CANCELLED: { bg: '#FEE2E2', color: '#991B1B' }
                };
                const style = colors[val] || colors.DRAFT;
                return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
            }
        },
    ];

    return (
        <MainLayout title="Goods Receipt Notes" subtitle="Track incoming material receipts">
            <div className="stats-grid">
                <StatsCard icon="📥" value={stats.total} label="Total GRNs" variant="primary" />
                <StatsCard icon="📦" value={stats.posted} label="Posted/Rec'd" variant="success" />
                <StatsCard icon="🔍" value={stats.pending} label="Pending QC/Post" variant="warning" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <Link href={route('procurement.grn.create')} as="button" style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px', textDecoration: 'none' }}>
                    ➕ Create GRN
                </Link>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <DataTable columns={grnColumns} data={grns.data} pagination={grns} title="Good Receipt Notes" />
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
