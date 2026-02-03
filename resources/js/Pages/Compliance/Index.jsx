import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import StatsCard from '../../Components/Dashboard/StatsCard';
import DataTable from '../../Components/Dashboard/DataTable';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';
import Select from '../../Components/UI/Select';

const documentData = [
    { id: 1, docId: 'DOC-001', title: 'Quality Management Manual', type: 'Manual', version: 'v3.2', status: 'Active', owner: 'Quality Dept', reviewDate: '2026-06-15', lastUpdated: '2026-01-15' },
    { id: 2, docId: 'DOC-002', title: 'Work Instruction - CNC Operation', type: 'WI', version: 'v2.1', status: 'Active', owner: 'Production', reviewDate: '2026-04-20', lastUpdated: '2026-01-10' },
    { id: 3, docId: 'DOC-003', title: 'SOP - Material Handling', type: 'SOP', version: 'v1.5', status: 'Under Review', owner: 'Warehouse', reviewDate: '2026-02-28', lastUpdated: '2026-02-01' },
    { id: 4, docId: 'DOC-004', title: 'Safety Procedures', type: 'Manual', version: 'v4.0', status: 'Active', owner: 'HSE', reviewDate: '2026-08-01', lastUpdated: '2025-12-20' },
];

const certificationData = [
    { id: 1, certId: 'CERT-001', name: 'ISO 9001:2015', issuedBy: 'Bureau Veritas', issueDate: '2024-03-15', expiryDate: '2027-03-14', status: 'Valid' },
    { id: 2, certId: 'CERT-002', name: 'ISO 14001:2015', issuedBy: 'TUV SUD', issueDate: '2024-05-20', expiryDate: '2027-05-19', status: 'Valid' },
    { id: 3, certId: 'CERT-003', name: 'IATF 16949', issuedBy: 'DNV GL', issueDate: '2023-08-10', expiryDate: '2026-08-09', status: 'Renewal Due' },
];

const auditData = [
    { id: 1, date: '2026-02-01', type: 'Internal', area: 'Production', auditor: 'Quality Team', findings: 2, status: 'Completed' },
    { id: 2, date: '2026-01-15', type: 'External', area: 'Quality System', auditor: 'Bureau Veritas', findings: 1, status: 'Completed' },
    { id: 3, date: '2026-03-10', type: 'Internal', area: 'Warehouse', auditor: 'Quality Team', findings: 0, status: 'Scheduled' },
];

const documentColumns = [
    { header: 'Doc ID', accessor: 'docId' },
    { header: 'Title', accessor: 'title' },
    { header: 'Type', accessor: 'type' },
    { header: 'Version', accessor: 'version' },
    { header: 'Owner', accessor: 'owner' },
    { header: 'Review Date', accessor: 'reviewDate' },
    {
        header: 'Status', accessor: 'status', render: (val) => {
            const colors = { Active: '#059669', 'Under Review': '#D97706', Obsolete: '#6B7280' };
            return <span style={{ color: colors[val], fontWeight: 500 }}>● {val}</span>;
        }
    },
];

