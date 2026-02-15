import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { Link } from 'react-router-dom';

export function WorkOrders() {
  const [orders, setOrders] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        const res = await api.get('/manufacturing/work-orders?per_page=15');
        setOrders(res.data?.data || []);
      } catch (e: any) {
        setError(e?.response?.data?.message || 'Failed to load work orders');
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  return (
    <AppLayout>
      <div className="page-header header-row">
        <div>
          <h1>Work Orders</h1>
          <p>Production work orders.</p>
        </div>
        <Link className="button" to="/manufacturing/work-orders/new">Create Work Order</Link>
      </div>

      <div className="card">
        {loading && <p>Loading...</p>}
        {error && <p className="muted">{error}</p>}
        {!loading && !error && (
          <table className="table">
            <thead>
              <tr>
                <th>WO Number</th>
                <th>Status</th>
                <th>Planned Qty</th>
                <th>Start Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {orders.map((wo) => (
                <tr key={wo.id}>
                  <td>{wo.wo_number}</td>
                  <td>{wo.status}</td>
                  <td>{wo.planned_quantity}</td>
                  <td>{wo.scheduled_start_date || '-'}</td>
                  <td>
                    <Link className="link" to={`/manufacturing/work-orders/${wo.id}/edit`}>Edit</Link>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </AppLayout>
  );
}
