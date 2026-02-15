import { NavLink } from 'react-router-dom';
import { useAuth } from '../../lib/auth';

export function AppLayout({ children }: { children: React.ReactNode }) {
  const { user, logout } = useAuth();

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <div className="logo">ERP</div>
          <div>
            <div className="brand-title">SME ERP</div>
            <div className="brand-sub">Manufacturing Suite</div>
          </div>
        </div>
        <nav className="nav">
          <NavLink to="/" end>Dashboard</NavLink>
          <NavLink to="/inventory/items">Inventory Items</NavLink>
          <NavLink to="/inventory/warehouses">Warehouses</NavLink>
          <NavLink to="/manufacturing/boms">BOMs</NavLink>
          <NavLink to="/manufacturing/work-orders">Work Orders</NavLink>
          <NavLink to="/health">API Health</NavLink>
        </nav>
      </aside>
      <main className="main">
        <header className="topbar">
          <div className="topbar-left">
            <span className="topbar-title">Control Center</span>
          </div>
          <div className="topbar-right">
            {user && (
              <div className="user-chip">
                <span>{user.name}</span>
                <button onClick={logout}>Sign out</button>
              </div>
            )}
          </div>
        </header>
        <section className="content">{children}</section>
      </main>
    </div>
  );
}
