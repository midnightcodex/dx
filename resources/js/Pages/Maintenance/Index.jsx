import React, { useState } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function MaintenanceDashboard({ tickets, equipmentList }) {
    const [createModalOpen, setCreateModalOpen] = useState(false);
    const [resolveModalOpen, setResolveModalOpen] = useState(false);
    const [selectedTicket, setSelectedTicket] = useState(null);

    const { data: createData, setData: setCreateData, post: postCreate, processing: processingCreate, reset: resetCreate, errors: createErrors } = useForm({
        equipment_id: '',
        subject: '',
        description: '',
        priority: 'NORMAL',
    });

    const { data: resolveData, setData: setResolveData, post: postResolve, processing: processingResolve, reset: resetResolve, errors: resolveErrors } = useForm({
        resolution_notes: '',
    });

    const handleCreate = (e) => {
        e.preventDefault();
        postCreate(route('maintenance.tickets.store'), {
            onSuccess: () => {
                setCreateModalOpen(false);
                resetCreate();
            }
        });
    };

    const openResolve = (ticket) => {
        setSelectedTicket(ticket);
        setResolveModalOpen(true);
    };

    const handleResolve = (e) => {
        e.preventDefault();
        postResolve(route('maintenance.tickets.resolve', selectedTicket.id), {
            onSuccess: () => {
                setResolveModalOpen(false);
                resetResolve();
                setSelectedTicket(null);
            }
        });
    };

    const columns = [
        { header: 'Ticket #', accessor: 'ticket_number', className: 'font-bold' },
        {
            header: 'Status',
            accessor: 'status',
            render: (val) => (
                <span className={`px-2 py-1 rounded text-xs font-bold ${val === 'OPEN' ? 'bg-red-100 text-red-800' :
                        val === 'CLOSED' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800'
                    }`}>
                    {val}
                </span>
            )
        },
        { header: 'Subject', accessor: 'subject' },
        { header: 'Equipment', accessor: 'equipment.name' },
        {
            header: 'Priority',
            accessor: 'priority',
            render: (val) => (
                <span className={`font-medium ${val === 'CRITICAL' ? 'text-red-600' :
                        val === 'HIGH' ? 'text-orange-600' : 'text-gray-600'
                    }`}>
                    {val}
                </span>
            )
        },
        { header: 'Reported By', accessor: 'reporter.name' },
        { header: 'Date', accessor: 'created_at', render: (val) => new Date(val).toLocaleDateString() },
        {
            header: 'Actions',
            accessor: 'id',
            render: (id, row) => row.status !== 'CLOSED' && (
                <button
                    onClick={() => openResolve(row)}
                    className="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                    Resolve
                </button>
            )
        }
    ];

    return (
        <MainLayout title="Maintenance Dashboard" subtitle="Track Repairs & Issues">
            <Head title="Maintenance" />

            <div className="flex justify-between items-center mb-6">
                <div className="flex gap-4">
                    <Link href={route('maintenance.equipment.index')} className="text-gray-600 hover:text-indigo-600 font-medium pt-2">
                        View Equipment Registry →
                    </Link>
                </div>
                <button
                    onClick={() => setCreateModalOpen(true)}
                    className="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow"
                >
                    Report Breakdown
                </button>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={tickets.data}
                    pagination={tickets}
                />
            </div>

            {/* Create Ticket Modal */}
            <Modal isOpen={createModalOpen} onClose={() => setCreateModalOpen(false)} title="Report Maintenance Issue">
                <form onSubmit={handleCreate} className="space-y-4">
                    <Select
                        label="Equipment *"
                        value={createData.equipment_id}
                        onChange={e => setCreateData('equipment_id', e.target.value)}
                        options={equipmentList.map(eq => ({ value: eq.id, label: `${eq.code} - ${eq.name}` }))}
                        error={createErrors.equipment_id}
                    />
                    <Input
                        label="Subject *"
                        value={createData.subject}
                        onChange={e => setCreateData('subject', e.target.value)}
                        error={createErrors.subject}
                        placeholder="Brief summary of issue"
                    />
                    <Select
                        label="Priority"
                        value={createData.priority}
                        onChange={e => setCreateData('priority', e.target.value)}
                        options={[
                            { value: 'LOW', label: 'Low - Routine' },
                            { value: 'NORMAL', label: 'Normal' },
                            { value: 'HIGH', label: 'High - Urgent' },
                            { value: 'CRITICAL', label: 'Critical - Production Stopped' },
                        ]}
                        error={createErrors.priority}
                    />
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            rows="4"
                            value={createData.description}
                            onChange={e => setCreateData('description', e.target.value)}
                        ></textarea>
                        {createErrors.description && <p className="text-red-500 text-xs mt-1">{createErrors.description}</p>}
                    </div>

                    <div className="flex justify-end pt-4 gap-3">
                        <button type="button" onClick={() => setCreateModalOpen(false)} className="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" disabled={processingCreate} className="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50">Submit Report</button>
                    </div>
                </form>
            </Modal>

            {/* Resolve Modal */}
            <Modal isOpen={resolveModalOpen} onClose={() => setResolveModalOpen(false)} title={`Resolve Ticket ${selectedTicket?.ticket_number}`}>
                <form onSubmit={handleResolve} className="space-y-4">
                    <p className="text-sm text-gray-600 mb-4">
                        Please describe the work done to resolve this issue. This will close the ticket and mark the equipment as Operational.
                    </p>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Resolution Notes *</label>
                        <textarea
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            rows="4"
                            value={resolveData.resolution_notes}
                            onChange={e => setResolveData('resolution_notes', e.target.value)}
                            placeholder="Replaced bearing, tested, working fine..."
                        ></textarea>
                        {resolveErrors.resolution_notes && <p className="text-red-500 text-xs mt-1">{resolveErrors.resolution_notes}</p>}
                    </div>

                    <div className="flex justify-end pt-4 gap-3">
                        <button type="button" onClick={() => setResolveModalOpen(false)} className="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" disabled={processingResolve} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">Resolve & Close</button>
                    </div>
                </form>
            </Modal>
        </MainLayout>
    );
}
