import React, { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function PurchaseOrderCreate({ vendors, items, warehouses }) {
    const { data, setData, post, processing, errors } = useForm({
        vendor_id: '',
        expected_date: '',
        delivery_warehouse_id: '',
        payment_terms: '',
        notes: '',
        lines: [
            { item_id: '', quantity: '', unit_price: '', tax_rate: 18, description: '' }
        ]
    });

    // Helper to calculate totals
    const calculateTotals = () => {
        let subtotal = 0;
        let taxTotal = 0;

        data.lines.forEach(line => {
            const qty = parseFloat(line.quantity) || 0;
            const price = parseFloat(line.unit_price) || 0;
            const taxRate = parseFloat(line.tax_rate) || 0;
            const lineBase = qty * price;
            subtotal += lineBase;
            taxTotal += lineBase * (taxRate / 100);
        });

        return { subtotal, taxTotal, total: subtotal + taxTotal };
    };

    const totals = calculateTotals();

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('procurement.purchase-orders.store'));
    };

    const handleLineChange = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;
        setData('lines', newLines);
    };

    const addLine = () => {
        setData('lines', [
            ...data.lines,
            { item_id: '', quantity: '', unit_price: '', tax_rate: 18, description: '' }
        ]);
    };

    const removeLine = (index) => {
        if (data.lines.length > 1) {
            const newLines = data.lines.filter((_, i) => i !== index);
            setData('lines', newLines);
        }
    };

    const vendorOptions = vendors.map(v => ({ value: v.id, label: `${v.name} (${v.vendor_code})` }));
    const itemOptions = items.map(i => ({ value: i.id, label: `${i.item_code} - ${i.name}` }));
    const warehouseOptions = warehouses.map(w => ({ value: w.id, label: w.name }));

    return (
        <MainLayout title="Create Purchase Order">
            <Head title="Create PO" />

            <div className="max-w-7xl mx-auto py-6">
                <form onSubmit={handleSubmit} className="bg-white shadow rounded-lg p-6">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <Select
                            label="Vendor *"
                            name="vendor_id"
                            value={data.vendor_id}
                            onChange={e => {
                                const v = vendors.find(vend => vend.id === e.target.value);
                                setData(d => ({
                                    ...d,
                                    vendor_id: e.target.value,
                                    payment_terms: v ? v.payment_terms : ''
                                }));
                            }}
                            options={vendorOptions}
                            error={errors.vendor_id}
                        />

                        <Input
                            label="Expected Delivery *"
                            type="date"
                            name="expected_date"
                            value={data.expected_date}
                            onChange={e => setData('expected_date', e.target.value)}
                            error={errors.expected_date}
                        />

                        <Select
                            label="Delivery Warehouse *"
                            name="delivery_warehouse_id"
                            value={data.delivery_warehouse_id}
                            onChange={e => setData('delivery_warehouse_id', e.target.value)}
                            options={warehouseOptions}
                            error={errors.delivery_warehouse_id}
                        />

                        <Input
                            label="Payment Terms"
                            name="payment_terms"
                            value={data.payment_terms}
                            onChange={e => setData('payment_terms', e.target.value)}
                            error={errors.payment_terms}
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
                    </div>

                    <div className="mb-4 flex justify-between items-center border-b pb-2">
                        <h3 className="text-lg font-medium text-gray-900">Line Items</h3>
                        <button
                            type="button"
                            onClick={addLine}
                            className="text-sm bg-blue-50 text-blue-700 px-3 py-1 rounded hover:bg-blue-100"
                        >
                            + Add Item
                        </button>
                    </div>

                    <div className="space-y-4 mb-8">
                        {data.lines.map((line, index) => (
                            <div key={index} className="flex gap-4 items-end border-b pb-4 last:border-0 border-gray-100">
                                <div className="flex-grow grid grid-cols-12 gap-4">
                                    <div className="col-span-4">
                                        <Select
                                            label={index === 0 ? "Item" : ""}
                                            value={line.item_id}
                                            onChange={e => handleLineChange(index, 'item_id', e.target.value)}
                                            options={itemOptions}
                                            error={errors[`lines.${index}.item_id`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={index === 0 ? "Quantity" : ""}
                                            type="number"
                                            step="0.01"
                                            value={line.quantity}
                                            onChange={e => handleLineChange(index, 'quantity', e.target.value)}
                                            error={errors[`lines.${index}.quantity`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={index === 0 ? "Unit Price" : ""}
                                            type="number"
                                            step="0.01"
                                            value={line.unit_price}
                                            onChange={e => handleLineChange(index, 'unit_price', e.target.value)}
                                            error={errors[`lines.${index}.unit_price`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={index === 0 ? "Tax %" : ""}
                                            type="number"
                                            step="0.01"
                                            value={line.tax_rate}
                                            onChange={e => handleLineChange(index, 'tax_rate', e.target.value)}
                                            error={errors[`lines.${index}.tax_rate`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <div className="text-right text-sm font-medium pt-8 text-gray-600">
                                            ₹{((parseFloat(line.quantity) || 0) * (parseFloat(line.unit_price) || 0) * (1 + (parseFloat(line.tax_rate) || 0) / 100)).toFixed(2)}
                                        </div>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => removeLine(index)}
                                    className="mb-2 text-red-600 hover:text-red-800 p-2"
                                    title="Remove line"
                                >
                                    🗑
                                </button>
                            </div>
                        ))}
                        {errors.lines && <div className="text-red-500 text-sm mt-1">{errors.lines}</div>}
                    </div>

                    <div className="flex justify-end border-t pt-4">
                        <div className="w-64 space-y-2">
                            <div className="flex justify-between text-sm text-gray-600">
                                <span>Subtotal:</span>
                                <span>₹{totals.subtotal.toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between text-sm text-gray-600">
                                <span>Tax:</span>
                                <span>₹{totals.taxTotal.toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between text-lg font-bold text-gray-900 border-t pt-2 mt-2">
                                <span>Total:</span>
                                <span>₹{totals.total.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>

                    <div className="flex justify-end gap-4 mt-8">
                        <Link
                            href={route('procurement.purchase-orders')}
                            className="bg-white border border-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-indigo-600 border border-transparent text-white font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            {processing ? 'Creating...' : 'Create Purchase Order'}
                        </button>
                    </div>
                </form>
            </div>
        </MainLayout>
    );
}
