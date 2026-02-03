import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const employeeData = [
    { id: 1, empId: 'EMP-001', name: 'Ramesh Kumar', department: 'Production', designation: 'Machine Operator', shift: 'Morning', status: 'Present', joinDate: '2022-03-15' },
    { id: 2, empId: 'EMP-002', name: 'Priya Sharma', department: 'Quality', designation: 'QC Inspector', shift: 'Morning', status: 'Present', joinDate: '2021-08-20' },
    { id: 3, empId: 'EMP-003', name: 'Ajay Singh', department: 'Maintenance', designation: 'Technician', shift: 'Morning', status: 'On Leave', joinDate: '2023-01-10' },
    { id: 4, empId: 'EMP-004', name: 'Sneha Reddy', department: 'Warehouse', designation: 'Store Keeper', shift: 'General', status: 'Present', joinDate: '2022-11-05' },
    { id: 5, empId: 'EMP-005', name: 'Vikram Patel', department: 'Production', designation: 'Supervisor', shift: 'Morning', status: 'Present', joinDate: '2020-06-01' },
];

const shiftData = [
    { id: 1, name: 'Morning', startTime: '06:00', endTime: '14:00', employees: 25, status: 'Active' },
    { id: 2, name: 'Afternoon', startTime: '14:00', endTime: '22:00', employees: 20, status: 'Active' },
    { id: 3, name: 'Night', startTime: '22:00', endTime: '06:00', employees: 10, status: 'Active' },
    { id: 4, name: 'General', startTime: '09:00', endTime: '18:00', employees: 15, status: 'Active' },
];

const employeeColumns = [
    { header: 'Emp ID', accessor: 'empId' },
    { header: 'Name', accessor: 'name' },
    { header: 'Department', accessor: 'department' },
    { header: 'Designation', accessor: 'designation' },
    { header: 'Shift', accessor: 'shift' },
    { header: 'Join Date', accessor: 'joinDate' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const colors = {
                'Present': { bg: '#D1FAE5', color: '#059669' },
                'On Leave': { bg: '#FEF3C7', color: '#D97706' },
                'Absent': { bg: '#FEE2E2', color: '#DC2626' },
            };
            const style = colors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: style.bg, color: style.color }}>{val}</span>;
        }
    },
];

