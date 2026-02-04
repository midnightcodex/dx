import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';
import StatusBadge from '../../Components/UI/StatusBadge'; // We used inline status badges before, maybe create one later or inline here.

export default function EquipmentList({ equipment }) {
    const [isModalOpen, setIsModalOpen] = useState(false);

    const { data, setData, post, processing, reset, errors } = useForm({
        name: '',
        code: '',
        status: 'OPERATIONAL',
        location: '',
        manufacturer: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('maintenance.equipment.store'), {
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
            }
        });
    };

    const columns = [
        { header: 'Code', accessor: 'code', className: 'font-medium text-gray-900' },
        { header: 'Name', accessor: 'name' },
        { header: 'Location', accessor: 'location' },
        { header: 'Manufacturer', accessor: 'manufacturer' },
        {
            header: 'Status',
            accessor: 'status',
            render: (val) => {
                const colors = {
                    OPERATIONAL: 'bg-green-100 text-green-800',
                    DOWN: 'bg-red-100 text-red-800',
                    MAINTENANCE: 'bg-yellow-100 text-yellow-800',
                };
                return (
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors[val]}`}>
                        {val}
                    </span>
                );
            }
        },
    ];

    return (
        <MainLayout title="Equipment Registry" subtitle="Manage Assets & Machines">
            <Head title="Equipment" />

            <div className="flex justify-end mb-4">
                <button
                    onClick={() => setIsModalOpen(true)}
                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Add Equipment
                </button>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={equipment.data}
                    pagination={equipment}
                />
            </div>

            <Modal isOpen={isModalOpen} onClose={() => setIsModalOpen(false)} title="Register Equipment">
                <form onSubmit={handleSubmit} className="space-y-4">
                    <Input
                        label="Equipment Code"
                        value={data.code}
                        onChange={e => setData('code', e.target.value)}
                        error={errors.code}
                        placeholder="e.g., EQ-005"
                    />
                    <Input
                        label="Name"
                        value={data.name}
                        onChange={e => setData('name', e.target.value)}
                        error={errors.name}
                        placeholder="e.g., HVAC Unit"
                    />
                    <div className="grid grid-cols-2 gap-4">
                        <Select
                            label="Status"
                            value={data.status}
                            onChange={e => setData('status', e.target.value)}
                            options={[
                                { value: 'OPERATIONAL', label: 'Operational' },
                                { value: 'DOWN', label: 'Down' },
                                { value: 'MAINTENANCE', label: 'Maintenance' },
                            ]}
                            error={errors.status}
                        />
                        <Input
                            label="Location"
                            value={data.location}
                            onChange={e => setData('location', e.target.value)}
                            error={errors.location}
                        />
                    </div>
                    <Input
                        label="Manufacturer"
                        value={data.manufacturer}
                        onChange={e => setData('manufacturer', e.target.value)}
                        error={errors.manufacturer}
                    />

                    <div className="flex justify-end pt-4 gap-3">
                        <button type="button" onClick={() => setIsModalOpen(false)} className="px-4 py-2 border rounded">Cancel</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">Save</button>
                    </div>
                </form>
            </Modal>
        </MainLayout>
    );
}
