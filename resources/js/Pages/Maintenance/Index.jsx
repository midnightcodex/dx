import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const machineData = [
    { id: 1, code: 'CNC-01', name: 'CNC Milling Machine', type: 'CNC', location: 'Shop Floor A', status: 'Running', lastMaintenance: '2026-01-15', nextPM: '2026-02-15', hours: 2450 },
    { id: 2, code: 'CNC-02', name: 'CNC Lathe', type: 'CNC', location: 'Shop Floor A', status: 'Idle', lastMaintenance: '2026-01-20', nextPM: '2026-02-20', hours: 1890 },
    { id: 3, code: 'PRESS-01', name: 'Hydraulic Press 100T', type: 'Press', location: 'Shop Floor B', status: 'Running', lastMaintenance: '2026-01-10', nextPM: '2026-02-10', hours: 3200 },
    { id: 4, code: 'WELD-01', name: 'MIG Welding Station', type: 'Welding', location: 'Shop Floor B', status: 'Under Maintenance', lastMaintenance: '2026-02-01', nextPM: '2026-03-01', hours: 1560 },
    { id: 5, code: 'MOLD-01', name: 'Injection Molding', type: 'Molding', location: 'Shop Floor C', status: 'Running', lastMaintenance: '2026-01-25', nextPM: '2026-02-25', hours: 4100 },
];

const pmSchedule = [
    { id: 1, machine: 'PRESS-01', task: 'Hydraulic Oil Change', dueDate: '2026-02-10', assignee: 'Vikram Singh', priority: 'High' },
    { id: 2, machine: 'CNC-01', task: 'Spindle Bearing Check', dueDate: '2026-02-15', assignee: 'Ajay Patel', priority: 'Normal' },
    { id: 3, machine: 'CNC-02', task: 'Coolant System Flush', dueDate: '2026-02-20', assignee: 'Vikram Singh', priority: 'Normal' },
];

