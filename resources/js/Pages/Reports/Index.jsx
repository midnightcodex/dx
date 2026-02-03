import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const reportCategories = [
    { name: 'Manufacturing', icon: '⚙️', reports: ['Production Summary', 'Work Order Status', 'Machine Utilization', 'Quality Metrics'] },
    { name: 'Inventory', icon: '📦', reports: ['Stock Valuation', 'Inventory Aging', 'Reorder Report', 'Movement Analysis'] },
    { name: 'Procurement', icon: '🛒', reports: ['Purchase History', 'Vendor Performance', 'Pending POs', 'GRN Summary'] },
    { name: 'Sales', icon: '💰', reports: ['Sales Analysis', 'Customer Ledger', 'Order Pipeline', 'Delivery Performance'] },
    { name: 'Finance', icon: '📊', reports: ['P&L Summary', 'Cost Analysis', 'Outstanding Receivables', 'Payables Aging'] },
];

export default function ReportsIndex() {
    const [showGenerateModal, setShowGenerateModal] = useState(false);
    const [showScheduleModal, setShowScheduleModal] = useState(false);
    const [generateFormData, setGenerateFormData] = useState({ category: '', report: '', dateFrom: '', dateTo: '', format: '' });
    const [scheduleFormData, setScheduleFormData] = useState({ report: '', frequency: '', time: '', recipients: '' });

    const handleGenerateSubmit = () => {
        alert('Report Generated!\n' + JSON.stringify(generateFormData, null, 2));
        setShowGenerateModal(false);
        setGenerateFormData({ category: '', report: '', dateFrom: '', dateTo: '', format: '' });
    };

    const handleScheduleSubmit = () => {
        alert('Report Scheduled!\n' + JSON.stringify(scheduleFormData, null, 2));
        setShowScheduleModal(false);
        setScheduleFormData({ report: '', frequency: '', time: '', recipients: '' });
    };

    return (
        <MainLayout title="Reports" subtitle="Analytics and business intelligence">
            <div className="stats-grid">
                <StatsCard icon="📊" value={20} label="Report Templates" variant="primary" />
                <StatsCard icon="📅" value={5} label="Scheduled Reports" variant="primary" />
                <StatsCard icon="⬇️" value={42} label="Downloads This Month" variant="success" />
                <StatsCard icon="⭐" value={8} label="Favorites" variant="warning" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowGenerateModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📊 Generate Report
                </button>
                <button onClick={() => setShowScheduleModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    📅 Schedule Report
                </button>
            </div>

            <div style={{ marginBottom: '24px' }}>
                <SearchBar />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '24px' }}>
                {reportCategories.map((cat, idx) => (
                    <ReportCategory key={idx} category={cat} onView={() => setShowGenerateModal(true)} />
                ))}
            </div>

            <RecentReports />

            {/* Generate Report Modal */}
            <Modal isOpen={showGenerateModal} onClose={() => setShowGenerateModal(false)} title="Generate Report" size="lg" footer={
                <>
                    <button onClick={() => setShowGenerateModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleGenerateSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Generate Report</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Category" name="category" value={generateFormData.category} onChange={(e) => setGenerateFormData({ ...generateFormData, category: e.target.value })} required options={reportCategories.map(c => ({ value: c.name.toLowerCase(), label: c.name }))} />
                    <Select label="Report" name="report" value={generateFormData.report} onChange={(e) => setGenerateFormData({ ...generateFormData, report: e.target.value })} required options={[
                        { value: 'production-summary', label: 'Production Summary' },
                        { value: 'stock-valuation', label: 'Stock Valuation' },
                        { value: 'sales-analysis', label: 'Sales Analysis' },
                        { value: 'vendor-performance', label: 'Vendor Performance' },
                    ]} />
                    <Input label="Date From" type="date" name="dateFrom" value={generateFormData.dateFrom} onChange={(e) => setGenerateFormData({ ...generateFormData, dateFrom: e.target.value })} required />
                    <Input label="Date To" type="date" name="dateTo" value={generateFormData.dateTo} onChange={(e) => setGenerateFormData({ ...generateFormData, dateTo: e.target.value })} required />
                    <Select label="Output Format" name="format" value={generateFormData.format} onChange={(e) => setGenerateFormData({ ...generateFormData, format: e.target.value })} required options={[
                        { value: 'pdf', label: 'PDF' },
                        { value: 'excel', label: 'Excel' },
                        { value: 'csv', label: 'CSV' },
                    ]} />
                </div>
            </Modal>

            {/* Schedule Report Modal */}
            <Modal isOpen={showScheduleModal} onClose={() => setShowScheduleModal(false)} title="Schedule Report" size="lg" footer={
                <>
                    <button onClick={() => setShowScheduleModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleScheduleSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Schedule</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Report" name="report" value={scheduleFormData.report} onChange={(e) => setScheduleFormData({ ...scheduleFormData, report: e.target.value })} required options={[
                        { value: 'production-summary', label: 'Production Summary' },
                        { value: 'stock-valuation', label: 'Stock Valuation' },
                        { value: 'sales-analysis', label: 'Sales Analysis' },
                    ]} />
                    <Select label="Frequency" name="frequency" value={scheduleFormData.frequency} onChange={(e) => setScheduleFormData({ ...scheduleFormData, frequency: e.target.value })} required options={[
                        { value: 'daily', label: 'Daily' },
                        { value: 'weekly', label: 'Weekly' },
                        { value: 'monthly', label: 'Monthly' },
                    ]} />
                    <Input label="Time" type="time" name="time" value={scheduleFormData.time} onChange={(e) => setScheduleFormData({ ...scheduleFormData, time: e.target.value })} required />
                    <Input label="Recipients (Email)" name="recipients" value={scheduleFormData.recipients} onChange={(e) => setScheduleFormData({ ...scheduleFormData, recipients: e.target.value })} placeholder="email@company.com" />
                </div>
            </Modal>
        </MainLayout>
    );
}

function SearchBar() {
    return (
        <div style={{ display: 'flex', gap: '12px' }}>
            <input type="text" placeholder="Search reports..." style={{ flex: 1, padding: '12px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px', backgroundColor: 'var(--color-white)' }} />
            <select style={{ padding: '12px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px', backgroundColor: 'var(--color-white)' }}>
                <option>All Categories</option>
                <option>Manufacturing</option>
                <option>Inventory</option>
                <option>Sales</option>
            </select>
        </div>
    );
}

function ReportCategory({ category, onView }) {
    return (
        <div className="chart-container">
            <div className="chart-header">
                <h3 className="chart-title">{category.icon} {category.name}</h3>
            </div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {category.reports.map((report, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', cursor: 'pointer', transition: 'all 0.15s ease' }}>
                        <span style={{ fontWeight: 500, fontSize: '14px' }}>{report}</span>
                        <div style={{ display: 'flex', gap: '8px' }}>
                            <button onClick={onView} style={{ padding: '4px 8px', borderRadius: '4px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontSize: '11px', cursor: 'pointer' }}>View</button>
                            <button style={{ padding: '4px 8px', borderRadius: '4px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', fontSize: '11px', cursor: 'pointer' }}>⬇️</button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function RecentReports() {
    const recent = [
        { name: 'Production Summary - Feb 2026', generated: '2026-02-02 10:30 AM', format: 'PDF', size: '245 KB' },
        { name: 'Stock Valuation Report', generated: '2026-02-01 04:15 PM', format: 'Excel', size: '1.2 MB' },
        { name: 'Sales Analysis - Jan 2026', generated: '2026-02-01 09:00 AM', format: 'PDF', size: '512 KB' },
    ];

    return (
        <div className="chart-container" style={{ marginTop: '24px' }}>
            <div className="chart-header"><h3 className="chart-title">Recently Generated</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {recent.map((r, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ width: '40px', height: '40px', borderRadius: '8px', backgroundColor: r.format === 'PDF' ? '#FEE2E2' : '#D1FAE5', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '16px', fontSize: '14px', fontWeight: 600, color: r.format === 'PDF' ? '#DC2626' : '#059669' }}>{r.format}</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 500, fontSize: '14px' }}>{r.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{r.generated} • {r.size}</div>
                        </div>
                        <button style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', fontSize: '12px', fontWeight: 500, cursor: 'pointer' }}>Download</button>
                    </div>
                ))}
            </div>
        </div>
    );
}
