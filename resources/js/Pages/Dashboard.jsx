import '../styles/dashboard.css';
import MainLayout from '../Components/Layout/MainLayout';
import StatsCard from '../Components/Dashboard/StatsCard';
import PayrollChart from '../Components/Dashboard/PayrollChart';
import DataTable from '../Components/Dashboard/DataTable';
import Tabs from '../Components/UI/Tabs';

// Sample data for the work orders table
const workOrderData = [
    { id: 1, woNumber: 'WO-2026-001', product: 'Steel Brackets', quantity: 500, status: 'In Progress', startDate: '2026-02-01' },
    { id: 2, woNumber: 'WO-2026-002', product: 'Aluminum Plates', quantity: 1000, status: 'Pending', startDate: '2026-02-02' },
    { id: 3, woNumber: 'WO-2026-003', product: 'Copper Wires', quantity: 250, status: 'Completed', startDate: '2026-01-30' },
    { id: 4, woNumber: 'WO-2026-004', product: 'Plastic Housings', quantity: 750, status: 'In Progress', startDate: '2026-02-01' },
    { id: 5, woNumber: 'WO-2026-005', product: 'Electronic PCBs', quantity: 200, status: 'Quality Check', startDate: '2026-01-29' },
];

const workOrderColumns = [
    { header: 'S/N', accessor: 'id' },
    { header: 'WO Number', accessor: 'woNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Quantity', accessor: 'quantity', render: (val) => val.toLocaleString() },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const statusColors = {
                'In Progress': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Pending': { bg: '#FEF3C7', color: '#D97706' },
                'Completed': { bg: '#D1FAE5', color: '#059669' },
                'Quality Check': { bg: '#EDE9FE', color: '#7C3AED' },
            };
            const style = statusColors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return (
                <span style={{
                    padding: '4px 12px',
                    borderRadius: '12px',
                    fontSize: '12px',
                    fontWeight: 500,
                    backgroundColor: style.bg,
                    color: style.color
                }}>
                    {val}
                </span>
            );
        }
    },
    { header: 'Start Date', accessor: 'startDate' },
];

// Inventory data for second tab
const inventoryData = [
    { id: 1, itemCode: 'RM-001', name: 'Raw Steel', warehouse: 'Main', quantity: 5000, unit: 'kg', reorderLevel: 1000 },
    { id: 2, itemCode: 'RM-002', name: 'Aluminum Sheets', warehouse: 'Main', quantity: 2500, unit: 'sheets', reorderLevel: 500 },
    { id: 3, itemCode: 'RM-003', name: 'Copper Wire 2mm', warehouse: 'Production', quantity: 800, unit: 'm', reorderLevel: 200 },
    { id: 4, itemCode: 'FG-001', name: 'Steel Brackets A1', warehouse: 'Finished Goods', quantity: 1200, unit: 'pcs', reorderLevel: 300 },
    { id: 5, itemCode: 'FG-002', name: 'Aluminum Plates B2', warehouse: 'Finished Goods', quantity: 450, unit: 'pcs', reorderLevel: 100 },
];

const inventoryColumns = [
    { header: 'S/N', accessor: 'id' },
    { header: 'Item Code', accessor: 'itemCode' },
    { header: 'Name', accessor: 'name' },
    { header: 'Warehouse', accessor: 'warehouse' },
    { header: 'Quantity', accessor: 'quantity', render: (val) => val.toLocaleString() },
    { header: 'Unit', accessor: 'unit' },
    {
        header: 'Stock Status',
        accessor: 'quantity',
        render: (val, row) => {
            const isLow = val <= row.reorderLevel;
            return (
                <span style={{
                    padding: '4px 12px',
                    borderRadius: '12px',
                    fontSize: '12px',
                    fontWeight: 500,
                    backgroundColor: isLow ? '#FEE2E2' : '#D1FAE5',
                    color: isLow ? '#DC2626' : '#059669'
                }}>
                    {isLow ? 'Low Stock' : 'In Stock'}
                </span>
            );
        }
    },
];

