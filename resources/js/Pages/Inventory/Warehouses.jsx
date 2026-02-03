import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const warehouseData = [
    { id: 1, code: 'WH-001', name: 'Main Warehouse', type: 'Central', location: 'Building A, Ground Floor', capacity: '10,000 sqft', utilization: 72, items: 3, value: 467500, manager: 'John Smith' },
    { id: 2, code: 'WH-002', name: 'Production Floor', type: 'WIP', location: 'Building B, Floor 1', capacity: '5,000 sqft', utilization: 45, items: 1, value: 15000, manager: 'Sarah Jones' },
    { id: 3, code: 'WH-003', name: 'Finished Goods', type: 'FG', location: 'Building A, Floor 1', capacity: '8,000 sqft', utilization: 58, items: 2, value: 117000, manager: 'Mike Chen' },
];

const locationData = [
    { id: 1, warehouse: 'Main Warehouse', zone: 'A', rack: 'A-01', bin: 'A-01-01', item: 'Raw Steel Sheet', quantity: 2500, unit: 'kg' },
    { id: 2, warehouse: 'Main Warehouse', zone: 'A', rack: 'A-01', bin: 'A-01-02', item: 'Raw Steel Sheet', quantity: 2500, unit: 'kg' },
    { id: 3, warehouse: 'Main Warehouse', zone: 'A', rack: 'A-02', bin: 'A-02-01', item: 'Aluminum Sheets', quantity: 2500, unit: 'sheets' },
];

const warehouseColumns = [
    { header: 'Code', accessor: 'code' },
    { header: 'Name', accessor: 'name' },
    { header: 'Type', accessor: 'type' },
    { header: 'Location', accessor: 'location' },
    { header: 'Capacity', accessor: 'capacity' },
    {
        header: 'Utilization', accessor: 'utilization', render: (val) => (
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <div style={{ width: '60px', height: '6px', backgroundColor: 'var(--color-gray-200)', borderRadius: '3px', overflow: 'hidden' }}>
                    <div style={{ width: `${val}%`, height: '100%', backgroundColor: val > 80 ? 'var(--color-danger)' : val > 60 ? 'var(--color-warning)' : 'var(--color-success)' }} />
                </div>
                <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{val}%</span>
            </div>
        )
    },
    { header: 'Value', accessor: 'value', render: (val) => `₹${(val / 1000).toFixed(0)}K` },
    { header: 'Manager', accessor: 'manager' },
];

const locationColumns = [
    { header: 'Warehouse', accessor: 'warehouse' },
    { header: 'Zone', accessor: 'zone' },
    { header: 'Rack', accessor: 'rack' },
    { header: 'Bin', accessor: 'bin' },
    { header: 'Item', accessor: 'item' },
    { header: 'Quantity', accessor: 'quantity', render: (val, row) => `${val.toLocaleString()} ${row.unit}` },
];

