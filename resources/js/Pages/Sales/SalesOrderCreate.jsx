import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import MainLayout from '../../Components/Layout/MainLayout';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

export default function SalesOrderCreate({ customers, items, warehouses }) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: '',
        order_date: new Date().toISOString().split('T')[0],
        expected_ship_date: '',
        notes: '',
        lines: [
            { item_id: '', quantity: 1, unit_price: 0, tax_rate: 0, line_total: 0 }
        ]
    });

    // Helper: Calculate totals
    const calculateTotals = () => {
        return data.lines.reduce((acc, line) => {
            const qty = parseFloat(line.quantity) || 0;
            const price = parseFloat(line.unit_price) || 0;
            const tax = parseFloat(line.tax_rate) || 0;
            const sub = qty * price;
            return {
                subtotal: acc.subtotal + sub,
                tax: acc.tax + (sub * (tax / 100)),
                total: acc.total + (sub * (1 + tax / 100))
            };
        }, { subtotal: 0, tax: 0, total: 0 });
    };

    const totals = calculateTotals();

    const handleLineChange = (index, field, value) => {
        const newLines = [...data.lines];
        newLines[index][field] = value;

        // Auto-fill price if item selected
        if (field === 'item_id') {
            const item = items.find(i => i.id === value);
            if (item) {
                newLines[index].unit_price = item.sale_price || 0;
            }
        }

        setData('lines', newLines);
    };

    const addLine = () => {
        setData('lines', [
            ...data.lines,
            { item_id: '', quantity: 1, unit_price: 0, tax_rate: 0 }
        ]);
    };

    const removeLine = (index) => {
        if (data.lines.length > 1) {
            setData('lines', data.lines.filter((_, i) => i !== index));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('sales.orders.store'));
    };

    const customerOptions = customers.map(c => ({ value: c.id, label: c.name }));
    const itemOptions = items.map(i => ({ value: i.id, label: `${i.item_code} - ${i.name} (₹${i.sale_price})` }));

    return (
        <MainLayout title="Create Sales Order">
            <Head title="Create Order" />

            <form onSubmit={handleSubmit} className="max-w-7xl mx-auto py-6">
                <div className="bg-white shadow rounded-lg p-6 mb-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Customer & Dates</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <Select
                            label="Customer *"
                            name="customer_id"
                            value={data.customer_id}
                            onChange={(e) => setData('customer_id', e.target.value)}
                            options={customerOptions}
                            error={errors.customer_id}
                        />
                        <Input
                            label="Order Date *"
                            type="date"
                            value={data.order_date}
                            onChange={(e) => setData('order_date', e.target.value)}
                            error={errors.order_date}
                        />
                        <Input
                            label="Expected Ship Date"
                            type="date"
                            value={data.expected_ship_date}
                            onChange={(e) => setData('expected_ship_date', e.target.value)}
                            error={errors.expected_ship_date}
                        />
                    </div>
                </div>

                <div className="bg-white shadow rounded-lg p-6 mb-6">
                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-medium text-gray-900">Order Items</h3>
                        <button type="button" onClick={addLine} className="text-sm text-indigo-600 hover:text-indigo-900 font-medium">+ Add Item</button>
                    </div>

                    <div className="space-y-4">
                        {data.lines.map((line, idx) => (
                            <div key={idx} className="flex gap-4 items-end border-b pb-4">
                                <div className="flex-grow grid grid-cols-12 gap-4">
                                    <div className="col-span-5">
                                        <Select
                                            label={idx === 0 ? "Item" : ""}
                                            value={line.item_id}
                                            onChange={(e) => handleLineChange(idx, 'item_id', e.target.value)}
                                            options={itemOptions}
                                            error={errors[`lines.${idx}.item_id`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={idx === 0 ? "Quantity" : ""}
                                            type="number"
                                            step="0.01"
                                            value={line.quantity}
                                            onChange={(e) => handleLineChange(idx, 'quantity', e.target.value)}
                                            error={errors[`lines.${idx}.quantity`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={idx === 0 ? "Price" : ""}
                                            type="number"
                                            step="0.01"
                                            value={line.unit_price}
                                            onChange={(e) => handleLineChange(idx, 'unit_price', e.target.value)}
                                            error={errors[`lines.${idx}.unit_price`]}
                                        />
                                    </div>
                                    <div className="col-span-2">
                                        <Input
                                            label={idx === 0 ? "Tax %" : ""}
                                            type="number"
                                            value={line.tax_rate}
                                            onChange={(e) => handleLineChange(idx, 'tax_rate', e.target.value)}
                                        />
                                    </div>
                                    <div className="col-span-1 text-right self-center pt-4 font-medium">
                                        {((line.quantity * line.unit_price) * (1 + (line.tax_rate / 100))).toFixed(2)}
                                    </div>
                                </div>
                                <button type="button" onClick={() => removeLine(idx)} className="text-red-500 hover:text-red-700 pb-2">🗑</button>
                            </div>
                        ))}
                    </div>

                    <div className="flex justify-end mt-6">
                        <div className="w-64 space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-gray-500">Subtotal</span>
                                <span className="font-medium">₹{totals.subtotal.toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-500">Tax</span>
                                <span className="font-medium">₹{totals.tax.toFixed(2)}</span>
                            </div>
                            <div className="flex justify-between border-t pt-2 text-base font-bold text-gray-900">
                                <span>Total</span>
                                <span>₹{totals.total.toFixed(2)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="flex justify-end gap-3">
                    <Link href={route('sales.orders')} className="px-4 py-2 bg-white border border-gray-300 rounded text-gray-700 hover:bg-gray-50">Cancel</Link>
                    <button type="submit" disabled={processing} className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
                        {processing ? 'Creating...' : 'Create Order'}
                    </button>
                </div>
            </form>
        </MainLayout>
    );
}
