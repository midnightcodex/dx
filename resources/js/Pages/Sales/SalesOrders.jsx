import React from 'react';
import { Head, Link } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import DataTable from '../../Components/Dashboard/DataTable';

export default function SalesOrders({ orders, filters }) {
    const columns = [
        {
            header: 'SO Number',
            accessor: 'so_number',
            render: (val, row) => (
                <Link href={route('sales.orders.show', row.id)} className="text-indigo-600 hover:text-indigo-900 font-medium">
                    {val}
                </Link>
            )
        },
        { header: 'Customer', accessor: 'customer.name' },
        { header: 'Date', accessor: 'order_date' },
        { header: 'Total', accessor: 'total_amount', render: (val) => `₹${Number(val).toFixed(2)}` },
        {
            header: 'Status',
            accessor: 'status',
            render: (val) => {
                const colors = {
                    DRAFT: 'bg-gray-100 text-gray-800',
                    CONFIRMED: 'bg-blue-100 text-blue-800',
                    SHIPPED: 'bg-green-100 text-green-800',
                    CANCELLED: 'bg-red-100 text-red-800',
                };
                return (
                    <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${colors[val] || 'bg-gray-100'}`}>
                        {val}
                    </span>
                );
            }
        },
    ];

    return (
        <MainLayout title="Sales Orders">
            <Head title="Sales Orders" />

            <div className="flex justify-between items-center mb-6">
                <h1 className="text-2xl font-bold text-gray-900">All Orders</h1>
                <Link
                    href={route('sales.orders.create')}
                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                >
                    + New Order
                </Link>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden">
                <DataTable
                    columns={columns}
                    data={orders.data}
                    pagination={orders}
                />
            </div>
        </MainLayout>
    );
}
