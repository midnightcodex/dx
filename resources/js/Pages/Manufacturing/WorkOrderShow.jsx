import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';

export default function WorkOrderShow({ workOrder }) {
    const wo = workOrder;

    const statusColors = {
        'PLANNED': { bg: '#E0E7FF', color: '#4338CA', label: 'Planned' },
        'RELEASED': { bg: '#FEF3C7', color: '#D97706', label: 'Released' },
        'IN_PROGRESS': { bg: '#DBEAFE', color: '#1D4ED8', label: 'In Progress' },
        'COMPLETED': { bg: '#D1FAE5', color: '#059669', label: 'Completed' },
        'CANCELLED': { bg: '#FEE2E2', color: '#DC2626', label: 'Cancelled' },
    };

    const status = statusColors[wo.status] || { bg: '#F3F4F6', color: '#6B7280', label: wo.status };

    const handleRelease = () => router.post(`/manufacturing/work-orders/${wo.id}/release`);
    const handleStart = () => router.post(`/manufacturing/work-orders/${wo.id}/start`);

    return (
        <MainLayout title={`Work Order: ${wo.wo_number}`} subtitle={wo.item?.name}>
            <Head title={`WO - ${wo.wo_number}`} />

            {/* Header Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <Link
                    href="/manufacturing/work-orders"
                    style={{ padding: '10px 16px', borderRadius: '6px', border: '1px solid var(--color-gray-200)', backgroundColor: 'white', textDecoration: 'none', color: 'var(--color-gray-700)', fontSize: '14px' }}
                >
                    ← Back to List
                </Link>

                {wo.status === 'PLANNED' && (
                    <button onClick={handleRelease} style={{ padding: '10px 16px', borderRadius: '6px', border: 'none', backgroundColor: '#10B981', color: 'white', cursor: 'pointer', fontSize: '14px' }}>
                        🚀 Release for Production
                    </button>
                )}
                {wo.status === 'RELEASED' && (
                    <button onClick={handleStart} style={{ padding: '10px 16px', borderRadius: '6px', border: 'none', backgroundColor: '#3B82F6', color: 'white', cursor: 'pointer', fontSize: '14px' }}>
                        ▶️ Start Production
                    </button>
                )}
            </div>

            {/* Status Badge */}
            <div style={{ marginBottom: '24px' }}>
                <span style={{
                    padding: '8px 16px',
                    borderRadius: '20px',
                    fontSize: '14px',
                    fontWeight: 600,
                    backgroundColor: status.bg,
                    color: status.color
                }}>
                    {status.label}
                </span>
            </div>

            {/* Main Info Card */}
            <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '24px', marginBottom: '24px' }}>
                <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '24px', boxShadow: 'var(--shadow-sm)' }}>
                    <h3 style={{ marginBottom: '16px', color: 'var(--color-gray-900)' }}>Work Order Details</h3>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                        <InfoRow label="WO Number" value={wo.wo_number} />
                        <InfoRow label="Item" value={wo.item?.name} />
                        <InfoRow label="Planned Qty" value={wo.planned_quantity?.toLocaleString()} />
                        <InfoRow label="Completed Qty" value={wo.completed_quantity?.toLocaleString() || '0'} />
                        <InfoRow label="Scheduled Start" value={wo.scheduled_start_date} />
                        <InfoRow label="Scheduled End" value={wo.scheduled_end_date || 'TBD'} />
                        <InfoRow label="Source Warehouse" value={wo.source_warehouse?.name} />
                        <InfoRow label="Target Warehouse" value={wo.target_warehouse?.name} />
                    </div>
                </div>

                <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '24px', boxShadow: 'var(--shadow-sm)' }}>
                    <h3 style={{ marginBottom: '16px', color: 'var(--color-gray-900)' }}>Progress</h3>
                    <div style={{ textAlign: 'center' }}>
                        <div style={{ fontSize: '48px', fontWeight: 700, color: 'var(--color-primary)' }}>
                            {wo.planned_quantity > 0 ? Math.round((wo.completed_quantity || 0) / wo.planned_quantity * 100) : 0}%
                        </div>
                        <div style={{ color: 'var(--color-gray-500)', marginTop: '8px' }}>
                            {wo.completed_quantity || 0} of {wo.planned_quantity} complete
                        </div>
                        <div style={{
                            marginTop: '16px',
                            height: '12px',
                            backgroundColor: 'var(--color-gray-200)',
                            borderRadius: '6px',
                            overflow: 'hidden'
                        }}>
                            <div style={{
                                width: `${wo.planned_quantity > 0 ? (wo.completed_quantity || 0) / wo.planned_quantity * 100 : 0}%`,
                                height: '100%',
                                backgroundColor: 'var(--color-primary)',
                                transition: 'width 0.3s ease'
                            }} />
                        </div>
                    </div>
                </div>
            </div>

            {/* BOM Materials */}
            {wo.bom?.lines && wo.bom.lines.length > 0 && (
                <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '24px', boxShadow: 'var(--shadow-sm)', marginBottom: '24px' }}>
                    <h3 style={{ marginBottom: '16px', color: 'var(--color-gray-900)' }}>Required Materials (from BOM)</h3>
                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                        <thead>
                            <tr style={{ borderBottom: '2px solid var(--color-gray-200)' }}>
                                <th style={{ textAlign: 'left', padding: '12px 8px', color: 'var(--color-gray-500)', fontSize: '12px', textTransform: 'uppercase' }}>Item</th>
                                <th style={{ textAlign: 'right', padding: '12px 8px', color: 'var(--color-gray-500)', fontSize: '12px', textTransform: 'uppercase' }}>Qty per Unit</th>
                                <th style={{ textAlign: 'right', padding: '12px 8px', color: 'var(--color-gray-500)', fontSize: '12px', textTransform: 'uppercase' }}>Total Required</th>
                            </tr>
                        </thead>
                        <tbody>
                            {wo.bom.lines.map((line, idx) => (
                                <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                                    <td style={{ padding: '12px 8px' }}>{line.item?.name || 'N/A'}</td>
                                    <td style={{ padding: '12px 8px', textAlign: 'right' }}>{line.quantity}</td>
                                    <td style={{ padding: '12px 8px', textAlign: 'right', fontWeight: 500 }}>
                                        {(line.quantity * wo.planned_quantity).toLocaleString()}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}

            {/* Notes */}
            {wo.notes && (
                <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '24px', boxShadow: 'var(--shadow-sm)' }}>
                    <h3 style={{ marginBottom: '16px', color: 'var(--color-gray-900)' }}>Notes</h3>
                    <p style={{ color: 'var(--color-gray-600)', whiteSpace: 'pre-wrap' }}>{wo.notes}</p>
                </div>
            )}
        </MainLayout>
    );
}

function InfoRow({ label, value }) {
    return (
        <div>
            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)', marginBottom: '4px' }}>{label}</div>
            <div style={{ fontSize: '14px', color: 'var(--color-gray-900)', fontWeight: 500 }}>{value || '-'}</div>
        </div>
    );
}
