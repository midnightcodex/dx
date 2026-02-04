import '../styles/dashboard.css';
import MainLayout from '../Components/Layout/MainLayout';
import StatsCard from '../Components/Dashboard/StatsCard';
import PayrollChart from '../Components/Dashboard/PayrollChart';
import DataTable from '../Components/Dashboard/DataTable';
import Tabs from '../Components/UI/Tabs';
import { Link, router } from '@inertiajs/react';

const workOrderColumns = [
    { header: 'WO Number', accessor: 'woNumber' },
    { header: 'Product', accessor: 'product' },
    { header: 'Qty', accessor: 'quantity' },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => {
            const statusColors = {
                'In Progress': { bg: '#DBEAFE', color: '#1D4ED8' },
                'Released': { bg: '#FEF3C7', color: '#D97706' },
                'Completed': { bg: '#D1FAE5', color: '#059669' },
                'Planned': { bg: '#F3F4F6', color: '#6B7280' },
            };
            const style = statusColors[val] || { bg: '#F3F4F6', color: '#6B7280' };
            return (
                <span className="px-2 py-1 rounded text-xs font-semibold" style={{ backgroundColor: style.bg, color: style.color }}>
                    {val}
                </span>
            );
        }
    },
    { header: 'Start Date', accessor: 'startDate' },
];

const inventoryColumns = [
    { header: 'Item Code', accessor: 'itemCode' },
    { header: 'Name', accessor: 'name' },
    { header: 'Total Qty', accessor: 'quantity', render: (val) => Number(val).toLocaleString() },
    { header: 'Unit', accessor: 'unit' },
    {
        header: 'Status',
        accessor: 'quantity',
        render: (val, row) => {
            const isLow = row.reorderLevel > 0 && val <= row.reorderLevel;
            return (
                <span className={`px-2 py-1 rounded text-xs font-semibold ${isLow ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}`}>
                    {isLow ? 'Low Stock' : 'Good'}
                </span>
            );
        }
    },
];

const salesColumns = [
    { header: 'SO Number', accessor: 'soNumber' },
    { header: 'Customer', accessor: 'customer' },
    { header: 'Amount', accessor: 'amount', render: (val) => `₹${Number(val).toFixed(2)}` },
    {
        header: 'Status',
        accessor: 'status',
        render: (val) => (
            <span className={`px-2 py-1 rounded text-xs font-semibold ${val === 'CONFIRMED' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100'}`}>
                {val}
            </span>
        )
    },
    { header: 'Date', accessor: 'date' },
];

export default function Dashboard({ stats, tables }) {
    const tabs = [
        {
            label: 'Recent Work Orders',
            content: <DataTable columns={workOrderColumns} data={tables.workOrders} title="Work Orders" actions={false} />
        },
        {
            label: 'Inventory',
            content: <DataTable columns={inventoryColumns} data={tables.inventory} title="Recent Items" actions={false} />
        },
        {
            label: 'Recent Sales',
            content: <DataTable columns={salesColumns} data={tables.salesOrders} title="Sales Orders" actions={false} />
        },
        {
            label: 'Quality',
            badge: stats.qualityIssues,
            content: (
                <div className="text-center p-12 text-gray-500">
                    <span className="text-4xl block mb-4">🔍</span>
                    <p>{stats.qualityIssues} inspections pending review</p>
                </div>
            )
        },
    ];

    return (
        <MainLayout title="Dashboard" subtitle="Overview">
            {/* Stats Cards */}
            <div className="stats-grid">
                <StatsCard
                    icon="⚙️"
                    value={stats.activeWorkOrders}
                    label="Active Work Orders"
                    trend="Currently Running"
                    variant="primary"
                />
                <StatsCard
                    icon="📦"
                    value={stats.lowStockItems}
                    label="Low Stock Items"
                    trend="Requires Reorder"
                    variant={stats.lowStockItems > 0 ? "danger" : "success"}
                />
                <StatsCard
                    icon="💰"
                    value={stats.pendingSalesOrders}
                    label="Pending Shipments"
                    trend="To Ship"
                    variant="warning"
                />
                <StatsCard
                    icon="🛒"
                    value={stats.pendingPOs}
                    label="Pending Purchases"
                    trend="Processing"
                    variant="info"
                />
                <StatsCard
                    icon="🔧"
                    value={stats.openTickets}
                    label="Open Tickets"
                    trend="Requires Attention"
                    variant={stats.openTickets > 0 ? "danger" : "success"}
                />
                <StatsCard
                    icon="🏖️"
                    value={stats.pendingLeaves}
                    label="Leave Requests"
                    trend="Needing Approval"
                    variant="warning"
                />
                <StatsCard
                    icon="👥"
                    value={stats.totalEmployees}
                    label="Total Workforce"
                    trend="Active"
                    variant="primary"
                />
            </div>

            {/* Chart Section */}
            <div className="dashboard-grid">
                <PayrollChart title="Weekly Production Output" />

                {/* Quick Actions Card */}
                <div className="chart-container">
                    <div className="chart-header">
                        <h3 className="chart-title">Quick Actions</h3>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <QuickActionCard
                            icon="📝"
                            title="Create WO"
                            description="New Production"
                            href={route('manufacturing.work-orders.create')}
                        />
                        <QuickActionCard
                            icon="📦"
                            title="Add Item"
                            description="New Inventory"
                            href={route('inventory.items.create')}
                        />
                        <QuickActionCard
                            icon="🛒"
                            title="Create PO"
                            description="Buy Material"
                            href={route('procurement.purchase-orders.create')}
                        />
                        <QuickActionCard
                            icon="🛍️"
                            title="Sales Order"
                            description="New Sale"
                            href={route('sales.orders.create')}
                        />
                        <QuickActionCard
                            icon="🔧"
                            title="Report Issue"
                            description="Maintenance"
                            href={route('maintenance.tickets.index')}
                        />
                        <QuickActionCard
                            icon="👥"
                            title="Onboard"
                            description="New Employee"
                            href={route('hr.employees.create')}
                        />
                    </div>
                </div>
            </div>

            {/* Tabs with Tables */}
            <Tabs tabs={tabs} />
        </MainLayout>
    );
}

function QuickActionCard({ icon, title, description, href }) {
    return (
        <Link href={href} className="block p-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 hover:border-indigo-500 transition-all">
            <span className="text-2xl block mb-2">{icon}</span>
            <div className="font-semibold text-gray-900">{title}</div>
            <div className="text-xs text-gray-500">{description}</div>
        </Link>
    );
}
