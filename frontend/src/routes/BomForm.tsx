import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { useToast } from '../lib/toast';

export function BomForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { push } = useToast();
  const [loading, setLoading] = useState(false);
  const [items, setItems] = useState<any[]>([]);
  const [form, setForm] = useState({
    item_id: '',
    bom_number: '',
    version: 1,
    is_active: true,
    base_quantity: 1,
  });

  useEffect(() => {
    const load = async () => {
      try {
        const itemRes = await api.get('/inventory/items-active');
        setItems(itemRes.data?.data || []);

        if (id) {
          const res = await api.get(`/manufacturing/boms-crud/${id}`);
          const bom = res.data?.data;
          setForm({
            item_id: bom.item_id || '',
            bom_number: bom.bom_number || '',
            version: bom.version || 1,
            is_active: !!bom.is_active,
            base_quantity: bom.base_quantity || 1,
          });
        }
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load BOM form data', 'error');
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
        await api.put(`/manufacturing/boms-crud/${id}`, form);
        push('BOM updated', 'success');
      } else {
        await api.post('/manufacturing/boms-crud', form);
        push('BOM created', 'success');
      }
      navigate('/manufacturing/boms');
    } catch (e: any) {
      push(e?.response?.data?.message || 'Failed to save BOM', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <div className="page-header">
        <h1>{id ? 'Edit BOM' : 'Create BOM'}</h1>
      </div>
      <form className="card form" onSubmit={onSubmit}>
        <div className="form-grid">
          <label>
            Item
            <select value={form.item_id} onChange={(e) => onChange('item_id', e.target.value)}>
              <option value="">Select Item</option>
              {items.map((it) => (
                <option key={it.id} value={it.id}>
                  {it.item_code} - {it.name}
                </option>
              ))}
            </select>
          </label>
          <label>
            BOM Number
            <input value={form.bom_number} onChange={(e) => onChange('bom_number', e.target.value)} />
          </label>
          <label>
            Version
            <input type="number" value={form.version} onChange={(e) => onChange('version', Number(e.target.value))} />
          </label>
          <label>
            Base Quantity
            <input type="number" value={form.base_quantity} onChange={(e) => onChange('base_quantity', Number(e.target.value))} />
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
          <button type="submit" disabled={loading}>{loading ? 'Saving...' : 'Save BOM'}</button>
        </div>
      </form>
    </AppLayout>
  );
}