export default function ComplianceIndex() {
    const [showDocModal, setShowDocModal] = useState(false);
    const [showAuditModal, setShowAuditModal] = useState(false);
    const [docFormData, setDocFormData] = useState({ title: '', type: '', owner: '', version: '' });
    const [auditFormData, setAuditFormData] = useState({ type: '', area: '', auditor: '', date: '' });

    const handleDocSubmit = () => {
        alert('Document Uploaded!\n' + JSON.stringify(docFormData, null, 2));
        setShowDocModal(false);
        setDocFormData({ title: '', type: '', owner: '', version: '' });
    };

    const handleAuditSubmit = () => {
        alert('Audit Scheduled!\n' + JSON.stringify(auditFormData, null, 2));
        setShowAuditModal(false);
        setAuditFormData({ type: '', area: '', auditor: '', date: '' });
    };

    const activeDocuments = documentData.filter(d => d.status === 'Active');
    const upcomingAudits = auditData.filter(a => a.status === 'Scheduled');

    const tabs = [
        { label: 'Documents', content: <DataTable columns={documentColumns} data={documentData} title="Document Control" /> },
        { label: 'Certifications', content: <CertificationList data={certificationData} /> },
        { label: 'Audit Log', content: <AuditLog data={auditData} /> },
    ];

    return (
        <MainLayout title="Compliance" subtitle="Documents, certifications, and audits">
            <div className="stats-grid">
                <StatsCard icon="📄" value={documentData.length} label="Controlled Documents" variant="primary" />
                <StatsCard icon="🏆" value={certificationData.length} label="Active Certifications" variant="success" />
                <StatsCard icon="🔍" value={upcomingAudits.length} label="Upcoming Audits" variant="warning" />
                <StatsCard icon="✅" value="98%" label="Document Compliance" variant="success" />
            </div>

            <div style={{ display: 'flex', gap: '12px', marginBottom: '24px' }}>
                <button onClick={() => setShowDocModal(true)} style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '12px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    ➕ Upload Document
                </button>
                <button onClick={() => setShowAuditModal(true)} style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🔍 Schedule Audit
                </button>
                <button style={{ padding: '12px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', fontWeight: 500, cursor: 'pointer', fontSize: '14px' }}>
                    🏆 View Certifications
                </button>
            </div>

            <div className="dashboard-grid">
                <CertificationOverview data={certificationData} />
                <UpcomingReviews documents={documentData} />
            </div>

            <Tabs tabs={tabs} />

            {/* Upload Document Modal */}
            <Modal isOpen={showDocModal} onClose={() => setShowDocModal(false)} title="Upload Document" size="lg" footer={
                <>
                    <button onClick={() => setShowDocModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleDocSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Upload</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Input label="Document Title" name="title" value={docFormData.title} onChange={(e) => setDocFormData({ ...docFormData, title: e.target.value })} required placeholder="e.g. Quality Manual" />
                    <Select label="Document Type" name="type" value={docFormData.type} onChange={(e) => setDocFormData({ ...docFormData, type: e.target.value })} required options={[{ value: 'manual', label: 'Manual' }, { value: 'sop', label: 'SOP' }, { value: 'wi', label: 'Work Instruction' }, { value: 'form', label: 'Form' }]} />
                    <Select label="Owner Department" name="owner" value={docFormData.owner} onChange={(e) => setDocFormData({ ...docFormData, owner: e.target.value })} required options={[{ value: 'quality', label: 'Quality Dept' }, { value: 'production', label: 'Production' }, { value: 'hse', label: 'HSE' }, { value: 'warehouse', label: 'Warehouse' }]} />
                    <Input label="Version" name="version" value={docFormData.version} onChange={(e) => setDocFormData({ ...docFormData, version: e.target.value })} placeholder="e.g. v1.0" />
                </div>
                <div style={{ marginTop: '16px', padding: '40px', border: '2px dashed var(--color-gray-300)', borderRadius: '12px', textAlign: 'center', backgroundColor: 'var(--color-gray-50)' }}>
                    <div style={{ fontSize: '32px', marginBottom: '8px' }}>📁</div>
                    <div style={{ color: 'var(--color-gray-500)' }}>Drag and drop file here, or click to browse</div>
                </div>
            </Modal>

            {/* Schedule Audit Modal */}
            <Modal isOpen={showAuditModal} onClose={() => setShowAuditModal(false)} title="Schedule Audit" size="lg" footer={
                <>
                    <button onClick={() => setShowAuditModal(false)} style={{ padding: '10px 20px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleAuditSubmit} style={{ padding: '10px 20px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer', fontWeight: 500 }}>Schedule Audit</button>
                </>
            }>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <Select label="Audit Type" name="type" value={auditFormData.type} onChange={(e) => setAuditFormData({ ...auditFormData, type: e.target.value })} required options={[{ value: 'internal', label: 'Internal Audit' }, { value: 'external', label: 'External Audit' }, { value: 'surveillance', label: 'Surveillance Audit' }]} />
                    <Select label="Audit Area" name="area" value={auditFormData.area} onChange={(e) => setAuditFormData({ ...auditFormData, area: e.target.value })} required options={[{ value: 'production', label: 'Production' }, { value: 'quality', label: 'Quality System' }, { value: 'warehouse', label: 'Warehouse' }, { value: 'maintenance', label: 'Maintenance' }]} />
                    <Input label="Auditor/Team" name="auditor" value={auditFormData.auditor} onChange={(e) => setAuditFormData({ ...auditFormData, auditor: e.target.value })} required placeholder="Audit team name" />
                    <Input label="Scheduled Date" type="date" name="date" value={auditFormData.date} onChange={(e) => setAuditFormData({ ...auditFormData, date: e.target.value })} required />
                </div>
            </Modal>
        </MainLayout>
    );
}

function CertificationOverview({ data }) {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Certifications</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                {data.map((c, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: c.status === 'Valid' ? 'var(--color-success-light)' : 'var(--color-warning-light)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '16px', fontSize: '20px' }}>
                            {c.status === 'Valid' ? '✓' : '⚠️'}
                        </div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600, fontSize: '14px' }}>{c.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Expires: {c.expiryDate}</div>
                        </div>
                        <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: c.status === 'Valid' ? '#D1FAE5' : '#FEF3C7', color: c.status === 'Valid' ? '#059669' : '#D97706' }}>{c.status}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function UpcomingReviews({ documents }) {
    const upcoming = documents.filter(d => new Date(d.reviewDate) <= new Date('2026-06-30')).sort((a, b) => new Date(a.reviewDate) - new Date(b.reviewDate));
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Upcoming Document Reviews</h3></div>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '8px' }}>
                {upcoming.map((d, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '12px', borderRadius: '8px', backgroundColor: 'var(--color-gray-50)' }}>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 500, fontSize: '14px' }}>{d.title}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d.docId} • {d.owner}</div>
                        </div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{d.reviewDate}</div>
                    </div>
                ))}
            </div>
        </div>
    );
}

