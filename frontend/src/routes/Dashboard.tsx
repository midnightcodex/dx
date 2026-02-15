import { useEffect, useState } from 'react';
import { AppLayout } from '../components/layout/AppLayout';
import { api } from '../lib/api';
import { useToast } from '../lib/toast';

export function Dashboard() {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState<any>(null);
  const { push } = useToast();

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        const res = await api.get('/dashboard');
        setData(res.data?.data || null);
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load dashboard', 'error');
      } finally {
        setLoading(false);
      }
    };
    load();
  }, [push]);

  return (
    <AppLayout>
      <div className="page-header">
        <h1>Dashboard</h1>
        <p>Live production overview.</p>
      </div>

      {loading && <div className="card">Loading...</div>}

      {!loading && data && (
        <div className="grid">
          <div className="card">
            <h3>Active Work Orders</h3>
            <p className="metric">{data.stats?.activeWorkOrders ?? 0}</p>
          </div>
          <div className="card">
            <h3>Total Items</h3>
            <p className="metric">{data.stats?.totalItems ?? 0}</p>
          </div>
          <div className="card">
            <h3>Pending POs</h3>
            <p className="metric">{data.stats?.pendingPOs ?? 0}</p>
          </div>
          <div className="card">
            <h3>Quality Issues</h3>
            <p className="metric">{data.stats?.qualityIssues ?? 0}</p>
          </div>
        </div>
      )}

      {!loading && data && (
        <div className="grid">
          <div className="card">
            <h3>Recent Work Orders</h3>
            <ul className="list">
              {(data.tables?.workOrders || []).map((wo: any) => (
                <li key={wo.id}>{wo.woNumber} — {wo.product} — {wo.status}</li>
              ))}
            </ul>
          </div>
          <div className="card">
            <h3>Recent Items</h3>
            <ul className="list">
              {(data.tables?.inventory || []).map((it: any) => (
                <li key={it.id}>{it.itemCode} — {it.name} — {it.quantity}</li>
              ))}
            </ul>
          </div>
        </div>
      )}
    </AppLayout>
  );
}
