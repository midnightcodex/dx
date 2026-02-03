import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const scheduleData = [
    { id: 1, product: 'Steel Brackets A1', machine: 'CNC-01', shift: 'Morning', quantity: 200, startTime: '06:00', endTime: '14:00', status: 'Running' },
    { id: 2, product: 'Aluminum Plates B2', machine: 'Press-02', shift: 'Morning', quantity: 350, startTime: '06:00', endTime: '14:00', status: 'Running' },
    { id: 3, product: 'Copper Connectors', machine: 'CNC-02', shift: 'Afternoon', quantity: 150, startTime: '14:00', endTime: '22:00', status: 'Scheduled' },
    { id: 4, product: 'Plastic Housings', machine: 'Mold-01', shift: 'Afternoon', quantity: 400, startTime: '14:00', endTime: '22:00', status: 'Scheduled' },
];

const machines = [
    { name: 'CNC-01', status: 'Running', utilization: 92, currentJob: 'Steel Brackets A1' },
    { name: 'CNC-02', status: 'Idle', utilization: 0, currentJob: '-' },
    { name: 'Press-02', status: 'Running', utilization: 85, currentJob: 'Aluminum Plates B2' },
    { name: 'Mold-01', status: 'Maintenance', utilization: 0, currentJob: 'Scheduled PM' },
];