function CertificationList({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Certification</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Issued By</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Issue Date</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Expiry Date</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Status</th>
                    </tr>
                </thead>
                <tbody>
                    {data.map((c, idx) => (
                        <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                            <td style={{ padding: '12px', fontWeight: 500 }}>{c.name}</td>
                            <td style={{ padding: '12px' }}>{c.issuedBy}</td>
                            <td style={{ padding: '12px' }}>{c.issueDate}</td>
                            <td style={{ padding: '12px' }}>{c.expiryDate}</td>
                            <td style={{ padding: '12px' }}><span style={{ color: c.status === 'Valid' ? 'var(--color-success)' : 'var(--color-warning)', fontWeight: 500 }}>● {c.status}</span></td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function AuditLog({ data }) {
    return (
        <div style={{ padding: '16px' }}>
            {data.map((a, idx) => (
                <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '16px', border: '1px solid var(--color-gray-200)', borderRadius: '12px', marginBottom: '12px' }}>
                    <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: a.type === 'External' ? 'var(--color-primary-light)' : 'var(--color-gray-100)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '16px', fontSize: '20px' }}>🔍</div>
                    <div style={{ flex: 1 }}>
                        <div style={{ fontWeight: 600 }}>{a.type} Audit - {a.area}</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{a.date} • Auditor: {a.auditor}</div>
                    </div>
                    <div style={{ textAlign: 'right' }}>
                        <div style={{ fontWeight: 500, color: a.findings > 0 ? 'var(--color-warning)' : 'var(--color-success)' }}>{a.findings} findings</div>
                        <span style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{a.status}</span>
                    </div>
                </div>
            ))}
        </div>
    );
}
