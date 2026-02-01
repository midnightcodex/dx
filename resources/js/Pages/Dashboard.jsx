export default function Dashboard() {
    return (
        <div className="min-h-screen bg-gray-900 text-white">
            <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                        SME Manufacturing ERP
                    </h1>
                    <p className="mt-4 text-xl text-gray-400">
                        Welcome to your manufacturing operations dashboard
                    </p>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    <DashboardCard
                        title="Work Orders"
                        value="24"
                        subtitle="Active today"
                        color="blue"
                    />
                    <DashboardCard
                        title="Inventory Items"
                        value="1,847"
                        subtitle="In stock"
                        color="green"
                    />
                    <DashboardCard
                        title="Purchase Orders"
                        value="12"
                        subtitle="Pending approval"
                        color="yellow"
                    />
                    <DashboardCard
                        title="Quality Issues"
                        value="3"
                        subtitle="Open NCRs"
                        color="red"
                    />
                </div>

                <div className="bg-gray-800 rounded-xl p-6 border border-gray-700">
                    <h2 className="text-xl font-semibold mb-4">🚀 Inertia.js + React Setup Complete!</h2>
                    <p className="text-gray-400 mb-4">
                        Your Laravel + React SPA is ready. The stack includes:
                    </p>
                    <ul className="list-disc list-inside text-gray-400 space-y-2">
                        <li>Laravel 12.49.0 (Backend APIs)</li>
                        <li>Inertia.js v2 (SPA Bridge)</li>
                        <li>React 18+ (Frontend UI)</li>
                        <li>Vite (Build Tool)</li>
                    </ul>
                </div>
            </div>
        </div>
    );
}

function DashboardCard({ title, value, subtitle, color }) {
    const colorClasses = {
        blue: 'from-blue-500 to-blue-600',
        green: 'from-green-500 to-green-600',
        yellow: 'from-yellow-500 to-yellow-600',
        red: 'from-red-500 to-red-600',
    };

    return (
        <div className="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-gray-600 transition-colors">
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-gray-400 text-sm font-medium">{title}</h3>
                <div className={`w-3 h-3 rounded-full bg-gradient-to-r ${colorClasses[color]}`}></div>
            </div>
            <p className="text-3xl font-bold text-white">{value}</p>
            <p className="text-gray-500 text-sm mt-1">{subtitle}</p>
        </div>
    );
}
