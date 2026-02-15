import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { useToast } from '../lib/toast';

type Option = { id: string; name?: string; item_code?: string };

type Warehouse = { id: string; code: string; name: string };

type Bom = { id: string; item_id: string; bom_code: string; revision: number };

type FormState = {
  item_id: string;
  bom_id: string;
  planned_quantity: string;
  scheduled_start_date: string;
  scheduled_end_date: string;
  source_warehouse_id: string;
  target_warehouse_id: string;
  status: string;
};

export function WorkOrderForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { push } = useToast();
  const [loading, setLoading] = useState(false);
  const [warehouses, setWarehouses] = useState<Warehouse[]>([]);
  const [boms, setBoms] = useState<Bom[]>([]);
  const [items, setItems] = useState<Option[]>([]);
  const [form, setForm] = useState<FormState>({
    item_id: '',
    bom_id: '',
    planned_quantity: '',
    scheduled_start_date: '',
    scheduled_end_date: '',
    source_warehouse_id: '',
    target_warehouse_id: '',
    status: 'PLANNED',
  });

  useEffect(() => {
    const load = async () => {
      try {
        const [whRes, bomRes, itemRes] = await Promise.all([
          api.get('/inventory/warehouses'),
          api.get('/manufacturing/boms'),
          api.get('/inventory/items-active'),
        ]);
        setWarehouses(whRes.data?.data || []);
        setBoms(bomRes.data?.data || []);
        setItems(itemRes.data?.data || []);

        if (id) {
          const res = await api.get(`/manufacturing/work-orders/${id}`);
          const wo = res.data?.data;
          setForm({
            item_id: wo.item_id || '',
            bom_id: wo.bom_id || '',
            planned_quantity: wo.planned_quantity ? String(wo.planned_quantity) : '',
            scheduled_start_date: wo.scheduled_start_date || '',
            scheduled_end_date: wo.scheduled_end_date || '',
            source_warehouse_id: wo.source_warehouse_id || '',
            target_warehouse_id: wo.target_warehouse_id || '',
            status: wo.status || 'PLANNED',
          });
        }
      } catch (e: any) {
        push(e?.response?.data?.message || 'Failed to load work order form data', 'error');
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
      planned_quantity: form.planned_quantity ? Number(form.planned_quantity) : null,
    };

    try {
      if (id) {
        await api.put(`/manufacturing/work-orders/${id}`, payload);
        push('Work order updated', 'success');
      } else {
        await api.post('/manufacturing/work-orders', payload);
        push('Work order created', 'success');
      }
      navigate('/manufacturing/work-orders');
    } catch (e: any) {
      push(e?.response?.data?.message || 'Failed to save work order', 'error');
    } finally {
      setLoading(false);
    }
  };

  const bomsForItem = form.item_id ? boms.filter((b) => b.item_id === form.item_id) : boms;

  return (
    <AppLayout>
      <div className="page-header">
        <h1>{id ? 'Edit Work Order' : 'Create Work Order'}</h1>
        <p>Plan and manage production work orders.</p>
      </div>

      <form className="card form" onSubmit={onSubmit}>
        <div className="form-grid">
          <label>
            Item (Finished Good)
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
            BOM
            <select value={form.bom_id} onChange={(e) => onChange('bom_id', e.target.value)}>
              <option value="">Select BOM</option>
              {bomsForItem.map((bom) => (
                <option key={bom.id} value={bom.id}>
                  {bom.bom_code} (Rev {bom.revision})
                </option>
              ))}
            </select>
          </label>
          <label>
            Planned Quantity
            <input value={form.planned_quantity} onChange={(e) => onChange('planned_quantity', e.target.value)} />
          </label>
          <label>
            Scheduled Start Date
            <input type="date" value={form.scheduled_start_date} onChange={(e) => onChange('scheduled_start_date', e.target.value)} />
          </label>
          <label>
            Scheduled End Date
            <input type="date" value={form.scheduled_end_date} onChange={(e) => onChange('scheduled_end_date', e.target.value)} />
          </label>
          <label>
            Source Warehouse
            <select value={form.source_warehouse_id} onChange={(e) => onChange('source_warehouse_id', e.target.value)}>
              <option value="">Select Source</option>
              {warehouses.map((wh) => (
                <option key={wh.id} value={wh.id}>
                  {wh.code} - {wh.name}
                </option>
              ))}
            </select>
          </label>
          <label>
            Target Warehouse
            <select value={form.target_warehouse_id} onChange={(e) => onChange('target_warehouse_id', e.target.value)}>
              <option value="">Select Target</option>
              {warehouses.map((wh) => (
                <option key={wh.id} value={wh.id}>
                  {wh.code} - {wh.name}
                </option>
              ))}
            </select>
          </label>
          {id && (
            <label>
              Status
              <select value={form.status} onChange={(e) => onChange('status', e.target.value)}>
                <option value="PLANNED">PLANNED</option>
                <option value="RELEASED">RELEASED</option>
                <option value="IN_PROGRESS">IN_PROGRESS</option>
                <option value="COMPLETED">COMPLETED</option>
                <option value="CANCELLED">CANCELLED</option>
              </select>
            </label>
          )}
        </div>

        <div className="form-actions">
          <button type="submit" disabled={loading}>{loading ? 'Saving...' : 'Save Work Order'}</button>
        </div>
      </form>
    </AppLayout>
  );
}
