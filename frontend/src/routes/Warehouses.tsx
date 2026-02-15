import { useEffect, useState } from 'react';
import { AppLayout } from '../components/layout/AppLayout';
import { api } from '../lib/api';
import { Link } from 'react-router-dom';
import { useToast } from '../lib/toast';

export function Warehouses() {
  const [warehouses, setWarehouses] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const { push } = useToast();

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        const res = await api.get('/inventory/warehouses-crud');
        setWarehouses(res.data?.data || []);
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load warehouses', 'error');
      } finally {
        setLoading(false);
      }
    };
    load();
  }, [push]);

  return (
    <AppLayout>
      <div className="page-header header-row">
        <div>
          <h1>Warehouses</h1>
          <p>Storage locations and inventory points.</p>
        </div>
        <Link className="button" to="/inventory/warehouses/new">Create Warehouse</Link>
      </div>

      <div className="card">
        {loading && <p>Loading...</p>}
        {!loading && (
          <table className="table">
            <thead>
              <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Active</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {warehouses.map((wh) => (
                <tr key={wh.id}>
                  <td>{wh.code}</td>
                  <td>{wh.name}</td>
                  <td>{wh.type}</td>
                  <td>{wh.is_active ? 'Yes' : 'No'}</td>
                  <td>
                    <Link className="link" to={`/inventory/warehouses/${wh.id}/edit`}>Edit</Link>
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
