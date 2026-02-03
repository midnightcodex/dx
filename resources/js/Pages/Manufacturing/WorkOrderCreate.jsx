import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function WorkOrderCreate({ items, boms, warehouses }) {
    const { data, setData, post, processing, errors } = useForm({
        item_id: '',
        bom_id: '',
        planned_quantity: '',
        scheduled_start_date: '',
        scheduled_end_date: '',
        source_warehouse_id: '',
        target_warehouse_id: '',
        priority: 'NORMAL',
        notes: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/manufacturing/work-orders');
    };

    // Filter BOMs based on selected item
    const filteredBoms = data.item_id
        ? boms.filter(b => b.item_id === data.item_id)
        : boms;

    return (
        <MainLayout title="Create Work Order" subtitle="Plan a new production run">
            <Head title="Create Work Order" />

            <div style={{ maxWidth: '800px' }}>
                <form onSubmit={handleSubmit}>
                    <div style={{
                        backgroundColor: 'var(--color-white)',
                        borderRadius: '12px',
                        padding: '24px',
                        boxShadow: 'var(--shadow-sm)',
                        marginBottom: '24px'
                    }}>
                        <h3 style={{ marginBottom: '20px', color: 'var(--color-gray-900)' }}>Product Information</h3>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                            <Select
                                label="Finished Good Item *"
                                value={data.item_id}
                                onChange={(e) => {
                                    setData('item_id', e.target.value);
                                    // Auto-select first matching BOM
                                    const matchingBom = boms.find(b => b.item_id === e.target.value);
                                    if (matchingBom) setData('bom_id', matchingBom.id);
                                }}
                                error={errors.item_id}
                                options={items.map(i => ({ value: i.id, label: `${i.item_code} - ${i.name}` }))}
                                placeholder="Select item to produce"
                            />
                            <Select
                                label="Bill of Materials (BOM) *"
                                value={data.bom_id}
                                onChange={(e) => setData('bom_id', e.target.value)}
                                error={errors.bom_id}
                                options={filteredBoms.map(b => ({ value: b.id, label: `${b.bom_code} (Rev ${b.revision})` }))}
                                placeholder="Select BOM version"
                            />
                        </div>
                    </div>

                    <div style={{
                        backgroundColor: 'var(--color-white)',
                        borderRadius: '12px',
                        padding: '24px',
                        boxShadow: 'var(--shadow-sm)',
                        marginBottom: '24px'
                    }}>
                        <h3 style={{ marginBottom: '20px', color: 'var(--color-gray-900)' }}>Quantity & Schedule</h3>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px' }}>
                            <Input
                                label="Planned Quantity *"
                                type="number"
                                value={data.planned_quantity}
                                onChange={(e) => setData('planned_quantity', e.target.value)}
                                error={errors.planned_quantity}
                                min="0.0001"
                                step="any"
                                placeholder="e.g. 100"
                            />
                            <Input
                                label="Scheduled Start *"
                                type="date"
                                value={data.scheduled_start_date}
                                onChange={(e) => setData('scheduled_start_date', e.target.value)}
                                error={errors.scheduled_start_date}
                            />
                            <Input
                                label="Scheduled End"
                                type="date"
                                value={data.scheduled_end_date}
                                onChange={(e) => setData('scheduled_end_date', e.target.value)}
                                error={errors.scheduled_end_date}
                            />
                        </div>
                    </div>

                    <div style={{
                        backgroundColor: 'var(--color-white)',
                        borderRadius: '12px',
                        padding: '24px',
                        boxShadow: 'var(--shadow-sm)',
                        marginBottom: '24px'
                    }}>
                        <h3 style={{ marginBottom: '20px', color: 'var(--color-gray-900)' }}>Warehouse & Priority</h3>

                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '16px' }}>
                            <Select
                                label="Source Warehouse (Raw Materials) *"
                                value={data.source_warehouse_id}
                                onChange={(e) => setData('source_warehouse_id', e.target.value)}
                                error={errors.source_warehouse_id}
                                options={warehouses.map(w => ({ value: w.id, label: `${w.code} - ${w.name}` }))}
                                placeholder="Where to pick materials"
                            />
                            <Select
                                label="Target Warehouse (Finished) *"
                                value={data.target_warehouse_id}
                                onChange={(e) => setData('target_warehouse_id', e.target.value)}
                                error={errors.target_warehouse_id}
                                options={warehouses.map(w => ({ value: w.id, label: `${w.code} - ${w.name}` }))}
                                placeholder="Where to store finished"
                            />
                            <Select
                                label="Priority"
                                value={data.priority}
                                onChange={(e) => setData('priority', e.target.value)}
                                options={[
                                    { value: 'LOW', label: '⚪ Low' },
                                    { value: 'NORMAL', label: '🔵 Normal' },
                                    { value: 'HIGH', label: '🟠 High' },
                                    { value: 'URGENT', label: '🔴 Urgent' },
                                ]}
                            />
                        </div>
                    </div>

                    <div style={{
                        backgroundColor: 'var(--color-white)',
                        borderRadius: '12px',
                        padding: '24px',
                        boxShadow: 'var(--shadow-sm)',
                        marginBottom: '24px'
                    }}>
                        <h3 style={{ marginBottom: '20px', color: 'var(--color-gray-900)' }}>Notes</h3>
                        <textarea
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Any special instructions or notes..."
                            style={{
                                width: '100%',
                                minHeight: '100px',
                                padding: '12px',
                                borderRadius: '8px',
                                border: '1px solid var(--color-gray-200)',
                                fontSize: '14px',
                                resize: 'vertical'
                            }}
                        />
                    </div>

                    <div style={{ display: 'flex', gap: '12px', justifyContent: 'flex-end' }}>
                        <Link
                            href="/manufacturing/work-orders"
                            style={{
                                padding: '12px 24px',
                                borderRadius: '8px',
                                border: '1px solid var(--color-gray-200)',
                                backgroundColor: 'var(--color-white)',
                                cursor: 'pointer',
                                fontSize: '14px',
                                textDecoration: 'none',
                                color: 'var(--color-gray-700)'
                            }}
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            style={{
                                padding: '12px 24px',
                                borderRadius: '8px',
                                border: 'none',
                                backgroundColor: 'var(--color-primary)',
                                color: 'white',
                                fontWeight: 500,
                                cursor: processing ? 'not-allowed' : 'pointer',
                                fontSize: '14px',
                                opacity: processing ? 0.7 : 1
                            }}
                        >
                            {processing ? 'Creating...' : 'Create Work Order'}
                        </button>
                    </div>
                </form>
            </div>
        </MainLayout>
    );
}
