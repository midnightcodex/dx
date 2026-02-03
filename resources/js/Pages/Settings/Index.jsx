import React, { useState } from 'react';
import '../../styles/dashboard.css';
import MainLayout from '../../Components/Layout/MainLayout';
import Tabs from '../../Components/UI/Tabs';
import Modal from '../../Components/UI/Modal';
import Input from '../../Components/UI/Input';

export default function SettingsIndex() {
    const tabs = [
        { label: 'Organization', content: <OrganizationSettings /> },
        { label: 'Master Settings', content: <MasterSettings /> },
        { label: 'Users', content: <UserManagement /> },
        { label: 'Integrations', content: <Integrations /> },
        { label: 'Preferences', content: <Preferences /> },
    ];

    return (
        <MainLayout title="Settings" subtitle="System configuration and preferences">
            <Tabs tabs={tabs} />
        </MainLayout>
    );
}

function MasterSettings() {        
    const [activeSection, setActiveSection] = useState('units');

    const sections = [
        { id: 'units', label: 'UOM (Units)', icon: '📏' },
        { id: 'taxes', label: 'Tax Rates', icon: '💸' },
        { id: 'categories', label: 'Item Categories', icon: '🏷️' },
        { id: 'terms', label: 'Payment Terms', icon: '📝' },
    ];

    const renderContent = () => {
        switch (activeSection) {
            case 'units': return <MastersList type="Unit" data={[{ name: 'Kilogram', code: 'kg' }, { name: 'Meter', code: 'm' }, { name: 'Pieces', code: 'pcs' }]} />;
            case 'taxes': return <MastersList type="Tax Rate" data={[{ name: 'GST 18%', code: '18' }, { name: 'GST 12%', code: '12' }, { name: 'Exempt', code: '0' }]} />;
            case 'categories': return <MastersList type="Category" data={[{ name: 'Raw Material', code: 'RM' }, { name: 'Finished Goods', code: 'FG' }, { name: 'Services', code: 'SVC' }]} />;
            case 'terms': return <MastersList type="Term" data={[{ name: 'Net 30', code: 'NET30' }, { name: 'Immediate', code: 'COD' }]} />;
            default: return null;
        }
    };

    return (
        <div style={{ display: 'grid', gridTemplateColumns: '250px 1fr', gap: '24px', alignItems: 'start' }}>
            <div className="chart-container" style={{ padding: '8px' }}>
                {sections.map(s => (
                    <div
                        key={s.id}
                        onClick={() => setActiveSection(s.id)}
                        style={{
                            padding: '12px 16px',
                            cursor: 'pointer',
                            borderRadius: '8px',
                            backgroundColor: activeSection === s.id ? 'var(--color-primary-light)' : 'transparent',
                            color: activeSection === s.id ? 'var(--color-primary)' : 'inherit',
                            fontWeight: activeSection === s.id ? 600 : 400,
                            marginBottom: '4px',
                            display: 'flex',
                            alignItems: 'center',
                            gap: '12px'
                        }}
                    >
                        <span>{s.icon}</span>
                        {s.label}
                    </div>
                ))}
            </div>
            <div className="chart-container">
                {renderContent()}
            </div>
        </div>
    );
}

