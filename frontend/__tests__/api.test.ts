import { auth, products } from '../lib/api';

// Mock fetch globally
const mockFetch = jest.fn();
global.fetch = mockFetch;

beforeEach(() => {
  mockFetch.mockClear();
});

describe('API Auth', () => {
  test('login sends POST with correct data', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ token: 'fake-jwt', user: { id: 1, email: 'test@test.com' } }),
    });

    const result = await auth.login('test@test.com', 'password123');

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/login'),
      expect.objectContaining({
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: 'test@test.com', password: 'password123' }),
      })
    );
    expect(result.token).toBe('fake-jwt');
  });

  test('register sends POST with name, email, password', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ message: 'Registered', token: 'jwt-123' }),
    });

    const result = await auth.register('new@test.com', 'pass123', 'New User');

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/register'),
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({ email: 'new@test.com', password: 'pass123', name: 'New User' }),
      })
    );
    expect(result.message).toBe('Registered');
  });

  test('login throws on error response', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      json: async () => ({ error: 'Credenciales invalidas' }),
    });

    await expect(auth.login('bad@test.com', 'wrong')).rejects.toThrow('Credenciales invalidas');
  });
});

describe('API Products', () => {
  test('list sends GET with token', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => [{ id: 1, name: 'Product A' }]),
    });

    const result = await products.list('my-token');

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/products'),
      expect.objectContaining({
        method: 'GET',
        headers: expect.objectContaining({ Authorization: 'Bearer my-token' }),
      })
    );
    expect(result).toHaveLength(1);
  });

  test('create sends POST with token and data', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ id: 2, name: 'New Product' }),
    });

    const result = await products.create({ name: 'New Product', price: 10 }, 'my-token');

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/products'),
      expect.objectContaining({
        method: 'POST',
        headers: expect.objectContaining({ Authorization: 'Bearer my-token' }),
      })
    );
    expect(result.name).toBe('New Product');
  });

  test('delete sends DELETE with token', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      json: async () => ({ message: 'eliminado' }),
    });

    await products.delete(5, 'my-token');

    expect(mockFetch).toHaveBeenCalledWith(
      expect.stringContaining('/api/products/5'),
      expect.objectContaining({ method: 'DELETE' })
    );
  });
});
