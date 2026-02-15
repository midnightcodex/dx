import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { api } from './api';

export type User = {
  id: string;
  name: string;
  email: string;
  organization_id?: string;
  roles?: Array<{ id: string; slug: string; name: string }>;
  permissions?: Array<{ id: string; slug: string; name: string }>;
};

type AuthState = {
  user: User | null;
  loading: boolean;
  token: string | null;
};

type AuthContextValue = AuthState & {
  login: (email: string, password: string) => Promise<boolean>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const [token, setToken] = useState<string | null>(() => localStorage.getItem('access_token'));

  const refresh = useCallback(async () => {
    try {
      setLoading(true);
      const res = await api.get('/auth/me');
      const me = res.data?.data?.user || null;
      setUser(me);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    refresh();
  }, [refresh]);

  const login = useCallback(async (email: string, password: string) => {
    try {
      const res = await api.post('/auth/login', { email, password });
      const accessToken = res.data?.data?.access_token;
      if (accessToken) {
        localStorage.setItem('access_token', accessToken);
        setToken(accessToken);
        await refresh();
        return true;
      }
      return false;
    } catch {
      return false;
    }
  }, [refresh]);

  const logout = useCallback(async () => {
    try {
      await api.post('/auth/logout');
    } catch {
      // ignore
    } finally {
      localStorage.removeItem('access_token');
      setToken(null);
      setUser(null);
    }
  }, []);

  const value = useMemo(() => ({ user, loading, token, login, logout, refresh }), [user, loading, token, login, logout, refresh]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
}
