import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';

export default function WorkOrderShow({ workOrder }) {
    const wo = workOrder;
    const [issueModalOpen, setIssueModalOpen] = useState(false);
    const [completeModalOpen, setCompleteModalOpen] = useState(false);

    const statusColors = {
        'PLANNED': { bg: '#E0E7FF', color: '#4338CA', label: 'Planned' },
        'RELEASED': { bg: '#FEF3C7', color: '#D97706', label: 'Released' },
        'IN_PROGRESS': { bg: '#DBEAFE', color: '#1D4ED8', label: 'In Progress' },
        'COMPLETED': { bg: '#D1FAE5', color: '#059669', label: 'Completed' },
        'CANCELLED': { bg: '#FEE2E2', color: '#DC2626', label: 'Cancelled' },
    };

    const status = statusColors[wo.status] || { bg: '#F3F4F6', color: '#6B7280', label: wo.status };

    const handleRelease = () => {
        if (confirm('Release this Work Order? This will allocate materials.')) {
            router.post(route('manufacturing.work-orders.release', wo.id));
        }
    };

    const handleStart = () => router.post(route('manufacturing.work-orders.start', wo.id));

    return (
        <MainLayout title={`Work Order: ${wo.wo_number}`} subtitle={wo.item?.name}>
            <Head title={`WO - ${wo.wo_number}`} />

            {/* Header Actions */}
            <div className="flex justify-between items-center mb-6">
                <Link
                    href={route('manufacturing.work-orders')}
                    className="px-4 py-2 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-medium"
                >
                    ← Back to List
                </Link>

                <div className="flex gap-3">
                    {wo.status === 'PLANNED' && (
                        <button onClick={handleRelease} className="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded font-medium text-sm flex items-center gap-2">
                            🚀 Release for Production
                        </button>
                    )}
                    {wo.status === 'RELEASED' && (
                        <button onClick={handleStart} className="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded font-medium text-sm flex items-center gap-2">
                            ▶️ Start Production
                        </button>
                    )}
                    {['RELEASED', 'IN_PROGRESS'].includes(wo.status) && (
                        <button onClick={() => setIssueModalOpen(true)} className="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded font-medium text-sm flex items-center gap-2">
                            📉 Issue Materials
                        </button>
                    )}
                    {wo.status === 'IN_PROGRESS' && (
                        <button onClick={() => setCompleteModalOpen(true)} className="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded font-medium text-sm flex items-center gap-2">
                            ✅ Record Output
                        </button>
                    )}
                </div>
            </div>

            {/* Status Badge */}
            <div className="mb-6">
                <span className="px-3 py-1 rounded-full text-sm font-semibold inline-flex items-center gap-2"
                    style={{ backgroundColor: status.bg, color: status.color }}>
                    <span className="w-2 h-2 rounded-full" style={{ backgroundColor: status.color }}></span>
                    {status.label}
                </span>
            </div>

            {/* Main Info Card */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div className="col-span-2 bg-white rounded-lg shadow-sm p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Order Details</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                        <InfoRow label="WO Number" value={wo.wo_number} />
                        <InfoRow label="Item" value={wo.item?.name} />
                        <InfoRow label="Priority" value={wo.priority} />
                        <InfoRow label="BOM Used" value={wo.bom?.bom_code} />
                        <InfoRow label="Source Warehouse" value={wo.source_warehouse?.name} />
                        <InfoRow label="Target Warehouse" value={wo.target_warehouse?.name} />
                        <InfoRow label="Created By" value={wo.created_by?.name} />
                        <InfoRow label="Date Created" value={new Date(wo.created_at).toLocaleDateString()} />
                    </div>
                </div>

                <div className="bg-white rounded-lg shadow-sm p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Production Progress</h3>
                    <div className="text-center py-4">
                        <div className="text-5xl font-bold text-indigo-600 mb-2">
                            {wo.planned_quantity > 0 ? Math.round((wo.completed_quantity || 0) / wo.planned_quantity * 100) : 0}%
                        </div>
                        <div className="text-gray-500 mb-6">
                            {Number(wo.completed_quantity || 0).toFixed(2)} / {Number(wo.planned_quantity).toFixed(2)} Units
                        </div>

                        <div className="w-full bg-gray-200 rounded-full h-3 mb-4 overflow-hidden">
                            <div
                                className="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                                style={{ width: `${Math.min(100, (wo.completed_quantity / wo.planned_quantity) * 100)}%` }}
                            ></div>
                        </div>

                        <div className="grid grid-cols-3 gap-2 text-center text-xs text-gray-500 mt-4 border-t pt-4">
                            <div>
                                <div className="font-semibold text-gray-700">Planned</div>
                                {wo.scheduled_start_date}
                            </div>
                            <div>
                                <div className="font-semibold text-gray-700">Started</div>
                                {wo.actual_start_at ? new Date(wo.actual_start_at).toLocaleDateString() : '-'}
                            </div>
                            <div>
                                <div className="font-semibold text-gray-700">Finished</div>
                                {wo.actual_end_at ? new Date(wo.actual_end_at).toLocaleDateString() : '-'}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Materials Section */}
            <div className="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div className="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 className="text-lg font-medium text-gray-900">
                        {wo.status === 'PLANNED' ? 'Planned Materials (BOM)' : 'Material Requirements & Status'}
                    </h3>
                </div>

                {wo.status === 'PLANNED' ? (
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty / Unit</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Required</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {wo.bom?.lines.map((line, idx) => (
                                <tr key={idx}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{line.item?.name}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{Number(line.quantity).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        {(Number(line.quantity) * Number(wo.planned_quantity)).toFixed(2)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                ) : (
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Required</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Issued</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pending</th>
                                <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {wo.materials?.map((mat, idx) => {
                                const pending = Math.max(0, mat.required_quantity - mat.issued_quantity);
                                const isFullyIssued = pending <= 0.001;
                                return (
                                    <tr key={mat.id}>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{mat.item?.name}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{Number(mat.required_quantity).toFixed(2)}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium text-right">{Number(mat.issued_quantity).toFixed(2)}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-orange-600 text-right">{pending.toFixed(2)}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${isFullyIssued ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                                {isFullyIssued ? 'Fulfilled' : 'Pending'}
                                            </span>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Models */}
            <IssueMaterialModal
                isOpen={issueModalOpen}
                onClose={() => setIssueModalOpen(false)}
                workOrder={wo}
            />

            <CompleteProductionModal
                isOpen={completeModalOpen}
                onClose={() => setCompleteModalOpen(false)}
                workOrder={wo}
            />

        </MainLayout>
    );
}

// Helper Components

function InfoRow({ label, value }) {
    return (
        <div className="mb-2">
            <div className="text-xs text-gray-500 uppercase tracking-wide">{label}</div>
            <div className="text-sm font-medium text-gray-900">{value || '-'}</div>
        </div>
    );
}

function IssueMaterialModal({ isOpen, onClose, workOrder }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        materials: workOrder.materials?.map(m => ({
            id: m.id,
            item_name: m.item?.name,
            pending: Math.max(0, m.required_quantity - m.issued_quantity),
            quantity: Math.max(0, m.required_quantity - m.issued_quantity) // Default to issuing full pending
        })).filter(m => m.pending > 0) || []
    });

    // Update list when WO changes (or modal opens ideally, but this is simple sync)
    // Note: In real app, might want to fetch latest or rely on parent updates
    React.useEffect(() => {
        if (isOpen && workOrder.materials) {
            const pendingMats = workOrder.materials.map(m => ({
                id: m.id,
                item_name: m.item?.name,
                pending: Math.max(0, m.required_quantity - m.issued_quantity),
                quantity: Math.max(0, m.required_quantity - m.issued_quantity)
            })).filter(m => m.pending > 0);
            setData('materials', pendingMats);
        }
    }, [isOpen, workOrder]);

    const submit = (e) => {
        e.preventDefault();
        post(route('manufacturing.work-orders.issue-materials', workOrder.id), {
            onSuccess: () => {
                reset();
                onClose();
            }
        });
    };

    const handleQtyChange = (index, val) => {
        const newMats = [...data.materials];
        newMats[index].quantity = val;
        setData('materials', newMats);
    };

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Issue Materials" footer={
            <>
                <button onClick={onClose} className="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                <button onClick={submit} disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
                    {processing ? 'Issuing...' : 'Issue Selected'}
                </button>
            </>
        }>
            <div className="space-y-4">
                <p className="text-sm text-gray-600 mb-4">
                    Confirm the quantities to issue from the source warehouse ({workOrder.source_warehouse?.name}).
                </p>
                {data.materials.length === 0 && (
                    <div className="p-4 bg-green-50 text-green-700 rounded text-center">
                        All materials have been fully issued!
                    </div>
                )}
                {data.materials.map((mat, idx) => (
                    <div key={mat.id} className="grid grid-cols-12 gap-4 items-center border-b pb-2">
                        <div className="col-span-6">
                            <div className="text-sm font-medium text-gray-900">{mat.item_name}</div>
                            <div className="text-xs text-gray-500">Pending: {Number(mat.pending).toFixed(2)}</div>
                        </div>
                        <div className="col-span-6">
                            <Input
                                type="number"
                                step="0.0001"
                                value={mat.quantity}
                                onChange={(e) => handleQtyChange(idx, e.target.value)}
                                placeholder="Qty to Issue"
                            />
                        </div>
                    </div>
                ))}
            </div>
        </Modal>
    );
}

function CompleteProductionModal({ isOpen, onClose, workOrder }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        completed_quantity: '',
        rejected_quantity: 0,
        notes: ''
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('manufacturing.work-orders.complete', workOrder.id), {
            onSuccess: () => {
                reset();
                onClose();
            }
        });
    };

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Record Production Output" footer={
            <>
                <button onClick={onClose} className="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                <button onClick={submit} disabled={processing} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                    {processing ? 'Recording...' : 'Complete Production'}
                </button>
            </>
        }>
            <div className="space-y-4">
                <p className="text-sm text-gray-600 mb-4">
                    Record finished goods entering the target warehouse ({workOrder.target_warehouse?.name}).
                </p>
                <Input
                    label="Good Quantity Produced *"
                    type="number"
                    step="0.0001"
                    value={data.completed_quantity}
                    onChange={(e) => setData('completed_quantity', e.target.value)}
                    error={errors.completed_quantity}
                    autofocus
                />

                <Input
                    label="Rejected Quantity"
                    type="number"
                    step="0.0001"
                    value={data.rejected_quantity}
                    onChange={(e) => setData('rejected_quantity', e.target.value)}
                    error={errors.rejected_quantity}
                />

                <Input
                    label="Notes"
                    value={data.notes}
                    onChange={(e) => setData('notes', e.target.value)}
                    error={errors.notes}
                />
            </div>
        </Modal>
    );
}
