import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import DataTable from '../../Components/Dashboard/DataTable';

export default function Employees({ employees, filters }) {
    const columns = [
        { header: 'Employee', accessor: 'name', className: 'font-bold text-gray-900', render: (_, row) => `${row.first_name} ${row.last_name}` },
        { header: 'Code', accessor: 'employee_code' },
        { header: 'Department', accessor: 'department.name' },
        { header: 'Designation', accessor: 'designation' },
        { header: 'Email', accessor: 'email' },
        { header: 'Status', accessor: 'status' },
    ];

    return (
        <MainLayout title="Employee Directory" subtitle="Manage Workforce">
            <Head title="Employees" />

            <div className="flex justify-between items-center mb-6">
                <div className="flex gap-2">
                    <input
                        type="text"
                        placeholder="Search employees..."
                        className="border rounded px-3 py-2"
                    // Add search logic later
                    />
                </div>
                <Link
                    href={route('hr.employees.create')}
                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    + Add Employee
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={employees.data}
                    pagination={employees}
                />
            </div>
        </MainLayout>
    );
}
