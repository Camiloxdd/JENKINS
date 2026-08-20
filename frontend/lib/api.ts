const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

interface RequestOptions {
  method?: string;
  body?: any;
  token?: string;
}

export async function apiFetch(endpoint: string, options: RequestOptions = {}) {
  const { method = 'GET', body, token } = options;

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(`${API_URL}${endpoint}`, {
    method,
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });

  const data = await res.json();

  if (!res.ok) {
    throw new Error(data.error || 'Error en la peticion');
  }

  return data;
}

export const auth = {
  login: (email: string, password: string) =>
    apiFetch('/api/login', { method: 'POST', body: { email, password } }),

  register: (email: string, password: string, name: string) =>
    apiFetch('/api/register', { method: 'POST', body: { email, password, name } }),

  me: (token: string) =>
    apiFetch('/api/me', { token }),
};

export const products = {
  list: (token: string) =>
    apiFetch('/api/products', { token }),

  get: (id: number, token: string) =>
    apiFetch(`/api/products/${id}`, { token }),

  create: (data: any, token: string) =>
    apiFetch('/api/products', { method: 'POST', body: data, token }),

  update: (id: number, data: any, token: string) =>
    apiFetch(`/api/products/${id}`, { method: 'PUT', body: data, token }),

  delete: (id: number, token: string) =>
    apiFetch(`/api/products/${id}`, { method: 'DELETE', token }),
};
