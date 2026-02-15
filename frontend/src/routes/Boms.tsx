import { useEffect, useState } from 'react';
import { AppLayout } from '../components/layout/AppLayout';
import { api } from '../lib/api';
import { Link } from 'react-router-dom';
import { useToast } from '../lib/toast';

export function Boms() {
  const [boms, setBoms] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const { push } = useToast();

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        const res = await api.get('/manufacturing/boms-crud');
        setBoms(res.data?.data || []);
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load BOMs', 'error');
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
          <h1>BOMs</h1>
          <p>Bill of Materials master.</p>
        </div>
        <Link className="button" to="/manufacturing/boms/new">Create BOM</Link>
      </div>

      <div className="card">
        {loading && <p>Loading...</p>}
        {!loading && (
          <table className="table">
            <thead>
              <tr>
                <th>Number</th>
                <th>Version</th>
                <th>Active</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {boms.map((bom) => (
                <tr key={bom.id}>
                  <td>{bom.bom_number}</td>
                  <td>{bom.version}</td>
                  <td>{bom.is_active ? 'Yes' : 'No'}</td>
                  <td>
                    <Link className="link" to={`/manufacturing/boms/${bom.id}/edit`}>Edit</Link>
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
