import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function SalesOrderShow({ order, warehouses }) {
    const [shipModalOpen, setShipModalOpen] = useState(false);

    const handleConfirm = () => {
        if (confirm('Confirm this order?')) {
            router.post(route('sales.orders.confirm', order.id));
        }
    };

    return (
        <MainLayout title={`Sales Order: ${order.so_number}`} subtitle={order.customer?.name}>
            <Head title={`SO - ${order.so_number}`} />

            <div className="flex justify-between items-center mb-6">
                <Link href={route('sales.orders')} className="text-indigo-600 hover:underline">← Back to List</Link>
                <div className="flex gap-3">
                    {order.status === 'DRAFT' && (
                        <button onClick={handleConfirm} className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-medium">
                            Confirm Order
                        </button>
                    )}
                    {['CONFIRMED', 'PARTIAL'].includes(order.status) && ( // Assuming PARTIAL is functionally treated like CONFIRMED for shipping
                        <button onClick={() => setShipModalOpen(true)} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 font-medium flex items-center gap-2">
                            📦 Ship Items
                        </button>
                    )}
                </div>
            </div>

            {/* Status Badge */}
            <div className="mb-6">
                <span className={`px-3 py-1 rounded-full text-sm font-bold 
                    ${order.status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : ''}
                    ${order.status === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : ''}
                    ${order.status === 'SHIPPED' ? 'bg-green-100 text-green-800' : ''}
                 `}>
                    {order.status}
                </span>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div className="bg-white shadow rounded-lg p-6">
                    <h3 className="font-medium text-gray-900 border-b pb-2 mb-4">Customer Details</h3>
                    <p className="font-bold">{order.customer?.name}</p>
                    <p className="text-sm text-gray-600">{order.customer?.email}</p>
                    <p className="text-sm text-gray-600 mt-2">Billing: {order.billing_address_snapshot}</p>
                </div>
                <div className="bg-white shadow rounded-lg p-6">
                    <h3 className="font-medium text-gray-900 border-b pb-2 mb-4">Order Info</h3>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <p className="text-gray-500">Order Date</p> <p>{order.order_date}</p>
                        <p className="text-gray-500">Exp. Ship Date</p> <p>{order.expected_ship_date || '-'}</p>
                        <p className="text-gray-500">Total Amount</p> <p className="font-bold">₹{Number(order.total_amount).toFixed(2)}</p>
                    </div>
                </div>
            </div>

            <div className="bg-white shadow rounded-lg overflow-hidden mb-8">
                <div className="px-6 py-4 border-b">
                    <h3 className="font-medium text-gray-900">Line Items</h3>
                </div>
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Shipped</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {order.lines.map(line => (
                            <tr key={line.id}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{line.item?.name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{Number(line.quantity).toFixed(2)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold text-right">{Number(line.shipped_quantity).toFixed(2)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">₹{Number(line.unit_price).toFixed(2)}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">₹{Number(line.line_amount).toFixed(2)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {order.shipments && order.shipments.length > 0 && (
                <div className="bg-white shadow rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b bg-gray-50">
                        <h3 className="font-medium text-gray-900">Shipments</h3>
                    </div>
                    <ul>
                        {order.shipments.map(ship => (
                            <li key={ship.id} className="px-6 py-4 border-b last:border-0 hover:bg-gray-50">
                                <div className="flex justify-between">
                                    <div className="font-medium text-indigo-700">{ship.shipment_number}</div>
                                    <div className="text-sm text-gray-600">{ship.shipment_date}</div>
                                    <div className="text-sm text-gray-600">Via {ship.warehouse?.name}</div>
                                </div>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <ShipModal
                isOpen={shipModalOpen}
                onClose={() => setShipModalOpen(false)}
                order={order}
                warehouses={warehouses}
            />
        </MainLayout>
    );
}

function ShipModal({ isOpen, onClose, order, warehouses }) {
    // Only show lines that aren't fully shipped
    const pendingLines = order.lines.filter(l => Number(l.shipped_quantity) < Number(l.quantity));

    const { data, setData, post, processing, reset, errors } = useForm({
        warehouse_id: '',
        shipment_date: new Date().toISOString().split('T')[0],
        lines: pendingLines.map(l => ({
            line_id: l.id,
            item_name: l.item?.name,
            pending: Number(l.quantity) - Number(l.shipped_quantity),
            quantity: Number(l.quantity) - Number(l.shipped_quantity) // Default to full pending
        }))
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('sales.orders.ship', order.id), {
            onSuccess: () => {
                reset();
                onClose();
            }
        });
    };

    const handleQtyChange = (idx, val) => {
        const newLines = [...data.lines];
        newLines[idx].quantity = val;
        setData('lines', newLines);
    };

    return (
        <Modal isOpen={isOpen} onClose={onClose} title="Create Shipment">
            <form onSubmit={submit} className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                    <Select
                        label="From Warehouse *"
                        value={data.warehouse_id}
                        onChange={e => setData('warehouse_id', e.target.value)}
                        options={warehouses.map(w => ({ value: w.id, label: w.name }))}
                        error={errors.warehouse_id}
                    />
                    <Input
                        label="Shipment Date *"
                        type="date"
                        value={data.shipment_date}
                        onChange={e => setData('shipment_date', e.target.value)}
                        error={errors.shipment_date}
                    />
                </div>

                <div className="border-t pt-4">
                    <h4 className="text-sm font-bold text-gray-700 mb-2">Items to Ship</h4>
                    {data.lines.map((line, idx) => (
                        <div key={line.line_id} className="flex justify-between items-center mb-2 text-sm">
                            <div className="w-1/2">
                                <div className="font-medium">{line.item_name}</div>
                                <div className="text-xs text-gray-500">Pending: {line.pending}</div>
                            </div>
                            <div className="w-1/4">
                                <Input
                                    type="number"
                                    step="0.01"
                                    value={line.quantity}
                                    onChange={e => handleQtyChange(idx, e.target.value)}
                                    className="h-8 text-sm"
                                />
                            </div>
                        </div>
                    ))}
                </div>

                <div className="flex justify-end pt-4 border-t gap-3">
                    <button type="button" onClick={onClose} className="px-4 py-2 border rounded">Cancel</button>
                    <button type="submit" disabled={processing} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                        {processing ? 'Shipping...' : 'Create Shipment'}
                    </button>
                </div>
            </form>
        </Modal>
    );
}
