import React from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';

export default function PurchaseOrders({ purchaseOrders, stats, filters }) {
    const { links } = purchaseOrders;

    const poColumns = [
        { header: 'PO Number', accessor: 'po_number', render: (val, row) => <Link href={route('procurement.purchase-orders.show', row.id)} className="text-blue-600 hover:underline">{val}</Link> },
        { header: 'Vendor', accessor: 'vendor.name' },
        { header: 'Order Date', accessor: 'order_date' },
        { header: 'Expected', accessor: 'expected_date' },
        { header: 'Amount', accessor: 'total_amount', render: (val) => `₹${Number(val).toLocaleString()}` },
        {
            header: 'Status', accessor: 'status', render: (val) => {
                const colors = {
                    DRAFT: { bg: '#F3F4F6', color: '#6B7280' },
                    SUBMITTED: { bg: '#FEF3C7', color: '#D97706' },
                    APPROVED: { bg: '#DBEAFE', color: '#1D4ED8' },
                    PARTIAL: { bg: '#EDE9FE', color: '#7C3AED' },
                    COMPLETED: { bg: '#D1FAE5', color: '#059669' },
                    CANCELLED: { bg: '#FEE2E2', color: '#991B1B' }
                };
                const style = colors[val] || colors.DRAFT;
                return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
            }
        },
    ];

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={poColumns} data={purchaseOrders.data} pagination={purchaseOrders} title="All Purchase Orders" /> },
        { label: 'Pending', badge: stats.pending, content: <DataTable columns={poColumns} data={purchaseOrders.data.filter(p => ['DRAFT', 'SUBMITTED'].includes(p.status))} title="Pending Approval" /> },
        { label: 'Approved', badge: stats.approved, content: <DataTable columns={poColumns} data={purchaseOrders.data.filter(p => p.status === 'APPROVED')} title="Approved POs" /> },
    ];

    return (
        <MainLayout title="Purchase Orders" subtitle="Manage procurement orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={stats.total} label="Total POs" variant="primary" />
                <StatsCard icon="💰" value={`₹${(Number(stats.totalValue) / 100000).toFixed(2)}L`} label="Total Value" variant="success" />
                <StatsCard icon="⏳" value={stats.pending} label="Pending Action" variant="warning" />
                <StatsCard icon="✅" value={stats.completed} label="Completed" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <Link href={route('procurement.purchase-orders.create')} as="button" style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px', textDecoration: 'none' }}>
                    ➕ Create PO
                </Link>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export
                </button>
            </div>

            <Tabs tabs={tabs} />
        </MainLayout>
    );
}
