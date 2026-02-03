import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const workstationData = [
    { id: 1, name: 'CNC Machine 01', code: 'WS-001', type: 'Machining', costPerHour: 1200, capacity: '8 hrs/shift', location: 'Shop Floor A', status: 'Active' },
    { id: 2, name: 'Assembly Line 1', code: 'WS-002', type: 'Assembly', costPerHour: 800, capacity: '8 hrs/shift', location: 'Shop Floor B', status: 'Active' },
    { id: 3, name: 'Painting Booth', code: 'WS-003', type: 'Finishing', costPerHour: 1500, capacity: '6 hrs/shift', location: 'Paint Shop', status: 'Maintenance' },
    { id: 4, name: 'Packaging Station', code: 'WS-004', type: 'Packaging', costPerHour: 400, capacity: '12 hrs/shift', location: 'Logistics', status: 'Active' },
    { id: 5, name: 'Welding Station', code: 'WS-005', type: 'Fabrication', costPerHour: 950, capacity: '8 hrs/shift', location: 'Shop Floor A', status: 'Active' },
];

const workstationColumns = [
    { header: 'Station Code', accessor: 'code' },
    { header: 'Name', accessor: 'name' },
    { header: 'Type', accessor: 'type' },
    { header: 'Cost/Hr', accessor: 'costPerHour', render: (val) => `₹${val}` },
    { header: 'Location', accessor: 'location' },
    {
        header: 'Status', accessor: 'status', render: (val) => (
            <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: val === 'Active' ? '#D1FAE5' : val === 'Maintenance' ? '#FEE2E2' : '#F3F4F6', color: val === 'Active' ? '#059669' : val === 'Maintenance' ? '#DC2626' : '#6B7280' }}>
                {val}
            </span>
        )
    },
];

export default function Workstations() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ name: '', code: '', type: '', costPerHour: '', location: '', capacity: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Workstation Added!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ name: '', code: '', type: '', costPerHour: '', location: '', capacity: '' });
    };

    const activeStations = workstationData.filter(w => w.status === 'Active');
    const maintenanceStations = workstationData.filter(w => w.status === 'Maintenance');

    return (
        <MainLayout title="Workstations" subtitle="Manage production resources and centers">
            <div className="stats-grid">
                <StatsCard icon="🏭" value={workstationData.length} label="Total Workstations" variant="primary" />
                <StatsCard icon="✅" value={activeStations.length} label="Operational" variant="success" />
                <StatsCard icon="🔧" value={maintenanceStations.length} label="In Maintenance" variant="danger" />
                <StatsCard icon="⚡" value="85%" label="Avg Utilization" variant="warning" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Workstation
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📤 Export List
                </button>
            </div>

            <div className="dashboard-grid">
                <UtilizationChart />
                <WorkstationTypes />
            </div>

            <DataTable columns={workstationColumns} data={workstationData} title="Workstation Directory" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Add New Workstation" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Station</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Station Name" name="name" value={formData.name} onChange={handleInputChange} required placeholder="e.g. CNC Machine 02" />
                    <Input label="Station Code" name="code" value={formData.code} onChange={handleInputChange} required placeholder="e.g. WS-006" />
                    <Select label="Type" name="type" value={formData.type} onChange={handleInputChange} required options={[
                        { value: 'machining', label: 'Machining' },
                        { value: 'assembly', label: 'Assembly' },
                        { value: 'finishing', label: 'Finishing' },
                        { value: 'packaging', label: 'Packaging' },
                        { value: 'fabrication', label: 'Fabrication' },
                        { value: 'inspection', label: 'Quality Inspection' },
                    ]} />
                    <Input label="Hourly Cost (₹)" type="number" name="costPerHour" value={formData.costPerHour} onChange={handleInputChange} required placeholder="0.00" />
                    <Input label="Capacity per Shift" name="capacity" value={formData.capacity} onChange={handleInputChange} placeholder="e.g. 8 hours" />
                    <Input label="Location" name="location" value={formData.location} onChange={handleInputChange} placeholder="Factory Floor Location" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function UtilizationChart() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Station Utilization</h3></div>
            <div style={{ padding: '16px', display: 'flex', alignItems: 'center', justifyContent: 'center', height: '150px', color: 'var(--color-gray-500)' }}>
                [Utilization Graph Placeholder]
            </div>
        </div>
    );
}

function WorkstationTypes() {
    const types = [
        { name: 'Machining', count: 5 },
        { name: 'Assembly', count: 3 },
        { name: 'Finishing', count: 2 },
    ];
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Station Types</h3></div>
            <div style={{ padding: '16px' }}>
                {types.map((t, i) => (
                    <div key={i} style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '8px' }}>
                        <span>{t.name}</span>
                        <span style={{ fontWeight: 600 }}>{t.count}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}
