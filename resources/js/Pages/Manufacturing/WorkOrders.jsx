import React, { useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const columns = [
    { header: 'WO Number', accessor: 'woNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Quantity', accessor: 'quantity', render: (val) => val?.toLocaleString() },
    {
        header: 'Progress',
        accessor: 'completedQuantity',
        render: (val, row) => {
            const pct = row.quantity > 0 ? Math.round((val / row.quantity) * 100) : 0;
            return (
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    <div style={{ width: '60px', height: '6px', backgroundColor: 'var(--color-gray-200)', borderRadius: '3px', overflow: 'hidden' }}>
                        <div style={{ width: `${pct}%`, height: '100%', backgroundColor: pct === 100 ? 'var(--color-success)' : 'var(--color-primary)' }} />
                    </div>
                    <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{pct}%</span>
                </div>
            );
        }
    },
    { header: 'Scheduled', accessor: 'scheduledStart' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val, row) => {
            const colors = {
                'Planned': { bg: '#E0E7FF', color: '#4338CA' },
                'Released': { bg: '#FEF3C7', color: '#D97706' },
                'In Progress': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Completed': { bg: '#D1FAE5', color: '#059669' },
                'Cancelled': { bg: '#FEE2E2', color: '#DC2626' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
    {
        header: 'Actions',
        accessor: 'id',
        render: (id, row) => (
            <div style={{ display: 'flex', gap: '8px' }}>
                {row.rawStatus === 'PLANNED' && (
                    <button
                        onClick={() => router.post(`/manufacturing/work-orders/${id}/release`)}
                        style={{ padding: '4px 10px', fontSize: '12px', backgroundColor: '#10B981', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                    >
                        Release
                    </button>
                )}
                {row.rawStatus === 'RELEASED' && (
                    <button
                        onClick={() => router.post(`/manufacturing/work-orders/${id}/start`)}
                        style={{ padding: '4px 10px', fontSize: '12px', backgroundColor: '#3B82F6', color: 'white', border: 'none', borderRadius: '4px', cursor: 'pointer' }}
                    >
                        Start
                    </button>
                )}
                <Link href={`/manufacturing/work-orders/${id}`} style={{ padding: '4px 10px', fontSize: '12px', backgroundColor: '#6B7280', color: 'white', borderRadius: '4px', textDecoration: 'none' }}>
                    View
                </Link>
            </div>
        )
    }
];

export default function WorkOrders({ workOrders, filters }) {
    const allOrders = workOrders?.data || [];

    // Stats
    const planned = allOrders.filter(wo => wo.rawStatus === 'PLANNED');
    const released = allOrders.filter(wo => wo.rawStatus === 'RELEASED');
    const inProgress = allOrders.filter(wo => wo.rawStatus === 'IN_PROGRESS');
    const completed = allOrders.filter(wo => wo.rawStatus === 'COMPLETED');

    const tabs = [
        { label: 'All Orders', content: <DataTable columns={columns} data={allOrders} actions={false} /> },
        { label: 'Planned', badge: planned.length, content: <DataTable columns={columns} data={planned} actions={false} /> },
        { label: 'Released', badge: released.length, content: <DataTable columns={columns} data={released} actions={false} /> },
        { label: 'In Progress', badge: inProgress.length, content: <DataTable columns={columns} data={inProgress} actions={false} /> },
        { label: 'Completed', content: <DataTable columns={columns} data={completed} actions={false} /> },
    ];

    return (
        <MainLayout title="Work Orders" subtitle="Manage production work orders">
            <div className="stats-grid">
                <StatsCard icon="📋" value={allOrders.length} label="Total Work Orders" variant="primary" />
                <StatsCard icon="📝" value={planned.length} label="Planned" variant="default" />
                <StatsCard icon="🔄" value={inProgress.length + released.length} label="Active" variant="warning" />
                <StatsCard icon="✅" value={completed.length} label="Completed" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <Link
                    href="/manufacturing/work-orders/create"
                    style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px', textDecoration: 'none' }}
                >
                    ➕ Create Work Order
                </Link>
            </div>

            <Tabs tabs={tabs} />

            {allOrders.length === 0 && (
                <div style={{ textAlign: 'center', padding: '48px', color: 'var(--color-gray-500)' }}>
                    <span style={{ fontSize: '48px', display: 'block', marginBottom: '16px' }}>📝</span>
                    <p>No work orders yet. Create your first one!</p>
                </div>
            )}
        </MainLayout>
    );
}

