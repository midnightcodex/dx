import { useEffect, useState } from 'react';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { Link } from 'react-router-dom';
import { useToast } from '../lib/toast';

export function InventoryItems() {
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const { push } = useToast();

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        const res = await api.get('/inventory/items?per_page=15');
        setItems(res.data?.data || []);
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load items', 'error');
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
          <h1>Inventory Items</h1>
          <p>Master list of stockable items.</p>
        </div>
        <Link className="button" to="/inventory/items/new">Create Item</Link>
      </div>

      <div className="card">
        {loading && <p>Loading...</p>}
        {!loading && (
          <table className="table">
            <thead>
              <tr>
                <th>Item Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Stock Type</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {items.map((item) => (
                <tr key={item.id}>
                  <td>{item.item_code}</td>
                  <td>{item.name}</td>
                  <td>{item.item_type}</td>
                  <td>{item.stock_type}</td>
                  <td>
                    <Link className="link" to={`/inventory/items/${item.id}/edit`}>Edit</Link>
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
