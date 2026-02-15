import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { useToast } from '../lib/toast';

export function WarehouseForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { push } = useToast();
  const [loading, setLoading] = useState(false);
  const [form, setForm] = useState({
    name: '',
    code: '',
    type: 'WAREHOUSE',
    address: '',
    allow_negative_stock: false,
    is_active: true,
  });

  useEffect(() => {
    if (!id) return;
    const load = async () => {
      try {
        const res = await api.get(`/inventory/warehouses-crud/${id}`);
        const wh = res.data?.data;
        setForm({
          name: wh.name || '',
          code: wh.code || '',
          type: wh.type || 'WAREHOUSE',
          address: wh.address || '',
          allow_negative_stock: !!wh.allow_negative_stock,
          is_active: !!wh.is_active,
        });
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load warehouse form data', 'error');
      }
    };
    load();
  }, [id, push]);

  const onChange = (key: string, value: any) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      if (id) {
        await api.put(`/inventory/warehouses-crud/${id}`, form);
        push('Warehouse updated', 'success');
      } else {
        await api.post('/inventory/warehouses-crud', form);
        push('Warehouse created', 'success');
      }
      navigate('/inventory/warehouses');
    } catch (e: any) {
      push(e?.response?.data?.message || 'Failed to save warehouse', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <div className="page-header">
        <h1>{id ? 'Edit Warehouse' : 'Create Warehouse'}</h1>
      </div>
      <form className="card form" onSubmit={onSubmit}>
        <div className="form-grid">
          <label>
            Name
            <input value={form.name} onChange={(e) => onChange('name', e.target.value)} />
          </label>
          <label>
            Code
            <input value={form.code} onChange={(e) => onChange('code', e.target.value)} />
          </label>
          <label>
            Type
            <select value={form.type} onChange={(e) => onChange('type', e.target.value)}>
              <option value="WAREHOUSE">WAREHOUSE</option>
              <option value="SHOP_FLOOR">SHOP_FLOOR</option>
              <option value="TRANSIT">TRANSIT</option>
              <option value="QUARANTINE">QUARANTINE</option>
            </select>
          </label>
          <label>
            Address
            <input value={form.address} onChange={(e) => onChange('address', e.target.value)} />
          </label>
          <label>
            Allow Negative Stock
            <select value={String(form.allow_negative_stock)} onChange={(e) => onChange('allow_negative_stock', e.target.value === 'true')}>
              <option value="false">No</option>
              <option value="true">Yes</option>
            </select>
          </label>
          <label>
            Active
            <select value={String(form.is_active)} onChange={(e) => onChange('is_active', e.target.value === 'true')}>
              <option value="true">Yes</option>
              <option value="false">No</option>
            </select>
          </label>
        </div>
        <div className="form-actions">
          <button type="submit" disabled={loading}>{loading ? 'Saving...' : 'Save Warehouse'}</button>
        </div>
      </form>
    </AppLayout>
  );
}