export default function HRIndex() {
    const [showAddModal, setShowAddModal] = useState(false);
    const [showAttendanceModal, setShowAttendanceModal] = useState(false);
    const [showShiftModal, setShowShiftModal] = useState(false);
    const [formData, setFormData] = useState({ name: '', email: '', phone: '', department: '', designation: '', shift: '', joinDate: '' });
    const [attendanceData, setAttendanceData] = useState({ employee: '', date: '', status: '', remarks: '' });
    const [shiftFormData, setShiftFormData] = useState({ name: '', startTime: '', endTime: '', breakDuration: '' });

    const handleInputChange = (e) => setFormData({ ...formData, [e.target.name]: e.target.value });

    const handleSubmit = () => {
        alert('Employee Added!\n' + JSON.stringify(formData, null, 2));
        setShowAddModal(false);
        setFormData({ name: '', email: '', phone: '', department: '', designation: '', shift: '', joinDate: '' });
    };

    const handleAttendanceSubmit = () => {
        alert('Attendance Marked!\n' + JSON.stringify(attendanceData, null, 2));
        setShowAttendanceModal(false);
        setAttendanceData({ employee: '', date: '', status: '', remarks: '' });
    };

    const handleShiftSubmit = () => {
        alert('Shift Created!\n' + JSON.stringify(shiftFormData, null, 2));
        setShowShiftModal(false);
        setShiftFormData({ name: '', startTime: '', endTime: '', breakDuration: '' });
    };

    const present = employeeData.filter(e => e.status === 'Present');
    const onLeave = employeeData.filter(e => e.status === 'On Leave');

    const tabs = [
        { label: 'Employees', content: <DataTable columns={employeeColumns} data={employeeData} title="Employee Directory" /> },
        { label: 'Shifts', content: <ShiftList data={shiftData} /> },
        { label: 'Attendance', content: <AttendanceSummary /> },
    ];

    return (
        <MainLayout title="Human Resources" subtitle="Employee and attendance management">
            <div className="stats-grid">
                <StatsCard icon="👥" value={employeeData.length} label="Total Employees" variant="primary" />
                <StatsCard icon="✅" value={present.length} label="Present Today" variant="success" />
                <StatsCard icon="🏖️" value={onLeave.length} label="On Leave" variant="warning" />
                <StatsCard icon="📊" value="96%" label="Attendance Rate" variant="success" />
            </div>

            {/* Quick Actions */}
            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowAddModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Add Employee
                </button>
                <button onClick={() => setShowAttendanceModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📅 Mark Attendance
                </button>
                <button onClick={() => setShowShiftModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔄 Manage Shifts
                </button>
            </div>

            {/* Mark Attendance Modal */}
            <Modal isOpen={showAttendanceModal} onClose={() => setShowAttendanceModal(false)} title="Mark Attendance" size="lg" footer={
                <>
                    <button onClick={() => setShowAttendanceModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleAttendanceSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Mark Attendance</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Employee" name="employee" value={attendanceData.employee} onChange={(e) => setAttendanceData({ ...attendanceData, employee: e.target.value })} required options={employeeData.map(e => ({ value: e.empId, label: `${e.empId} - ${e.name}` }))} />
                    <Input label="Date" type="date" name="date" value={attendanceData.date} onChange={(e) => setAttendanceData({ ...attendanceData, date: e.target.value })} required />
                    <Select label="Status" name="status" value={attendanceData.status} onChange={(e) => setAttendanceData({ ...attendanceData, status: e.target.value })} required options={[{ value: 'present', label: '✅ Present' }, { value: 'absent', label: '❌ Absent' }, { value: 'leave', label: '🏖️ On Leave' }, { value: 'half-day', label: '⏰ Half Day' }]} />
                    <Input label="Remarks" name="remarks" value={attendanceData.remarks} onChange={(e) => setAttendanceData({ ...attendanceData, remarks: e.target.value })} placeholder="Optional remarks" />
                </div>
            </Modal>

            {/* Manage Shifts Modal */}
            <Modal isOpen={showShiftModal} onClose={() => setShowShiftModal(false)} title="Create Shift" size="lg" footer={
                <>
                    <button onClick={() => setShowShiftModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleShiftSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Create Shift</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Shift Name" name="name" value={shiftFormData.name} onChange={(e) => setShiftFormData({ ...shiftFormData, name: e.target.value })} required placeholder="e.g. Morning Shift" />
                    <Input label="Start Time" type="time" name="startTime" value={shiftFormData.startTime} onChange={(e) => setShiftFormData({ ...shiftFormData, startTime: e.target.value })} required />
                    <Input label="End Time" type="time" name="endTime" value={shiftFormData.endTime} onChange={(e) => setShiftFormData({ ...shiftFormData, endTime: e.target.value })} required />
                    <Input label="Break Duration (mins)" type="number" name="breakDuration" value={shiftFormData.breakDuration} onChange={(e) => setShiftFormData({ ...shiftFormData, breakDuration: e.target.value })} placeholder="e.g. 60" />
                </div>
            </Modal>

            <div className="dashboard-grid">
                <DepartmentBreakdown />
                <TodayAttendance employees={employeeData} />
            </div>

            <Tabs tabs={tabs} />

            {/* Add Employee Modal */}
            <Modal
                isOpen={showAddModal}
                onClose={() => setShowAddModal(false)}
                title="Add Employee"
                size="lg"
                footer={
                    <>
                        <button onClick={() => setShowAddModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                        <button onClick={handleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Add Employee</button>
                    </>
                }
            >
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input
                        label="Full Name"
                        name="name"
                        value={formData.name}
                        onChange={handleInputChange}
                        required
                        placeholder="Enter full name"
                    />
                    <Input
                        label="Email"
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleInputChange}
                        required
                        placeholder="employee@company.com"
                    />
                    <Input
                        label="Phone"
                        type="tel"
                        name="phone"
                        value={formData.phone}
                        onChange={handleInputChange}
                        placeholder="+91 98765 43210"
                    />
                    <Select
                        label="Department"
                        name="department"
                        value={formData.department}
                        onChange={handleInputChange}
                        required
                        options={[
                            { value: 'production', label: 'Production' },
                            { value: 'quality', label: 'Quality' },
                            { value: 'maintenance', label: 'Maintenance' },
                            { value: 'warehouse', label: 'Warehouse' },
                            { value: 'admin', label: 'Administration' },
                        ]}
                    />
                    <Input
                        label="Designation"
                        name="designation"
                        value={formData.designation}
                        onChange={handleInputChange}
                        required
                        placeholder="e.g. Machine Operator"
                    />
                    <Select
                        label="Shift"
                        name="shift"
                        value={formData.shift}
                        onChange={handleInputChange}
                        required
                        options={[
                            { value: 'morning', label: 'Morning (06:00 - 14:00)' },
                            { value: 'afternoon', label: 'Afternoon (14:00 - 22:00)' },
                            { value: 'night', label: 'Night (22:00 - 06:00)' },
                            { value: 'general', label: 'General (09:00 - 18:00)' },
                        ]}
                    />
                    <Input
                        label="Join Date"
                        type="date"
                        name="joinDate"
                        value={formData.joinDate}
                        onChange={handleInputChange}
                        required
                    />
                </div>
            </Modal>
        </MainLayout>
    );
}

function DepartmentBreakdown() {
    const departments = [
        { name: 'Production', count: 2, percentage: 40 },
        { name: 'Quality', count: 1, percentage: 20 },
        { name: 'Maintenance', count: 1, percentage: 20 },
        { name: 'Warehouse', count: 1, percentage: 20 },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">By Department</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {departments.map((d, idx) => (
                    <div key={idx}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '4px' }}>
                            <span style={{ fontSize: '14px' }}>{d.name}</span>
                            <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d.count} employees</span>
                        </div>
                        <div style={{ width: '100%', height: '8px', backgroundColor: 'var(--color-gray-200)', borderRadius: '4px' }}>
                            <div style={{ width: `${d.percentage}%`, height: '100%', backgroundColor: 'var(--color-primary)', borderRadius: '4px' }} />
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function TodayAttendance({ employees }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Today's Status</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {employees.slice(0, 5).map((e, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ width: '36px', height: '36px', borderRadius: '50%', backgroundColor: 'var(--color-primary-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '12px', fontWeight: 600, color: 'var(--color-primary)', fontSize: '14px' }}>
                            {e.name.split(' ').map(n => n[0]).join('')}
                        </div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 500, fontSize: '14px' }}>{e.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{e.designation}</div>
                        </div>
                        <div style={{ width: '8px', height: '8px', borderRadius: '50%', backgroundColor: e.status === 'Present' ? 'var(--color-success)' : 'var(--color-warning)' }} />
                    </div>
                ))}
            </div>
        </div>
    );
}

