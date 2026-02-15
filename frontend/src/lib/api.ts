import axios from 'axios';

const rawBaseURL = (import.meta.env.VITE_API_BASE_URL as string | undefined)?.trim() ?? '';
const normalizedBaseURL = rawBaseURL.replace(/\/+$/, '');
const baseURL = normalizedBaseURL === ''
  ? '/api'
  : (normalizedBaseURL.endsWith('/api') ? normalizedBaseURL : `${normalizedBaseURL}/api`);

export const api = axios.create({
  baseURL,
  withCredentials: false,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    if (status === 401) {
      localStorage.removeItem('access_token');
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);
