import { useState } from 'react';
import { api } from '../lib/api';
import { AppLayout } from '../components/layout/AppLayout';
import { useToast } from '../lib/toast';

export function Health() {
  const [status, setStatus] = useState<'idle' | 'loading' | 'ok' | 'error'>('idle');
  const { push } = useToast();

  const ping = async () => {
    try {
      setStatus('loading');
      await api.get('/auth/me');
      setStatus('ok');
      push('API health check passed', 'success');
    } catch (e: any) {
      setStatus('error');
      push(e?.response?.data?.message || 'API health check failed', 'error');
    }
  };

  return (
    <AppLayout>
      <div className="page-header">
        <h1>API Health</h1>
        <p>Calls /api/auth/me to validate auth middleware.</p>
      </div>
      <div className="card">
        <button onClick={ping}>Ping</button>
        <p className="muted">
          {status === 'idle' && 'No checks run yet.'}
          {status === 'loading' && 'Checking API...'}
          {status === 'ok' && 'API is reachable.'}
          {status === 'error' && 'API check failed.'}
        </p>
      </div>
    </AppLayout>
  );
}
