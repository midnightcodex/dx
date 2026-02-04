import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';
import DataTable from '../../Components/Dashboard/DataTable';

export default function Leaves({ requests, leaveTypes, balances, isEmployee }) {
    const [applyModalOpen, setApplyModalOpen] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        leave_type_id: '',
        start_date: '',
        end_date: '',
        reason: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.leaves.store'), {
            onSuccess: () => {
                setApplyModalOpen(false);
                reset();
            }
        });
    };

    const handleAction = (id, status) => {
        if (!confirm(`Are you sure you want to ${status} this request?`)) return;
        router.post(route('hr.leaves.update', id), {
            status,
            rejection_reason: status === 'REJECTED' ? 'Manager Rejection' : null
        });
    };

    const columns = [
        { header: 'Employee', accessor: 'employee.first_name', render: (_, row) => `${row.employee.first_name} ${row.employee.last_name}` },
        { header: 'Type', accessor: 'type.name' },
        { header: 'Dates', accessor: 'start_date', render: (_, row) => `${row.start_date} to ${row.end_date} (${Number(row.days_requested)} days)` },
        { header: 'Reason', accessor: 'reason' },
        {
            header: 'Status',
            accessor: 'status',
            render: (val) => (
                <span className={`px-2 py-1 rounded text-xs font-bold ${val === 'APPROVED' ? 'bg-green-100 text-green-800' :
                        val === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'
                    }`}>
                    {val}
                </span>
            )
        },
        {
            header: 'Actions',
            accessor: 'id',
            render: (id, row) => row.status === 'PENDING' && (
                <div className="flex gap-2">
                    <button onClick={() => handleAction(id, 'APPROVED')} className="text-green-600 hover:underline">Approve</button>
                    <button onClick={() => handleAction(id, 'REJECTED')} className="text-red-600 hover:underline">Reject</button>
                </div>
            )
        }
    ];

    return (
        <MainLayout title="Leave Management">
            <Head title="Leaves" />

            {/* Balances Card (Only for Employees) */}
            {isEmployee && (
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    {balances.map(bal => (
                        <div key={bal.id} className="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
                            <h4 className="text-gray-500 font-medium">{bal.type.name}</h4>
                            <div className="mt-2 flex items-baseline gap-2">
                                <span className="text-3xl font-bold text-gray-900">{Number(bal.days_allocated - bal.days_used)}</span>
                                <span className="text-gray-500">/ {Number(bal.days_allocated)} days left</span>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            <div className="flex justify-end mb-4">
                <button
                    onClick={() => setApplyModalOpen(true)}
                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    Apply for Leave
                </button>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={requests.data}
                    pagination={requests}
                />
            </div>

            <Modal isOpen={applyModalOpen} onClose={() => setApplyModalOpen(false)} title="Apply for Leave">
                <form onSubmit={submit} className="space-y-4">
                    <Select
                        label="Leave Type *"
                        value={data.leave_type_id}
                        onChange={e => setData('leave_type_id', e.target.value)}
                        options={leaveTypes.map(t => ({ value: t.id, label: t.name }))}
                        error={errors.leave_type_id}
                    />
                    <div className="grid grid-cols-2 gap-4">
                        <Input label="Start Date *" type="date" value={data.start_date} onChange={e => setData('start_date', e.target.value)} error={errors.start_date} />
                        <Input label="End Date *" type="date" value={data.end_date} onChange={e => setData('end_date', e.target.value)} error={errors.end_date} />
                    </div>
                    <Input label="Reason *" value={data.reason} onChange={e => setData('reason', e.target.value)} error={errors.reason} />

                    <div className="flex justify-end pt-4 gap-3">
                        <button type="button" onClick={() => setApplyModalOpen(false)} className="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Submit Request</button>
                    </div>
                </form>
            </Modal>
        </MainLayout>
    );
}
