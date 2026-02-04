import React from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';

export default function PurchaseOrderShow({ purchaseOrder }) {
    const { vendor, lines, goods_receipt_notes } = purchaseOrder;

    const action = (routeKey, method = 'post') => {
        if (confirm('Are you sure you want to proceed with this action?')) {
            router[method](route(routeKey, purchaseOrder.id));
        }
    };

    const statusColors = {
        DRAFT: { bg: 'bg-gray-100', text: 'text-gray-800' },
        SUBMITTED: { bg: 'bg-yellow-100', text: 'text-yellow-800' },
        APPROVED: { bg: 'bg-blue-100', text: 'text-blue-800' },
        PARTIAL: { bg: 'bg-purple-100', text: 'text-purple-800' },
        COMPLETED: { bg: 'bg-green-100', text: 'text-green-800' },
        CANCELLED: { bg: 'bg-red-100', text: 'text-red-800' },
    };

    const statusStyle = statusColors[purchaseOrder.status] || statusColors.DRAFT;

    return (
        <MainLayout title={`Purchase Order ${purchaseOrder.po_number}`}>
            <Head title={purchaseOrder.po_number} />

            <div className="max-w-7xl mx-auto py-6 space-y-6">
                {/* Header & Actions */}
                <div className="bg-white shadow rounded-lg p-6 flex justify-between items-start">
                    <div>
                        <div className="flex items-center gap-4">
                            <h1 className="text-2xl font-bold text-gray-900">{purchaseOrder.po_number}</h1>
                            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${statusStyle.bg} ${statusStyle.text}`}>
                                {purchaseOrder.status}
                            </span>
                        </div>
                        <p className="mt-1 text-sm text-gray-500">Created on {new Date(purchaseOrder.created_at).toLocaleDateString()}</p>
                    </div>
                    <div className="flex gap-3">
                        {purchaseOrder.status === 'DRAFT' && (
                            <>
                                <button
                                    onClick={() => action('procurement.purchase-orders.submit')}
                                    className="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700 font-medium"
                                >
                                    Submit for Approval
                                </button>
                                <button
                                    onClick={() => action('procurement.purchase-orders.cancel')}
                                    className="px-4 py-2 border border-red-300 text-red-700 rounded hover:bg-red-50 font-medium"
                                >
                                    Cancel
                                </button>
                            </>
                        )}

                        {purchaseOrder.status === 'SUBMITTED' && (
                            <>
                                <button
                                    onClick={() => action('procurement.purchase-orders.approve')}
                                    className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium"
                                >
                                    Approve
                                </button>
                                <button
                                    onClick={() => action('procurement.purchase-orders.cancel')}
                                    className="px-4 py-2 border border-red-300 text-red-700 rounded hover:bg-red-50 font-medium"
                                >
                                    Reject / Cancel
                                </button>
                            </>
                        )}

                        {['APPROVED', 'PARTIAL'].includes(purchaseOrder.status) && (
                            <Link
                                href={route('procurement.grn.create', { po_id: purchaseOrder.id })}
                                className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium flex items-center gap-2"
                            >
                                <span>📦</span> Receive Goods (GRN)
                            </Link>
                        )}

                        <Link
                            href={route('procurement.purchase-orders')}
                            className="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 font-medium"
                        >
                            Back
                        </Link>
                    </div>
                </div>

                {/* Details Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {/* Vendor Info */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Vendor Details</h3>
                        <dl className="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Vendor</dt>
                                <dd className="mt-1 text-sm text-gray-900">{vendor.name}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Contact</dt>
                                <dd className="mt-1 text-sm text-gray-900">{vendor.contact_person}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Email</dt>
                                <dd className="mt-1 text-sm text-gray-900">{vendor.email}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Phone</dt>
                                <dd className="mt-1 text-sm text-gray-900">{vendor.phone}</dd>
                            </div>
                        </dl>
                    </div>

                    {/* Order Info */}
                    <div className="bg-white shadow rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Order Information</h3>
                        <dl className="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Order Date</dt>
                                <dd className="mt-1 text-sm text-gray-900">{purchaseOrder.order_date}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Expected Delivery</dt>
                                <dd className="mt-1 text-sm text-gray-900">{purchaseOrder.expected_date}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Warehouse</dt>
                                <dd className="mt-1 text-sm text-gray-900">{purchaseOrder.delivery_warehouse?.name}</dd>
                            </div>
                            <div className="sm:col-span-1">
                                <dt className="text-sm font-medium text-gray-500">Payment Terms</dt>
                                <dd className="mt-1 text-sm text-gray-900">{purchaseOrder.payment_terms || 'N/A'}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {/* Line Items */}
                <div className="bg-white shadow rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="text-lg font-medium text-gray-900">Line Items</h3>
                    </div>
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {lines.map((line, index) => (
                                <tr key={line.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{index + 1}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">{line.item?.name}</div>
                                        <div className="text-sm text-gray-500">{line.item?.item_code}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{Number(line.quantity).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        <span className={line.received_quantity >= line.quantity ? 'text-green-600 font-bold' : 'text-gray-900'}>
                                            {Number(line.received_quantity).toFixed(2)}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">₹{Number(line.unit_price).toFixed(2)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">₹{Number(line.line_amount).toFixed(2)}</td>
                                </tr>
                            ))}
                        </tbody>
                        <tfoot className="bg-gray-50">
                            <tr>
                                <td colSpan="5" className="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal</td>
                                <td className="px-6 py-3 text-right text-sm font-bold text-gray-900">₹{Number(purchaseOrder.subtotal).toFixed(2)}</td>
                            </tr>
                            <tr>
                                <td colSpan="5" className="px-6 py-3 text-right text-sm font-medium text-gray-500">Tax</td>
                                <td className="px-6 py-3 text-right text-sm font-bold text-gray-900">₹{Number(purchaseOrder.tax_amount).toFixed(2)}</td>
                            </tr>
                            <tr className="bg-gray-100">
                                <td colSpan="5" className="px-6 py-4 text-right text-base font-bold text-gray-900">Grand Total</td>
                                <td className="px-6 py-4 text-right text-base font-bold text-indigo-700">₹{Number(purchaseOrder.total_amount).toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {/* Related GRNs */}
                {goods_receipt_notes && goods_receipt_notes.length > 0 && (
                    <div className="bg-white shadow rounded-lg overflow-hidden">
                        <div className="px-6 py-4 border-b border-gray-200">
                            <h3 className="text-lg font-medium text-gray-900">Related Goods Receipts (GRN)</h3>
                        </div>
                        <ul className="divide-y divide-gray-200">
                            {goods_receipt_notes.map((grn) => (
                                <li key={grn.id} className="px-6 py-4 hover:bg-gray-50">
                                    <div className="flex items-center justify-between">
                                        <div className="flex gap-4">
                                            <span className="text-sm font-medium text-indigo-600">{grn.grn_number}</span>
                                            <span className="text-sm text-gray-500">{grn.receipt_date}</span>
                                            <span className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {grn.status}
                                            </span>
                                        </div>
                                        <Link href={route('procurement.grn.show', grn.id)} className="text-sm font-medium text-gray-600 hover:text-gray-900">
                                            View Details →
                                        </Link>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </MainLayout>
    );
}