function MastersList({ type, data }) {
    const [items, setItems] = useState(data);
    const [showModal, setShowModal] = useState(false);
    const [newItem, setNewItem] = useState({ name: '', code: '' });

    const handleAdd = () => {
        if (newItem.name && newItem.code) {
            setItems([...items, newItem]);
            setNewItem({ name: '', code: '' });
            setShowModal(false);
        }
    };

    return (
        <div>
            <div className="chart-header">
                <h3 className="chart-title">Manage {type}s</h3>
                <button onClick={() => setShowModal(true)} style={{ padding: '8px 16px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, fontSize: '14px', cursor: 'pointer' }}>+ Add {type}</button>
            </div>
            <div style={{ padding: '0 16px 16px' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                            <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Name</th>
                            <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Code/Value</th>
                            <th style={{ padding: '12px', textAlign: 'right', fontSize: '12px', color: 'var(--color-gray-500)' }}>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.map((item, idx) => (
                            <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                                <td style={{ padding: '12px', fontWeight: 500 }}>{item.name}</td>
                                <td style={{ padding: '12px', color: 'var(--color-gray-500)' }}>{item.code}</td>
                                <td style={{ padding: '12px', textAlign: 'right' }}>
                                    <button style={{ color: 'var(--color-danger)', background: 'none', border: 'none', cursor: 'pointer' }}>🗑</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <Modal isOpen={showModal} onClose={() => setShowModal(false)} title={`Add New ${type}`} size="sm" footer={
                <>
                    <button onClick={() => setShowModal(false)} style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', backgroundColor: 'var(--color-white)', cursor: 'pointer' }}>Cancel</button>
                    <button onClick={handleAdd} style={{ padding: '8px 16px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', cursor: 'pointer' }}>Save</button>
                </>
            }>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                    <Input label="Name" value={newItem.name} onChange={(e) => setNewItem({ ...newItem, name: e.target.value })} placeholder={`e.g. ${type} Name`} />
                    <Input label="Code / Value" value={newItem.code} onChange={(e) => setNewItem({ ...newItem, code: e.target.value })} placeholder="e.g. Short code or value" />
                </div>
            </Modal>
        </div>
    );
}

function OrganizationSettings() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">Organization Profile</h3></div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px', padding: '16px' }}>
                <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 500, color: 'var(--color-gray-500)', marginBottom: '8px' }}>Company Name</label>
                    <input type="text" defaultValue="SME Manufacturing Ltd" style={{ width: '100%', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
                <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 500, color: 'var(--color-gray-500)', marginBottom: '8px' }}>Tax ID / GST</label>
                    <input type="text" defaultValue="27AAACS1234A1Z5" style={{ width: '100%', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
                <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 500, color: 'var(--color-gray-500)', marginBottom: '8px' }}>Address</label>
                    <input type="text" defaultValue="Plot 123, Industrial Area, Pune" style={{ width: '100%', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
                <div>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 500, color: 'var(--color-gray-500)', marginBottom: '8px' }}>Phone</label>
                    <input type="text" defaultValue="+91 20 1234 5678" style={{ width: '100%', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
                <div style={{ gridColumn: 'span 2' }}>
                    <label style={{ display: 'block', fontSize: '12px', fontWeight: 500, color: 'var(--color-gray-500)', marginBottom: '8px' }}>Email</label>
                    <input type="email" defaultValue="info@smemanufacturing.com" style={{ width: '100%', padding: '12px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }} />
                </div>
            </div>
            <div style={{ padding: '16px', borderTop: '1px solid var(--color-gray-100)' }}>
                <button style={{ padding: '12px 24px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, cursor: 'pointer' }}>Save Changes</button>
            </div>
        </div>
    );
}

function UserManagement() {
    const users = [
        { name: 'Admin User', email: 'admin@sme.com', role: 'Administrator', status: 'Active', lastLogin: '2026-02-02' },
        { name: 'John Smith', email: 'john@sme.com', role: 'Production Manager', status: 'Active', lastLogin: '2026-02-02' },
        { name: 'Sarah Jones', email: 'sarah@sme.com', role: 'Quality Manager', status: 'Active', lastLogin: '2026-02-01' },
        { name: 'Mike Chen', email: 'mike@sme.com', role: 'Warehouse Manager', status: 'Active', lastLogin: '2026-02-01' },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header">
                <h3 className="chart-title">User Management</h3>
                <button style={{ padding: '8px 16px', borderRadius: '8px', border: 'none', backgroundColor: 'var(--color-primary)', color: 'white', fontWeight: 500, fontSize: '14px', cursor: 'pointer' }}>+ Add User</button>
            </div>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    <tr style={{ borderBottom: '1px solid var(--color-gray-200)' }}>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Name</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Email</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Role</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Status</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Last Login</th>
                        <th style={{ padding: '12px', textAlign: 'left', fontSize: '12px', color: 'var(--color-gray-500)' }}>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {users.map((u, idx) => (
                        <tr key={idx} style={{ borderBottom: '1px solid var(--color-gray-100)' }}>
                            <td style={{ padding: '12px', fontWeight: 500 }}>{u.name}</td>
                            <td style={{ padding: '12px' }}>{u.email}</td>
                            <td style={{ padding: '12px' }}>{u.role}</td>
                            <td style={{ padding: '12px' }}><span style={{ color: 'var(--color-success)', fontWeight: 500 }}>● {u.status}</span></td>
                            <td style={{ padding: '12px' }}>{u.lastLogin}</td>
                            <td style={{ padding: '12px' }}>
                                <button style={{ padding: '4px 8px', borderRadius: '4px', border: '1px solid var(--color-gray-200)', backgroundColor: 'transparent', marginRight: '4px', cursor: 'pointer', fontSize: '12px' }}>Edit</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function Integrations() {
    const integrations = [
        { name: 'Tally ERP', description: 'Accounting integration', status: 'Connected', icon: '📊' },
        { name: 'Barcode Scanner', description: 'Warehouse operations', status: 'Connected', icon: '📱' },
        { name: 'Email (SMTP)', description: 'Notifications', status: 'Connected', icon: '✉️' },
        { name: 'SMS Gateway', description: 'Alert notifications', status: 'Not Connected', icon: '📲' },
    ];

    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">External Integrations</h3></div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '16px', padding: '16px' }}>
                {integrations.map((i, idx) => (
                    <div key={idx} style={{ display: 'flex', alignItems: 'center', padding: '20px', borderRadius: '12px', border: '1px solid var(--color-gray-200)' }}>
                        <div style={{ width: '48px', height: '48px', borderRadius: '12px', backgroundColor: 'var(--color-gray-100)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginRight: '16px', fontSize: '24px' }}>{i.icon}</div>
                        <div style={{ flex: 1 }}>
                            <div style={{ fontWeight: 600 }}>{i.name}</div>
                            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>{i.description}</div>
                        </div>
                        <span style={{ padding: '4px 12px', borderRadius: '12px', fontSize: '12px', fontWeight: 500, backgroundColor: i.status === 'Connected' ? '#D1FAE5' : '#F3F4F6', color: i.status === 'Connected' ? '#059669' : '#6B7280' }}>{i.status}</span>
                    </div>
                ))}
            </div>
        </div>
    );
}

function Preferences() {
    return (
        <div className="chart-container">
            <div className="chart-header"><h3 className="chart-title">System Preferences</h3></div>
            <div style={{ padding: '16px' }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 0', borderBottom: '1px solid var(--color-gray-100)' }}>
                    <div>
                        <div style={{ fontWeight: 500 }}>Date Format</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Display format for dates</div>
                    </div>
                    <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }}>
                        <option>YYYY-MM-DD</option>
                        <option>DD/MM/YYYY</option>
                        <option>MM/DD/YYYY</option>
                    </select>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 0', borderBottom: '1px solid var(--color-gray-100)' }}>
                    <div>
                        <div style={{ fontWeight: 500 }}>Currency</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Default currency symbol</div>
                    </div>
                    <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }}>
                        <option>₹ INR</option>
                        <option>$ USD</option>
                        <option>€ EUR</option>
                    </select>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 0', borderBottom: '1px solid var(--color-gray-100)' }}>
                    <div>
                        <div style={{ fontWeight: 500 }}>Timezone</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>System timezone</div>
                    </div>
                    <select style={{ padding: '8px 16px', borderRadius: '8px', border: '1px solid var(--color-gray-200)', fontSize: '14px' }}>
                        <option>Asia/Kolkata (IST)</option>
                        <option>UTC</option>
                    </select>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '16px 0' }}>
                    <div>
                        <div style={{ fontWeight: 500 }}>Email Notifications</div>
                        <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>Receive system alerts via email</div>
                    </div>
                    <div style={{ width: '48px', height: '24px', borderRadius: '12px', backgroundColor: 'var(--color-primary)', position: 'relative', cursor: 'pointer' }}>
                        <div style={{ width: '20px', height: '20px', borderRadius: '50%', backgroundColor: 'white', position: 'absolute', right: '2px', top: '2px' }} />
                    </div>
                </div>
            </div>
        </div>
    );
}