const machineColumns = [
    { header: 'Code', accessor: 'code' },
    { header: 'Machine', accessor: 'name' },
    { header: 'Type', accessor: 'type' },
    { header: 'Location', accessor: 'location' },
    { header: 'Hours', accessor: 'hours', render: (val) => `${val.toLocaleString()} hrs` },
    { header: 'Next PM', accessor: 'nextPM' },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Running: { bg: '#D1FAE5', color: '#059669' }, Idle: { bg: '#F3F4F6', color: '#6B7280' }, 'Under Maintenance': { bg: '#FEF3C7', color: '#D97706' }, Breakdown: { bg: '#FEE2E2', color: '#DC2626' } };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function MaintenanceIndex() {
    const [showPMModal, setShowPMModal] = useState(false);
    const [showBreakdownModal, setShowBreakdownModal] = useState(false);
    const [showMachineModal, setShowMachineModal] = useState(false);
    const [pmData, setPmData] = useState({ machine: '', task: '', dueDate: '', assignee: '', priority: '' });
    const [breakdownData, setBreakdownData] = useState({ machine: '', issue: '', reportedBy: '', severity: '' });
    const [machineFormData, setMachineFormData] = useState({ code: '', name: '', type: '', location: '' });

    const handlePMSubmit = () => {
        alert('PM Task Scheduled!\n' + JSON.stringify(pmData, null, 2));
        setShowPMModal(false);
        setPmData({ machine: '', task: '', dueDate: '', assignee: '', priority: '' });
    };

    const handleBreakdownSubmit = () => {
        alert('Breakdown Reported!\n' + JSON.stringify(breakdownData, null, 2));
        setShowBreakdownModal(false);
        setBreakdownData({ machine: '', issue: '', reportedBy: '', severity: '' });
    };

    const handleMachineSubmit = () => {
        alert('Machine Added!\n' + JSON.stringify(machineFormData, null, 2));
        setShowMachineModal(false);
        setMachineFormData({ code: '', name: '', type: '', location: '' });
    };

    const running = machineData.filter(m => m.status === 'Running');
    const upcoming = pmSchedule.filter(pm => new Date(pm.dueDate) <= new Date('2026-02-15'));

    const tabs = [
        { label: 'Machines', content: <DataTable columns={machineColumns} data={machineData} title="Equipment List" /> },
        { label: 'PM Schedule', badge: pmSchedule.length, content: <PMScheduleList data={pmSchedule} /> },
        { label: 'Breakdown Log', content: <BreakdownLog /> },
    ];

    return (
        <MainLayout title="Maintenance" subtitle="Equipment and preventive maintenance">
            <div className="stats-grid">
                <StatsCard icon="🏭" value={machineData.length} label="Total Machines" variant="primary" />
                <StatsCard icon="✅" value={running.length} label="Running" variant="success" />
                <StatsCard icon="🔧" value={1} label="Under Maintenance" variant="warning" />
                <StatsCard icon="📋" value={upcoming.length} label="PM Due This Week" variant="primary" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowPMModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Schedule PM Task
                </button>
                <button onClick={() => setShowBreakdownModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🚨 Report Breakdown
                </button>
                <button onClick={() => setShowMachineModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🏭 Add Machine
                </button>
            </div>

            <div className="dashboard-grid">
                <MachineStatusOverview machines={machineData} />
                <MaintenanceCalendar />
            </div>

            <Tabs tabs={tabs} />

            {/* Schedule PM Modal */}
            <Modal isOpen={showPMModal} onClose={() => setShowPMModal(false)} title="Schedule PM Task" size="lg" footer={
                <>
                    <button onClick={() => setShowPMModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handlePMSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Schedule Task</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Machine" name="machine" value={pmData.machine} onChange={(e) => setPmData({ ...pmData, machine: e.target.value })} required options={machineData.map(m => ({ value: m.code, label: `${m.code} - ${m.name}` }))} />
                    <Input label="Task Description" name="task" value={pmData.task} onChange={(e) => setPmData({ ...pmData, task: e.target.value })} required placeholder="e.g. Oil Change" />
                    <Input label="Due Date" type="date" name="dueDate" value={pmData.dueDate} onChange={(e) => setPmData({ ...pmData, dueDate: e.target.value })} required />
                    <Input label="Assignee" name="assignee" value={pmData.assignee} onChange={(e) => setPmData({ ...pmData, assignee: e.target.value })} placeholder="Technician name" />
                    <Select label="Priority" name="priority" value={pmData.priority} onChange={(e) => setPmData({ ...pmData, priority: e.target.value })} options={[{ value: 'high', label: '🔴 High' }, { value: 'normal', label: '🟡 Normal' }, { value: 'low', label: '🟢 Low' }]} />
                </div>
            </Modal>

            {/* Report Breakdown Modal */}
            <Modal isOpen={showBreakdownModal} onClose={() => setShowBreakdownModal(false)} title="Report Breakdown" size="lg" footer={
                <>
                    <button onClick={() => setShowBreakdownModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleBreakdownSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-danger)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Report Breakdown</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Machine" name="machine" value={breakdownData.machine} onChange={(e) => setBreakdownData({ ...breakdownData, machine: e.target.value })} required options={machineData.map(m => ({ value: m.code, label: `${m.code} - ${m.name}` }))} />
                    <Select label="Severity" name="severity" value={breakdownData.severity} onChange={(e) => setBreakdownData({ ...breakdownData, severity: e.target.value })} required options={[{ value: 'critical', label: '🔴 Critical - Line Stopped' }, { value: 'major', label: '🟠 Major - Degraded Performance' }, { value: 'minor', label: '🟡 Minor - Still Operational' }]} />
                    <Input label="Issue Description" name="issue" value={breakdownData.issue} onChange={(e) => setBreakdownData({ ...breakdownData, issue: e.target.value })} required placeholder="Describe the issue" style={{ gridColumn: 'span 2' }} />
                    <Input label="Reported By" name="reportedBy" value={breakdownData.reportedBy} onChange={(e) => setBreakdownData({ ...breakdownData, reportedBy: e.target.value })} placeholder="Your name" />
                </div>
            </Modal>

            {/* Add Machine Modal */}
            <Modal isOpen={showMachineModal} onClose={() => setShowMachineModal(false)} title="Add Machine" size="lg" footer={
                <>
                    <button onClick={() => setShowMachineModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleMachineSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Machine</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Machine Code" name="code" value={machineFormData.code} onChange={(e) => setMachineFormData({ ...machineFormData, code: e.target.value })} required placeholder="e.g. CNC-03" />
                    <Input label="Machine Name" name="name" value={machineFormData.name} onChange={(e) => setMachineFormData({ ...machineFormData, name: e.target.value })} required placeholder="e.g. CNC Milling Machine" />
                    <Select label="Type" name="type" value={machineFormData.type} onChange={(e) => setMachineFormData({ ...machineFormData, type: e.target.value })} required options={[{ value: 'cnc', label: 'CNC' }, { value: 'press', label: 'Press' }, { value: 'welding', label: 'Welding' }, { value: 'molding', label: 'Molding' }]} />
                    <Select label="Location" name="location" value={machineFormData.location} onChange={(e) => setMachineFormData({ ...machineFormData, location: e.target.value })} required options={[{ value: 'floor-a', label: 'Shop Floor A' }, { value: 'floor-b', label: 'Shop Floor B' }, { value: 'floor-c', label: 'Shop Floor C' }]} />
                </div>
            </Modal>
        </MainLayout>
    );
}

function MachineStatusOverview({ machines }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Machine Status</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {machines.map((m, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '10px', height: '10px', borderRadius: '50%', backgroundColor: m.status === 'Running' ? 'var(--color-success)' : m.status === 'Idle' ? 'var(--color-gray-400)' : 'var(--color-warning)', marginRight: '12px' }} />
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, fontSize: '14px' }}>{m.code}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{m.name}</div>
                        </div>
                        <div style={{ fontSize: '12px', color: m.status === 'Running' ? 'var(--color-success)' : 'var(--color-gray-500)' }}>{m.status}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function MaintenanceCalendar() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Upcoming PM Tasks</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {pmSchedule.map((pm, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ width: '48px', textAlign: 'center', marginRight: '12px' }}>
                            <div style={{ fontSize: '18px', fontWeight: 700, color: 'var(--color-primary)' }}>{pm.dueDate.split('-')[2]}</div>
                            <div style={{ fontSize: '11px', color: 'var(--color-gray-500)' }}>Feb</div>
                        </div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, fontSize: '14px' }}>{pm.machine}: {pm.task}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Assigned: {pm.assignee}</div>
                        </div>
                        <span style={{ fontSize: '11px', color: pm.priority === 'High' ? 'var(--color-danger)' : 'var(--color-gray-500)' }}>● {pm.priority}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function PMScheduleList({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Machine</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Task</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Due Date</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Assignee</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Priority</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((pm, idx) => (
                        <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                            <td style={{ padding: '12px', fontWeight: 500 }}>{pm.machine}</td>
                            <td style={{ padding: '12px' }}>{pm.task}</td>
                            <td style={{ padding: '12px' }}>{pm.dueDate}</td>
                            <td style={{ padding: '12px' }}>{pm.assignee}</td>
                            <td style={{ padding: '12px' }}><span style={{ color: pm.priority === 'High' ? 'var(--color-danger)' : 'var(--color-gray-600)' }}>● {pm.priority}</span></td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function BreakdownLog() {
    const breakdowns = [
        { date: '2026-01-28', machine: 'WELD-01', issue: 'Wire feed motor failure', downtime: '4 hrs', resolved: true },
        { date: '2026-01-15', machine: 'CNC-02', issue: 'Coolant pump leak', downtime: '2 hrs', resolved: true },
    ];

    return (
        <div style={{ padding: '16px' }}>
            {breakdowns.map((b, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', border: '1px solid var(--color-gray-200)', borderRadius: '12px', marginBottom: '12px' }}>
                    <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: 'var(--color-danger-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '16px', fontSize: '20px' }}>⚠️</div>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 600 }}>{b.machine}: {b.issue}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{b.date} • Downtime: {b.downtime}</div>
                    </div>
                    <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: '#D1FAE5', color: '#059669' }}>Resolved</span>
                </div>
            ))}
        </div>
    );
}
