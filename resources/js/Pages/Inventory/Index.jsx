import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import StockPieChart from '../../Components/Charts/StockPieChart';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const stockSummary = [
    { id: 1, itemCode: 'RM-001', name: 'Raw Steel Sheet', category: 'Raw Material', warehouse: 'Main', quantity: 5000, unit: 'kg', value: 250000, reorderLevel: 1000, status: 'In Stock' },
    { id: 2, itemCode: 'RM-002', name: 'Aluminum Sheets', category: 'Raw Material', warehouse: 'Main', quantity: 2500, unit: 'sheets', value: 187500, reorderLevel: 500, status: 'In Stock' },
    { id: 3, itemCode: 'RM-003', name: 'Copper Wire 2mm', category: 'Raw Material', warehouse: 'Production', quantity: 150, unit: 'm', value: 15000, reorderLevel: 200, status: 'Low Stock' },
    { id: 4, itemCode: 'FG-001', name: 'Steel Brackets A1', category: 'Finished Goods', warehouse: 'Finished Goods', quantity: 1200, unit: 'pcs', value: 72000, reorderLevel: 300, status: 'In Stock' },
    { id: 5, itemCode: 'FG-002', name: 'Aluminum Plates B2', category: 'Finished Goods', warehouse: 'Finished Goods', quantity: 450, unit: 'pcs', value: 45000, reorderLevel: 500, status: 'Low Stock' },
    { id: 6, itemCode: 'PK-001', name: 'Packaging Boxes', category: 'Packaging', warehouse: 'Main', quantity: 3000, unit: 'pcs', value: 30000, reorderLevel: 500, status: 'In Stock' },
];

const stockColumns = [
    { header: 'Item Code', accessor: 'itemCode' },
    { header: 'Name', accessor: 'name' },
    { header: 'Category', accessor: 'category' },
    { header: 'Warehouse', accessor: 'warehouse' },
    { header: 'Quantity', accessor: 'quantity', render: (val, row) => `${val.toLocaleString()} ${row.unit}` },
    { header: 'Value', accessor: 'value', render: (val) => `₹${val.toLocaleString()}` },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const isLow = val === 'Low Stock';
            return (
                <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: isLow ? '#FEE2E2' : '#D1FAE5', color: isLow ? '#DC2626' : '#059669' }}>
                    {val}
                </span>
            );
        }
    },
];

