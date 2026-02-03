import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const deliveryData = [
    { id: 1, doNumber: 'DO-2026-042', soNumber: 'SO-2026-083', customer: 'Tech Solutions Ltd', dispatchDate: '2026-02-03', items: 5, status: 'In Transit', vehicle: 'MH-12-AB-1234', driver: 'Ramesh Kumar' },
    { id: 2, doNumber: 'DO-2026-041', soNumber: 'SO-2026-082', customer: 'Global Exports', dispatchDate: '2026-01-30', items: 1, status: 'Delivered', vehicle: 'MH-12-CD-5678', driver: 'Suresh Patil' },
    { id: 3, doNumber: 'DO-2026-040', soNumber: 'SO-2026-081', customer: 'Metro Traders', dispatchDate: '2026-01-26', items: 3, status: 'Delivered', vehicle: 'MH-12-EF-9012', driver: 'Ajay Singh' },
];

const pendingDispatch = [
    { id: 1, soNumber: 'SO-2026-085', customer: 'ABC Industries', items: 3, scheduledDate: '2026-02-10', priority: 'High' },
    { id: 2, soNumber: 'SO-2026-084', customer: 'XYZ Manufacturing', items: 2, scheduledDate: '2026-02-08', priority: 'Normal' },
];

const deliveryColumns = [
    { header: 'DO Number', accessor: 'doNumber' },
    { header: 'SO Number', accessor: 'soNumber' },
    { header: 'Customer', accessor: 'customer' },
    { header: 'Dispatch Date', accessor: 'dispatchDate' },
    { header: 'Items', accessor: 'items' },
    { header: 'Vehicle', accessor: 'vehicle' },
    { header: 'Driver', accessor: 'driver' },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { 'In Transit': { bg: '#EDE9FE', color: '#7C3AED' }, Delivered: { bg: '#D1FAE5', color: '#059669' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function Delivery() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ soNumber: '', vehicle: '', driver: '', dispatchDate: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Delivery Note Created!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ soNumber: '', vehicle: '', driver: '', dispatchDate: '' });
    };

    const inTransit = deliveryData.filter(d => d.status === 'In Transit');
    const delivered = deliveryData.filter(d => d.status === 'Delivered');

    return (
        <MainLayout title="Delivery Notes" subtitle="Manage shipments and dispatch">
            <div className="stats-grid">
                <StatsCard icon="📦" value={deliveryData.length} label="Total Deliveries" trend="This month" variant="primary" />
                <StatsCard icon="🚚" value={inTransit.length} label="In Transit" variant="warning" />
                <StatsCard icon="✅" value={delivered.length} label="Delivered" variant="success" />
                <StatsCard icon="📋" value={pendingDispatch.length} label="Pending Dispatch" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Create Delivery Note
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📍 Track Shipments
                </button>
            </div>

            <div className="dashboard-grid">
                <PendingDispatchList data={pendingDispatch} />
                <DeliveryTracking />
            </div>

            <DataTable columns={deliveryColumns} data={deliveryData} title="Delivery Notes" />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Create Delivery Note" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Delivery Note</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Sales Order" name="soNumber" value={formData.soNumber} onChange={handleInputChange} required options={[
                        { value: 'so-085', label: 'SO-2026-085 - ABC Industries' },
                        { value: 'so-084', label: 'SO-2026-084 - XYZ Manufacturing' },
                    ]} />
                    <Input label="Dispatch Date" type="date" name="dispatchDate" value={formData.dispatchDate} onChange={handleInputChange} required />
                    <Select label="Vehicle" name="vehicle" value={formData.vehicle} onChange={handleInputChange} required options={[
                        { value: 'v1', label: 'MH-12-AB-1234' },
                        { value: 'v2', label: 'MH-12-CD-5678' },
                        { value: 'v3', label: 'MH-12-EF-9012' },
                    ]} />
                    <Select label="Driver" name="driver" value={formData.driver} onChange={handleInputChange} required options={[
                        { value: 'd1', label: 'Ramesh Kumar' },
                        { value: 'd2', label: 'Suresh Patil' },
                        { value: 'd3', label: 'Ajay Singh' },
                    ]} />
                </div>
            </Modal>
        </MainLayout>
    );
}

function PendingDispatchList({ data }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Pending Dispatch</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {data.map((d, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{d.customer}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d.soNumber} • {d.items} items</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                            <div style={{ fontSize: '14px', fontWeight: 500 }}>{d.scheduledDate}</div>
                            <span style={{ fontSize: '11px', color: d.priority === 'High' ? 'var(--color-danger)' : 'var(--color-gray-500)' }}>● {d.priority}</span>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function DeliveryTracking() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Live Tracking: DO-2026-042</h3></div>
            <div style={{ padding: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '20px' }}>
                    <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '24px' }}>🚚</div>
                    <div>
                        <div style={{ fontWeight: 600 }}>MH-12-AB-1234</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Driver: Ramesh Kumar</div>
                    </div>
                </div>
                <div style={{ display: 'flex', gap: '8px', marginBottom: '12px' }}>
                    {['Dispatched', 'In Transit', 'Out for Delivery', 'Delivered'].map((step, idx) => (
                        <div key={idx} style={{ flex: 1, height: '4px', borderRadius: '2px', backgroundColor: idx < 2 ? 'var(--color-primary)' : 'var(--color-gray-200)' }} />
                    ))}
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: '11px', color: 'var(--color-gray-500)' }}>
                    <span>Dispatched</span>
                    <span style={{ fontWeight: 500, color: 'var(--color-primary)' }}>In Transit</span>
                    <span>Out for Delivery</span>
                    <span>Delivered</span>
                </div>
            </div>
        </div>
    );
}
