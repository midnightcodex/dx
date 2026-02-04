import React, { useState, useEffect } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function GRNCreate({ purchaseOrders, selectedPo }) {
    const { data, setData, post, processing, errors } = useForm({
        purchase_order_id: selectedPo ? selectedPo.id : '',
        supplier_invoice_number: '',
        supplier_invoice_date: '',
        notes: '',
        lines: selectedPo ? selectedPo.lines.map(line => ({
            po_line_id: line.id,
            item_name: line.item.name,
            ordered_quantity: line.quantity,
            pending_quantity: Number(line.quantity) - Number(line.received_quantity),
            received_quantity: Number(line.quantity) - Number(line.received_quantity), // Default to full pending
            accepted_quantity: Number(line.quantity) - Number(line.received_quantity),
            rejected_quantity: 0,
            rejection_reason: ''
        })) : []
    });

    const handlePoChange = (e) => {
        const poId = e.target.value;
        if (poId) {
            router.get(route('procurement.grn.create'), { po_id: poId });
        }
    };

    const handleLineChange = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;

        // Auto-calc accepted if rejected changes or received changes
        if (field === 'received_quantity' || field === 'rejected_quantity') {
            const received = parseFloat(newLines[index].received_quantity) || 0;
            const rejected = parseFloat(newLines[index].rejected_quantity) || 0;
            newLines[index].accepted_quantity = Math.max(0, received - rejected);
        }

        setData('lines', newLines);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('procurement.grn.store'));
    };

    const poOptions = purchaseOrders.map(po => ({
        value: po.id,
        label: `${po.po_number} - ${po.vendor.name}`
    }));

    return (
        <MainLayout title="Create Goods Receipt Note">
            <Head title="Create GRN" />

            <div className="max-w-7xl mx-auto py-6">
                <form onSubmit={handleSubmit} className="bg-white shadow rounded-lg p-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <Select
                                label="Select Purchase Order *"
                                name="purchase_order_id"
                                value={data.purchase_order_id}
                                onChange={handlePoChange}
                                options={poOptions}
                                error={errors.purchase_order_id}
                            />
                            {selectedPo && (
                                <div className="mt-2 text-sm text-gray-500">
                                    <p>Vendor: {selectedPo.vendor.name}</p>
                                    <p>Date: {selectedPo.order_date}</p>
                                </div>
                            )}
                        </div>

                        {selectedPo && (
                            <>
                                <Input
                                    label="Supplier Invoice #"
                                    name="supplier_invoice_number"
                                    value={data.supplier_invoice_number}
                                    onChange={e => setData('supplier_invoice_number', e.target.value)}
                                    error={errors.supplier_invoice_number}
                                />
                                <Input
                                    label="Supplier Invoice Date"
                                    type="date"
                                    name="supplier_invoice_date"
                                    value={data.supplier_invoice_date}
                                    onChange={e => setData('supplier_invoice_date', e.target.value)}
                                    error={errors.supplier_invoice_date}
                                />
                                <div className="md:col-span-2">
                                    <Input
                                        label="Notes"
                                        name="notes"
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        error={errors.notes}
                                    />
                                </div>
                            </>
                        )}
                    </div>

                    {selectedPo && (
                        <>
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Items to Receive</h3>
                            <div className="space-y-4 mb-8">
                                {data.lines.map((line, index) => (
                                    <div key={index} className="border p-4 rounded-md bg-gray-50">
                                        <div className="flex justify-between mb-2">
                                            <span className="font-medium text-indigo-700">{line.item_name}</span>
                                            <span className="text-sm text-gray-600">
                                                Ordered: {Number(line.ordered_quantity).toFixed(2)} |
                                                Pending: {Number(line.pending_quantity).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <Input
                                                label="Received Qty *"
                                                type="number"
                                                step="0.0001"
                                                value={line.received_quantity}
                                                onChange={e => handleLineChange(index, 'received_quantity', e.target.value)}
                                                error={errors[`lines.${index}.received_quantity`]}
                                            />
                                            <Input
                                                label="Rejected Qty"
                                                type="number"
                                                step="0.0001"
                                                value={line.rejected_quantity}
                                                onChange={e => handleLineChange(index, 'rejected_quantity', e.target.value)}
                                                error={errors[`lines.${index}.rejected_quantity`]}
                                            />
                                            <Input
                                                label="Rejection Reason"
                                                name="rejection_reason"
                                                value={line.rejection_reason}
                                                onChange={e => handleLineChange(index, 'rejection_reason', e.target.value)}
                                                error={errors[`lines.${index}.rejection_reason`]}
                                            />
                                        </div>
                                        <div className="mt-2 text-sm text-green-700 font-medium">
                                            Accepted Qty: {Number(line.accepted_quantity).toFixed(2)}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="flex justify-end gap-4 mt-8">
                                <Link
                                    href={route('procurement.grn.index')}
                                    className="bg-white border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md hover:bg-gray-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-indigo-600 border border-transparent text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700"
                                >
                                    {processing ? 'Processing...' : 'Create GRN'}
                                </button>
                            </div>
                        </>
                    )}
                </form>
            </div>
        </MainLayout>
    );
}
