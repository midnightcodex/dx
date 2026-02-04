import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import DataTable from '../../Components/Dashboard/DataTable';

export default function Payroll({ payrolls }) {
    const [modalOpen, setModalOpen] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear(),
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.payroll.store'), {
            onSuccess: () => {
                setModalOpen(false);
                reset();
            }
        });
    };

    const columns = [
        { header: 'Period', accessor: 'id', render: (_, row) => `${new Date(2000, row.month - 1).toLocaleString('default', { month: 'long' })} ${row.year}` },
        { header: 'Status', accessor: 'status' },
        { header: 'Processed Amount', accessor: 'id', render: () => 'View Detail' }, // Placeholder aggregation if not in Model
        { header: 'Processed At', accessor: 'processed_at', render: (val) => val ? new Date(val).toLocaleString() : '-' },
        {
            header: 'Action',
            accessor: 'id',
            render: (id) => (
                <Link href={route('hr.payroll.show', id)} className="text-indigo-600 hover:underline">
                    View Payslips
                </Link>
            )
        }
    ];

    return (
        <MainLayout title="Payroll Processing">
            <Head title="Payroll" />

            <div className="flex justify-end mb-4">
                <button
                    onClick={() => setModalOpen(true)}
                    className="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                    Run Payroll
                </button>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={payrolls.data}
                    pagination={payrolls}
                />
            </div>

            <Modal isOpen={modalOpen} onClose={() => setModalOpen(false)} title="Run Payroll">
                <form onSubmit={submit} className="space-y-4">
                    <p className="text-sm text-gray-600">
                        This will calculate salaries for all active employees based on their assigned salary structure.
                    </p>
                    <div className="grid grid-cols-2 gap-4">
                        <Input
                            label="Month (1-12) *"
                            type="number"
                            min="1" max="12"
                            value={data.month}
                            onChange={e => setData('month', e.target.value)}
                            error={errors.month}
                        />
                        <Input
                            label="Year *"
                            type="number"
                            min="2025"
                            value={data.year}
                            onChange={e => setData('year', e.target.value)}
                            error={errors.year}
                        />
                    </div>

                    <div className="flex justify-end pt-4 gap-3">
                        <button type="button" onClick={() => setModalOpen(false)} className="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                            {processing ? 'Processing...' : 'Run Payroll'}
                        </button>
                    </div>
                </form>
            </Modal>
        </MainLayout>
    );
}