export default function InventoryIndex() {
    const [showAddModal, setShowAddModal] = useState(false);
    const [showLedgerModal, setShowLedgerModal] = useState(false);
    const [showTransferModal, setShowTransferModal] = useState(false);
    const [formData, setFormData] = useState({ itemCode: '', name: '', category: '', warehouse: '', quantity: '', unit: '', unitPrice: '', reorderLevel: '' });
    const [ledgerData, setLedgerData] = useState({ item: '', dateFrom: '', dateTo: '', transactionType: '' });
    const [transferData, setTransferData] = useState({ item: '', fromWarehouse: '', toWarehouse: '', quantity: '', reason: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

    const handleSubmit = () => {
        alert('Stock Added!\n' + JSON.stringify(formData, null, 2));
        setShowAddModal(false);
        setFormData({ itemCode: '', name: '', category: '', warehouse: '', quantity: '', unit: '', unitPrice: '', reorderLevel: '' });
    };

    const handleLedgerSubmit = () => {
        alert('Viewing Stock Ledger for:\n' + JSON.stringify(ledgerData, null, 2));
        setShowLedgerModal(false);
        setLedgerData({ item: '', dateFrom: '', dateTo: '', transactionType: '' });
    };

    const handleTransferSubmit = () => {
        alert('Stock Transfer Initiated!\n' + JSON.stringify(transferData, null, 2));
        setShowTransferModal(false);
        setTransferData({ item: '', fromWarehouse: '', toWarehouse: '', quantity: '', reason: '' });
    };

    const handleFilter = (filter) => {
        alert(`Filter applied: ${filter || 'None'}`);
    };

    const handleSort = (sort) => {
        alert(`Sort by: ${sort || 'None'}`);
    };

    const lowStock = stockSummary.filter(s => s.status === 'Low Stock');
    const totalValue = stockSummary.reduce((sum, s) => sum + s.value, 0);

    const tabs = [
        { label: 'All Items', content: <DataTable columns={stockColumns} data={stockSummary} title="Inventory Items" onFilter={handleFilter} onSort={handleSort} onAdd={() => setShowAddModal(true)} /> },
        { label: 'Low Stock', badge: lowStock.length, content: <DataTable columns={stockColumns} data={lowStock} title="Low Stock Items" onFilter={handleFilter} onSort={handleSort} onAdd={() => setShowAddModal(true)} /> },
        { label: 'Movements', content: <RecentMovements /> },
    ];

    return (
        <MainLayout title="Inventory" subtitle="Stock management and tracking">
            <div className="stats-grid">
                <StatsCard icon="📦" value={stockSummary.length} label="Total Items" trend="6 active items" trendDirection="up" variant="primary" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Total Stock Value" trend="5% increase" trendDirection="up" variant="success" />
                <StatsCard icon="⚠️" value={lowStock.length} label="Low Stock Alerts" trend="Needs attention" trendDirection="down" variant="danger" />
                <StatsCard icon="🏭" value={3} label="Warehouses" variant="primary" />
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button
                    onClick={() => router.visit('/inventory/items')}
                    style={{
                        display: 'flex', alignItems: 'center', gap: '8px',
                        padding: '12px 20px', borderRadius: '8px', border: 'none',
                        backgroundColor: 'var(--color-primary)', color: 'white',
                        fontWeight: 500, cursor: 'pointer', fontSize: '14px'
                    }}
                >
                    📦 Item Master
                </button>
                <button onClick={() => setShowAddModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Stock
                </button>
                <button onClick={() => setShowLedgerModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📋 Stock Ledger
                </button>
                <button onClick={() => setShowTransferModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔄 Stock Transfer
                </button>
            </div>

            <div className="dashboard-grid">
                <StockPieChart />
                <StockByWarehouse />
            </div>

            <Tabs tabs={tabs} />

            {/* Add Stock Modal */}
            <Modal isOpen={showAddModal} onClose={() => setShowAddModal(false)} title="Add Stock" size="lg" footer={
                <>
                    <button onClick={() => setShowAddModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Stock</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Item Code" name="itemCode" value={formData.itemCode} onChange={handleInputChange} required placeholder="e.g. RM-004" />
                    <Input label="Item Name" name="name" value={formData.name} onChange={handleInputChange} required placeholder="Enter item name" />
                    <Select label="Category" name="category" value={formData.category} onChange={handleInputChange} required options={[{ value: 'raw-material', label: 'Raw Material' }, { value: 'finished-goods', label: 'Finished Goods' }, { value: 'packaging', label: 'Packaging' }, { value: 'consumables', label: 'Consumables' }]} />
                    <Select label="Warehouse" name="warehouse" value={formData.warehouse} onChange={handleInputChange} required options={[{ value: 'main', label: 'Main Warehouse' }, { value: 'production', label: 'Production' }, { value: 'finished-goods', label: 'Finished Goods' }]} />
                    <Input label="Quantity" type="number" name="quantity" value={formData.quantity} onChange={handleInputChange} required placeholder="Enter quantity" />
                    <Select label="Unit" name="unit" value={formData.unit} onChange={handleInputChange} required options={[{ value: 'kg', label: 'Kilograms (kg)' }, { value: 'pcs', label: 'Pieces (pcs)' }, { value: 'm', label: 'Meters (m)' }, { value: 'sheets', label: 'Sheets' }]} />
                    <Input label="Unit Price (₹)" type="number" name="unitPrice" value={formData.unitPrice} onChange={handleInputChange} required placeholder="Enter unit price" />
                    <Input label="Reorder Level" type="number" name="reorderLevel" value={formData.reorderLevel} onChange={handleInputChange} placeholder="Minimum stock level" />
                </div>
            </Modal>

            {/* Stock Ledger Modal */}
            <Modal isOpen={showLedgerModal} onClose={() => setShowLedgerModal(false)} title="Stock Ledger" size="lg" footer={
                <>
                    <button onClick={() => setShowLedgerModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleLedgerSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>View Ledger</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Select Item" name="item" value={ledgerData.item} onChange={(e) => setLedgerData({ ...ledgerData, item: e.target.value })} required options={stockSummary.map(s => ({ value: s.itemCode, label: `${s.itemCode} - ${s.name}` }))} />
                    <Select label="Transaction Type" name="transactionType" value={ledgerData.transactionType} onChange={(e) => setLedgerData({ ...ledgerData, transactionType: e.target.value })} options={[{ value: '', label: 'All Transactions' }, { value: 'in', label: 'Stock In' }, { value: 'out', label: 'Stock Out' }, { value: 'transfer', label: 'Transfer' }]} />
                    <Input label="Date From" type="date" name="dateFrom" value={ledgerData.dateFrom} onChange={(e) => setLedgerData({ ...ledgerData, dateFrom: e.target.value })} />
                    <Input label="Date To" type="date" name="dateTo" value={ledgerData.dateTo} onChange={(e) => setLedgerData({ ...ledgerData, dateTo: e.target.value })} />
                </div>
                <div style={{ marginTop: '24px', padding: '16px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                    <h4 style={{ marginBottom: '12px', fontSize: '14px', fontWeight: 600 }}>Recent Transactions Preview</h4>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', padding: '8px 0', borderBottom: '1px solid var(--color-gray-200)' }}>
                            <span>2026-02-02 - GRN-2026-042</span>
                            <span style={{ color: 'var(--color-success)' }}>+500 kg</span>
                        </div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', padding: '8px 0', borderBottom: '1px solid var(--color-gray-200)' }}>
                            <span>2026-02-01 - WO-2026-015</span>
                            <span style={{ color: 'var(--color-danger)' }}>-200 kg</span>
                        </div>
                        <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '13px', padding: '8px 0' }}>
                            <span>2026-01-30 - Transfer</span>
                            <span style={{ color: 'var(--color-warning)' }}>→ Production</span>
                        </div>
                    </div>
                </div>
            </Modal>

            {/* Stock Transfer Modal */}
            <Modal isOpen={showTransferModal} onClose={() => setShowTransferModal(false)} title="Stock Transfer" size="lg" footer={
                <>
                    <button onClick={() => setShowTransferModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleTransferSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Transfer Stock</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Select Item" name="item" value={transferData.item} onChange={(e) => setTransferData({ ...transferData, item: e.target.value })} required options={stockSummary.map(s => ({ value: s.itemCode, label: `${s.itemCode} - ${s.name} (${s.quantity} ${s.unit})` }))} />
                    <Input label="Quantity to Transfer" type="number" name="quantity" value={transferData.quantity} onChange={(e) => setTransferData({ ...transferData, quantity: e.target.value })} required placeholder="Enter quantity" />
                    <Select label="From Warehouse" name="fromWarehouse" value={transferData.fromWarehouse} onChange={(e) => setTransferData({ ...transferData, fromWarehouse: e.target.value })} required options={[{ value: 'main', label: 'Main Warehouse' }, { value: 'production', label: 'Production' }, { value: 'finished-goods', label: 'Finished Goods' }]} />
                    <Select label="To Warehouse" name="toWarehouse" value={transferData.toWarehouse} onChange={(e) => setTransferData({ ...transferData, toWarehouse: e.target.value })} required options={[{ value: 'main', label: 'Main Warehouse' }, { value: 'production', label: 'Production' }, { value: 'finished-goods', label: 'Finished Goods' }]} />
                    <Input label="Reason/Notes" name="reason" value={transferData.reason} onChange={(e) => setTransferData({ ...transferData, reason: e.target.value })} placeholder="e.g. Production requirement" style={{ gridColumn: 'span 2' }} />
                </div>
            </Modal>
        </MainLayout>
    );
}

function StockByWarehouse() {
    const warehouses = [
        { name: 'Main Warehouse', items: 3, value: 467500 },
        { name: 'Production', items: 1, value: 15000 },
        { name: 'Finished Goods', items: 2, value: 117000 },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Stock by Warehouse</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {warehouses.map((wh, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px' }}>🏭</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{wh.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{wh.items} items</div>
                        </div>
                        <div style={{ fontWeight: 600, color: 'var(--color-gray-900)' }}>₹{(wh.value / 1000).toFixed(0)}K</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function RecentMovements() {
    const movements = [
        { type: 'IN', item: 'Raw Steel Sheet', quantity: '500 kg', reference: 'GRN-2026-042', date: '2026-02-02', time: '10:30 AM' },
        { type: 'OUT', item: 'Steel Brackets A1', quantity: '100 pcs', reference: 'DO-2026-018', date: '2026-02-02', time: '09:15 AM' },
        { type: 'IN', item: 'Aluminum Sheets', quantity: '200 sheets', reference: 'GRN-2026-041', date: '2026-02-01', time: '04:45 PM' },
        { type: 'OUT', item: 'Copper Wire 2mm', quantity: '50 m', reference: 'WO-2026-004', date: '2026-02-01', time: '02:30 PM' },
    ];

    return (
        <div style={{ padding: '16px' }}>
            {movements.map((m, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', marginBottom: '12px' }}>
                    <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: m.type === 'IN' ? 'var(--color-success-light)' : 'var(--color-danger-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px', color: m.type === 'IN' ? 'var(--color-success)' : 'var(--color-danger)', fontWeight: 600 }}>
                        {m.type === 'IN' ? '↓' : '↑'}
                    </div>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 500 }}>{m.item}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{m.reference}</div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                        <div style={{ fontWeight: 500, color: m.type === 'IN' ? 'var(--color-success)' : 'var(--color-danger)' }}>{m.type === 'IN' ? '+' : '-'}{m.quantity}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{m.date} {m.time}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}
