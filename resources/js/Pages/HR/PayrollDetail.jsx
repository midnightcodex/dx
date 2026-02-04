import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import DataTable from '../../Components/Dashboard/DataTable';

export default function PayrollDetail({ payroll, payslips }) {
    const period = `${new Date(2000, payroll.month - 1).toLocaleString('default', { month: 'long' })} ${payroll.year}`;

    const columns = [
        { header: 'Employee', accessor: 'employee.first_name', render: (_, row) => `${row.employee.first_name} ${row.employee.last_name}` },
        { header: 'Employee Code', accessor: 'employee.employee_code' },
        { header: 'Gross Earnings', accessor: 'gross_earnings', render: (val) => `₹${Number(val).toFixed(2)}` },
        { header: 'Deductions', accessor: 'total_deductions', render: (val) => `₹${Number(val).toFixed(2)}` },
        { header: 'Net Pay', accessor: 'net_pay', className: 'font-bold text-green-700', render: (val) => `₹${Number(val).toFixed(2)}` },
        {
            header: 'Action',
            accessor: 'id',
            render: () => <button className="text-gray-400 cursor-not-allowed">Download PDF</button> // PDF V2
        }
    ];

    // Mock Pagination wrap
    const paginated = {
        data: payslips,
        current_page: 1,
        last_page: 1,
        total: payslips.length,
        links: []
    };

    return (
        <MainLayout title={`Payroll: ${period}`} subtitle={`${payslips.length} Payslips Generated`}>
            <Head title="Payroll Detail" />

            <div className="mb-4">
                <Link href={route('hr.payroll.index')} className="text-indigo-600 hover:underline">← Back to Payroll History</Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={paginated.data}
                    pagination={paginated}
                />
            </div>
        </MainLayout>
    );
}