function ShiftList({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px' }}>
                {data.map((s, idx) => (
                    <div key={idx} style={{ padding: '20px', borderRadius: '12px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)' }}>
                        <div style={{ fontWeight: 600, fontSize: '16px', marginBottom: '8px' }}>{s.name} Shift</div>
                        <div style={{ fontSize: '24px', fontWeight: 700, color: 'var(--color-primary)', marginBottom: '8px' }}>{s.startTime} - {s.endTime}</div>
                        <div style={{ fontSize: '14px', color: 'var(--color-gray-500)' }}>{s.employees} employees assigned</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function AttendanceSummary() {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    const rates = [98, 95, 97, 92, 96];

    return (
        <div style={{ padding: '24px' }}>
            <div style={{ display: 'flex', alignItems: 'flex-end', justifyContent: 'space-around', height: '180px' }}>
                {days.map((d, idx) => (
                    <div key={idx} style={{ textAlign: 'center' }}>
                        <div style={{ width: '40px', height: `${rates[idx] * 1.5}px`, background: rates[idx] >= 95 ? 'var(--color-success)' : 'var(--color-warning)', borderRadius: '6px 6px 0 0', marginBottom: '8px' }} />
                        <div style={{ fontSize: '14px', fontWeight: 500 }}>{rates[idx]}%</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}
