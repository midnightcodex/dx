import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';

export default function GRNShow({ grn }) {
    const { purchase_order, lines, received_by } = grn;

    const handlePost = () => {
        if (confirm('Are you sure you want to POST this GRN? This will update inventory stock levels and cannot be undone.')) {
            router.post(route('procurement.grn.post', grn.id));
        }
    };

    const statusColors = {
        DRAFT: { bg: 'bg-gray-100', text: 'text-gray-800' },
        INSPECTING: { bg: 'bg-yellow-100', text: 'text-yellow-800' },
        APPROVED: { bg: 'bg-blue-100', text: 'text-blue-800' },
        POSTED: { bg: 'bg-green-100', text: 'text-green-800' },
        CANCELLED: { bg: 'bg-red-100', text: 'text-red-800' },
    };

    const statusStyle = statusColors[grn.status] || statusColors.DRAFT;

    return (
        <MainLayout title={`GRN ${grn.grn_number}`}>
            <Head title={grn.grn_number} />

            <div className="max-w-7xl mx-auto py-6 space-y-6">
                {/* Header & Actions */}
                <div className="bg-white shadow rounded-lg p-6 flex justify-between items-start">
                    <div>
                        <div className="flex items-center gap-4">
                            <h1 className="text-2xl font-bold text-gray-900">{grn.grn_number}</h1>
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${statusStyle.bg} ${statusStyle.text}`}>
                                {grn.status}
                            </span>
                        </div>
                        <p className="mt-1 text-sm text-gray-500">
                            Received on {new Date(grn.receipt_date).toLocaleDateString()} against <Link href={route('procurement.purchase-orders.show', purchase_order.id)} className="text-indigo-600 hover:underline">{purchase_order.po_number}</Link>
                        </p>
                    </div>
                    <div className="flex gap-3">
                        {grn.status === 'DRAFT' && (
                            <button
                                onClick={handlePost}
                                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium flex items-center gap-2"
                            >
                                <span>📦</span> Post to Inventory
                            </button>
                        )}
                        <Link
                            href={route('procurement.grn.index')}
                            className="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 font-medium"
                        >
                            Back
                        </Link>
                    </div>
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="bg-white shadow rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Receipt Details</h3>
                        <dl className="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Vendor</dt>
                                <dd className="mt-1 text-sm text-gray-900">{grn.purchase_order.vendor.name}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Received By</dt>
                                <dd className="mt-1 text-sm text-gray-900">{received_by?.name || 'N/A'}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Warehouse</dt>
                                <dd className="mt-1 text-sm text-gray-900">{grn.warehouse?.name}</dd>
                            </div>
                        </dl>
                    </div>

                    <div className="bg-white shadow rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Invoice Info</h3>
                        <dl className="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Supplier Invoice #</dt>
                                <dd className="mt-1 text-sm text-gray-900">{grn.supplier_invoice_number || '-'}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Invoice Date</dt>
                                <dd className="mt-1 text-sm text-gray-900">{grn.supplier_invoice_date || '-'}</dd>
                            </div>
                            <div className="col-span-2">
                                <dt className="text-sm font-medium text-gray-500">Notes</dt>
                                <dd className="mt-1 text-sm text-gray-900">{grn.notes || '-'}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {/* Lines */}
                <div className="bg-white shadow rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="text-lg font-medium text-gray-900">Received Items</h3>
                    </div>
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Accepted</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {lines.map((line) => (
                                <tr key={line.id}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">{line.item?.name}</div>
                                        <div className="text-sm text-gray-500">{line.item?.item_code}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{Number(line.ordered_quantity).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{Number(line.received_quantity).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-red-600 text-right">
                                        {Number(line.rejected_quantity) > 0 ? Number(line.rejected_quantity).toFixed(2) : '-'}
                                        {line.rejection_reason && <div className="text-xs text-red-500">{line.rejection_reason}</div>}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-green-700 font-bold text-right">{Number(line.accepted_quantity).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            ${line.quality_status === 'PASSED' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`}>
                                            {line.quality_status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </MainLayout>
    );
}