export default function Dashboard({ stats, tables }) {
    const tabs = [
        {
            label: 'Work Orders',
            content: <DataTable columns={workOrderColumns} data={tables.workOrders} title="Recent Work Orders" actions={false} />
        },
        {
            label: 'Inventory',
            content: <DataTable columns={inventoryColumns} data={tables.inventory} title="Recent Items" actions={false} />
        },
        // ... keeping other tabs as placeholders for now ...
        {
            label: 'Quality Inspections',
            badge: stats.qualityIssues,
            content: (
                <div style={{
                    textAlign: 'center',
                    padding: '48px',
                    color: 'var(--color-gray-500)'
                }}>
                    <span style={{ fontSize: '48px', display: 'block', marginBottom: '16px' }}>🔍</span>
                    <p>{stats.qualityIssues} inspections pending review</p>
                </div>
            )
        },
        {
            label: 'Maintenance',
            content: (
                <div style={{
                    textAlign: 'center',
                    padding: '48px',
                    color: 'var(--color-gray-500)'
                }}>
                    <span style={{ fontSize: '48px', display: 'block', marginBottom: '16px' }}>🔧</span>
                    <p>All equipment operational</p>
                </div>
            )
        },
    ];

    return (
        <MainLayout
            title="Manufacturing Dashboard"
            subtitle="Real-time production and inventory overview"
        >
            {/* Stats Cards */}
            <div className="stats-grid">
                <StatsCard
                    icon="⚙️"
                    value={stats.activeWorkOrders}
                    label="Active Work Orders"
                    trend="Real-time"
                    trendDirection="neutral"
                    variant="primary"
                    animationDelay={0}
                />
                <StatsCard
                    icon="📦"
                    value={stats.totalItems}
                    label="Total Items"
                    trend="In Database"
                    trendDirection="neutral"
                    variant="success"
                    animationDelay={100}
                />
                <StatsCard
                    icon="🛒"
                    value={stats.pendingPOs}
                    label="Pending POs"
                    trend=" Procurement"
                    trendDirection="neutral"
                    variant="warning"
                    animationDelay={200}
                />
                <StatsCard
                    icon="⚠️"
                    value={stats.qualityIssues}
                    label="Quality Issues"
                    trend="Requires Attention"
                    trendDirection="down"
                    variant="danger"
                    animationDelay={300}
                />
            </div>

            {/* Chart Section */}
            <div className="dashboard-grid">
                <PayrollChart title="Production Output (Projected)" />

                {/* Quick Actions Card */}
                <div className="chart-container">
                    <div className="chart-header">
                        <h3 className="chart-title">Quick Actions</h3>
                    </div>
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
                        <QuickActionCard
                            icon="📝"
                            title="Create Work Order"
                            description="Start new production"
                        />
                        <QuickActionCard
                            icon="📦"
                            title="New Item"
                            description="Register inventory"
                        />
                        <QuickActionCard
                            icon="🔍"
                            title="Stock Check"
                            description="Audit warehouse"
                        />
                        <QuickActionCard
                            icon="📊"
                            title="Reports"
                            description="View analytics"
                        />
                    </div>
                </div>
            </div>

            {/* Tabs with Tables */}
            <Tabs tabs={tabs} />
        </MainLayout>
    );
}

function QuickActionCard({ icon, title, description }) {
    return (
        <div style={{
            padding: '16px',
            borderRadius: '12px',
            border: '1px solid var(--color-gray-200)',
            cursor: 'pointer',
            transition: 'all 0.2s ease',
            backgroundColor: 'var(--color-white)'
        }}
            onMouseEnter={(e) => {
                e.currentTarget.style.backgroundColor = 'var(--color-gray-50)';
                e.currentTarget.style.borderColor = 'var(--color-primary)';
            }}
            onMouseLeave={(e) => {
                e.currentTarget.style.backgroundColor = 'var(--color-white)';
                e.currentTarget.style.borderColor = 'var(--color-gray-200)';
            }}
        >
            <span style={{ fontSize: '24px', display: 'block', marginBottom: '8px' }}>{icon}</span>
            <div style={{ fontWeight: 600, color: 'var(--color-gray-900)', marginBottom: '4px' }}>
                {title}
            </div>
            <div style={{ fontSize: '12px', color: 'var(--color-gray-500)' }}>
                {description}
            </div>
        </div>
    );
}