export default function Production() {
    const [showModal, setShowModal] = useState(false);
    const [formData, setFormData] = useState({ workOrder: '', machine: '', shift: '', operator: '', plannedQty: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });
    const handleSubmit = () => {
        alert('Production Scheduled!\n' + JSON.stringify(formData, null, 2));
        setShowModal(false);
        setFormData({ workOrder: '', machine: '', shift: '', operator: '', plannedQty: '' });
    };

    return (
        <MainLayout title="Production Planning" subtitle="Schedule and monitor production">
            <div className="stats-grid">
                <StatsCard icon="🏭" value={4} label="Machines Active" trend="2 idle" variant="primary" />
                <StatsCard icon="📊" value="78%" label="Overall Utilization" trend="Above target" trendDirection="up" variant="success" />
                <StatsCard icon="📅" value={8} label="Jobs Scheduled Today" variant="primary" />
                <StatsCard icon="⏱️" value="6.5 hrs" label="Avg Cycle Time" trend="0.5 hrs faster" trendDirection="up" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Schedule Production
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📊 Capacity View
                </button>
            </div>

            <div className="dashboard-grid">
                <MachineStatus machines={machines} />
                <TodaySchedule data={scheduleData} />
            </div>

            <GanttChart />

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title="Schedule Production" size="lg" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Schedule</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Work Order" name="workOrder" value={formData.workOrder} onChange={handleInputChange} required options={[
                        { value: 'wo-001', label: 'WO-2026-001 - Steel Brackets A1' },
                        { value: 'wo-002', label: 'WO-2026-002 - Aluminum Plates B2' },
                    ]} />
                    <Select label="Machine" name="machine" value={formData.machine} onChange={handleInputChange} required options={[
                        { value: 'cnc-01', label: 'CNC-01' },
                        { value: 'cnc-02', label: 'CNC-02' },
                        { value: 'press-02', label: 'Press-02' },
                        { value: 'mold-01', label: 'Mold-01' },
                    ]} />
                    <Select label="Shift" name="shift" value={formData.shift} onChange={handleInputChange} required options={[
                        { value: 'morning', label: 'Morning (06:00 - 14:00)' },
                        { value: 'afternoon', label: 'Afternoon (14:00 - 22:00)' },
                        { value: 'night', label: 'Night (22:00 - 06:00)' },
                    ]} />
                    <Input label="Operator" name="operator" value={formData.operator} onChange={handleInputChange} placeholder="Operator name" />
                    <Input label="Planned Quantity" type="number" name="plannedQty" value={formData.plannedQty} onChange={handleInputChange} required placeholder="Qty to produce" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function MachineStatus({ machines }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Machine Status</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {machines.map((m, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '10px', height: '10px', borderRadius: '50%', backgroundColor: m.status === 'Running' ? 'var(--color-success)' : m.status === 'Idle' ? 'var(--color-gray-400)' : 'var(--color-warning)', marginRight: '12px' }} />
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, fontSize: '14px' }}>{m.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{m.currentJob}</div>
                        </div>
                        <div style={{ textAlign: 'right' }}>
                            <div style={{ fontWeight: 600, fontSize: '14px' }}>{m.utilization}%</div>
                            <div style={{ fontSize: '12px', color: m.status === 'Running' ? 'var(--color-success)' : m.status === 'Maintenance' ? 'var(--color-warning)' : 'var(--color-gray-500)' }}>{m.status}</div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function TodaySchedule({ data }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Today's Schedule</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {data.map((item, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', backgroundColor: item.status === 'Running' ? 'var(--color-primary-light)' : 'var(--color-gray-50)' }}>
                        <div style={{ width: '80px', fontSize: '12px', color: 'var(--color-gray-500)' }}>{item.startTime} - {item.endTime}</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 500, fontSize: '14px' }}>{item.product}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{item.machine} • {item.quantity} units</div>
                        </div>
                        <span style={{ padding: '4px 8px', borderRadius: '8px', fontSize: '11px', fontWeight: 500, backgroundColor: item.status === 'Running' ? 'var(--color-success-light)' : 'var(--color-gray-200)', color: item.status === 'Running' ? 'var(--color-success)' : 'var(--color-gray-600)' }}>
                            {item.status}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function GanttChart() {
    const hours = ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'];

    return (
        <div className="chart-container" style={{ marginTop: '24px' }}>
            <div className="chart-header"><h3 className="chart-title">Production Timeline</h3></div>
            <div style={{ overflowX: 'auto' }}>
                <div style={{ display: 'flex', borderBottom: '1px solid var(--color-gray-200)', paddingBottom: '8px', marginBottom: '12px' }}>
                    <div style={{ width: '120px', flexShrink: 0 }}></div>
                    {hours.map((h, idx) => (
                        <div key={idx} style={{ flex: 1, fontSize: '11px', color: 'var(--color-gray-500)', minWidth: '60px' }}>{h}</div>
                    ))}
                </div>
                {['CNC-01', 'CNC-02', 'Press-02', 'Mold-01'].map((machine, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', marginBottom: '8px' }}>
                        <div style={{ width: '120px', flexShrink: 0, fontSize: '13px', fontWeight: 500 }}>{machine}</div>
                        <div style={{ flex: 1, height: '32px', backgroundColor: 'var(--color-gray-100)', borderRadius: '4px', position: 'relative' }}>
                            {idx === 0 && <div style={{ position: 'absolute', left: '0%', width: '50%', height: '100%', backgroundColor: 'var(--color-primary)', borderRadius: '4px', display: 'flex', alignItems: 'center', paddingLeft: '8px', fontSize: '11px', color: 'white' }}>Steel Brackets</div>}
                            {idx === 2 && <div style={{ position: 'absolute', left: '0%', width: '50%', height: '100%', backgroundColor: 'var(--color-success)', borderRadius: '4px', display: 'flex', alignItems: 'center', paddingLeft: '8px', fontSize: '11px', color: 'white' }}>Aluminum Plates</div>}
                            {idx === 1 && <div style={{ position: 'absolute', left: '50%', width: '50%', height: '100%', backgroundColor: 'var(--color-info)', borderRadius: '4px', display: 'flex', alignItems: 'center', paddingLeft: '8px', fontSize: '11px', color: 'white' }}>Copper Connectors</div>}
                            {idx === 3 && <div style={{ position: 'absolute', left: '50%', width: '50%', height: '100%', backgroundColor: 'var(--color-warning)', borderRadius: '4px', display: 'flex', alignItems: 'center', paddingLeft: '8px', fontSize: '11px', color: 'white' }}>Plastic Housings</div>}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
