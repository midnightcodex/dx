import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { useToast } from '../lib/toast';

type Option = {
  id: string;
  name?: string;
  item_code?: string;
  uom_code?: string;
  uom_name?: string;
  category_name?: string;
  category_code?: string;
};

type FormState = {
  item_code: string;
  name: string;
  primary_uom_id: string;
  category_id: string;
  item_type: string;
  stock_type: string;
  is_batch_tracked: boolean;
  is_serial_tracked: boolean;
  standard_cost: string;
};

export function InventoryItemForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { push } = useToast();
  const [loading, setLoading] = useState(false);
  const [uoms, setUoms] = useState<Option[]>([]);
  const [categories, setCategories] = useState<Option[]>([]);

  const [form, setForm] = useState<FormState>({
    item_code: '',
    name: '',
    primary_uom_id: '',
    category_id: '',
    item_type: 'STOCKABLE',
    stock_type: 'RAW_MATERIAL',
    is_batch_tracked: false,
    is_serial_tracked: false,
    standard_cost: '',
  });

  useEffect(() => {
    const load = async () => {
      try {
        const [uomRes, catRes] = await Promise.all([
          api.get('/inventory/uoms'),
          api.get('/inventory/item-categories'),
        ]);
        setUoms(uomRes.data?.data || []);
        setCategories(catRes.data?.data || []);

        if (!id) return;

        const res = await api.get(`/inventory/items/${id}`);
        const item = res.data?.data;
        setForm({
          item_code: item.item_code || '',
          name: item.name || '',
          primary_uom_id: item.primary_uom_id || '',
          category_id: item.category_id || '',
          item_type: item.item_type || 'STOCKABLE',
          stock_type: item.stock_type || 'RAW_MATERIAL',
          is_batch_tracked: !!item.is_batch_tracked,
          is_serial_tracked: !!item.is_serial_tracked,
          standard_cost: item.standard_cost ? String(item.standard_cost) : '',
        });
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load item form data', 'error');
      }
    };

    load();
  }, [id, push]);

  const onChange = (key: keyof FormState, value: any) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    const payload = {
      ...form,
      standard_cost: form.standard_cost ? Number(form.standard_cost) : null,
    };

    try {
      if (id) {
        await api.put(`/inventory/items/${id}`, payload);
        push('Item updated', 'success');
      } else {
        await api.post('/inventory/items', payload);
        push('Item created', 'success');
      }
      navigate('/inventory/items');
    } catch (e: any) {
      push(e?.response?.data?.message || 'Failed to save item', 'error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <AppLayout>
      <div className="page-header">
        <h1>{id ? 'Edit Item' : 'Create Item'}</h1>
        <p>Maintain inventory master data.</p>
      </div>

      <form className="card form" onSubmit={onSubmit}>
        <div className="form-grid">
          <label>
            Item Code
            <input value={form.item_code} onChange={(e) => onChange('item_code', e.target.value)} />
          </label>
          <label>
            Name
            <input value={form.name} onChange={(e) => onChange('name', e.target.value)} />
          </label>
          <label>
            Primary UOM
            <select value={form.primary_uom_id} onChange={(e) => onChange('primary_uom_id', e.target.value)}>
              <option value="">Select UOM</option>
              {uoms.map((uom) => (
                <option key={uom.id} value={uom.id}>
                  {uom.uom_code} - {uom.uom_name}
                </option>
              ))}
            </select>
          </label>
          <label>
            Category
            <select value={form.category_id} onChange={(e) => onChange('category_id', e.target.value)}>
              <option value="">Select Category</option>
              {categories.map((cat) => (
                <option key={cat.id} value={cat.id}>
                  {cat.category_code} - {cat.category_name}
                </option>
              ))}
            </select>
          </label>
          <label>
            Item Type
            <select value={form.item_type} onChange={(e) => onChange('item_type', e.target.value)}>
              <option value="STOCKABLE">STOCKABLE</option>
              <option value="SERVICE">SERVICE</option>
              <option value="CONSUMABLE">CONSUMABLE</option>
            </select>
          </label>
          <label>
            Stock Type
            <select value={form.stock_type} onChange={(e) => onChange('stock_type', e.target.value)}>
              <option value="RAW_MATERIAL">RAW_MATERIAL</option>
              <option value="WIP">WIP</option>
              <option value="FINISHED_GOOD">FINISHED_GOOD</option>
              <option value="SPARE_PART">SPARE_PART</option>
            </select>
          </label>
          <label>
            Standard Cost
            <input value={form.standard_cost} onChange={(e) => onChange('standard_cost', e.target.value)} />
          </label>
          <label>
            Batch Tracked
            <select value={String(form.is_batch_tracked)} onChange={(e) => onChange('is_batch_tracked', e.target.value === 'true')}>
              <option value="false">No</option>
              <option value="true">Yes</option>
            </select>
          </label>
          <label>
            Serial Tracked
            <select value={String(form.is_serial_tracked)} onChange={(e) => onChange('is_serial_tracked', e.target.value === 'true')}>
              <option value="false">No</option>
              <option value="true">Yes</option>
            </select>
          </label>
        </div>

        <div className="form-actions">
          <button type="submit" disabled={loading}>{loading ? 'Saving...' : 'Save Item'}</button>
        </div>
      </form>
    </AppLayout>
  );
}