export default function Warehouses() {
    const [showWarehouseModal, setShowWarehouseModal] = useState(false);
    const [showLocationModal, setShowLocationModal] = useState(false);
    const [warehouseForm, setWarehouseForm] = useState({ name: '', type: '', location: '', capacity: '', manager: '' });
    const [locationForm, setLocationForm] = useState({ warehouse: '', zone: '', rack: '', bin: '' });

    const handleWarehouseChange = (e) => setWarehouseForm({ ...warehouseForm, [e.target.name]: e.target.value });
    const handleLocationChange = (e) => setLocationForm({ ...locationForm, [e.target.name]: e.target.value });

    const totalValue = warehouseData.reduce((sum, w) => sum + w.value, 0);
    const avgUtilization = Math.round(warehouseData.reduce((sum, w) => sum + w.utilization, 0) / warehouseData.length);

    return (
        <MainLayout title="Warehouses" subtitle="Manage storage locations">
            <div className="stats-grid">
                <StatsCard icon="🏭" value={warehouseData.length} label="Total Warehouses" variant="primary" />
                <StatsCard icon="📊" value={`${avgUtilization}%`} label="Avg Utilization" trend="Optimal range" trendDirection="up" variant="success" />
                <StatsCard icon="💰" value={`₹${(totalValue / 100000).toFixed(1)}L`} label="Total Stock Value" variant="success" />
                <StatsCard icon="📍" value={locationData.length} label="Storage Locations" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowWarehouseModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Warehouse
                </button>
                <button onClick={() => setShowLocationModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📍 Add Location
                </button>
            </div>

            <WarehouseOverview warehouses={warehouseData} />
            <div style={{ marginTop: '24px' }}><DataTable columns={warehouseColumns} data={warehouseData} title="Warehouse List" /></div>
            <div style={{ marginTop: '24px' }}><DataTable columns={locationColumns} data={locationData} title="Bin Locations" actions={false} /></div>

            {/* Add Warehouse Modal */}
            <Modal isOpen={showWarehouseModal} onClose={() => setShowWarehouseModal(false)} title="Add Warehouse" size="lg" footer={
                <>
                    <button onClick={() => setShowWarehouseModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={() => { alert('Warehouse Added!'); setShowWarehouseModal(false); }} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Warehouse</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Warehouse Name" name="name" value={warehouseForm.name} onChange={handleWarehouseChange} required placeholder="e.g. Raw Materials Store" />
                    <Select label="Type" name="type" value={warehouseForm.type} onChange={handleWarehouseChange} required options={[
                        { value: 'central', label: 'Central' },
                        { value: 'wip', label: 'Work in Progress' },
                        { value: 'fg', label: 'Finished Goods' },
                        { value: 'quarantine', label: 'Quarantine' },
                    ]} />
                    <Input label="Location" name="location" value={warehouseForm.location} onChange={handleWarehouseChange} placeholder="e.g. Building C, Floor 2" />
                    <Input label="Capacity (sqft)" name="capacity" value={warehouseForm.capacity} onChange={handleWarehouseChange} placeholder="e.g. 5000" />
                    <Input label="Manager" name="manager" value={warehouseForm.manager} onChange={handleWarehouseChange} placeholder="Warehouse manager name" />
                </div>
            </Modal>

            {/* Add Location Modal */}
            <Modal isOpen={showLocationModal} onClose={() => setShowLocationModal(false)} title="Add Bin Location" size="md" footer={
                <>
                    <button onClick={() => setShowLocationModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={() => { alert('Location Added!'); setShowLocationModal(false); }} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Location</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Warehouse" name="warehouse" value={locationForm.warehouse} onChange={handleLocationChange} required options={[
                        { value: 'wh-001', label: 'Main Warehouse' },
                        { value: 'wh-002', label: 'Production Floor' },
                        { value: 'wh-003', label: 'Finished Goods' },
                    ]} />
                    <Input label="Zone" name="zone" value={locationForm.zone} onChange={handleLocationChange} required placeholder="e.g. A" />
                    <Input label="Rack" name="rack" value={locationForm.rack} onChange={handleLocationChange} required placeholder="e.g. A-01" />
                    <Input label="Bin" name="bin" value={locationForm.bin} onChange={handleLocationChange} required placeholder="e.g. A-01-01" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function WarehouseOverview({ warehouses }) {
    return (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '16px' }}>
            {warehouses.map((wh, idx) => (
                <div key={idx} className="chart-container" style={{ padding: '20px' }}>
                    <div style={{ display: 'flex', alignItems: 'center', marginBottom: '16px' }}>
                        <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px', fontSize: '24px' }}>🏭</div>
                        <div>
                            <div style={{ fontWeight: 600, fontSize: '16px' }}>{wh.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{wh.type} • {wh.code}</div>
                        </div>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                        <div style={{ padding: '12px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Utilization</div>
                            <div style={{ fontSize: '18px', fontWeight: 600 }}>{wh.utilization}%</div>
                        </div>
                        <div style={{ padding: '12px', backgroundColor: 'var(--color-gray-50)', borderRadius: '8px' }}>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Stock Value</div>
                            <div style={{ fontSize: '18px', fontWeight: 600 }}>₹{(wh.value / 1000).toFixed(0)}K</div>
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}
