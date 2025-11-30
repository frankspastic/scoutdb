import client from './client';

export const scoutsAPI = {
  // Get all scouts
  getAll: (params = {}) =>
    client.get('/scouts', { params }),

  // Get single scout
  getById: (id) =>
    client.get(`/scouts/${id}`),

  // Create scout
  create: (data) =>
    client.post('/scouts', data),

  // Update scout
  update: (id, data) =>
    client.put(`/scouts/${id}`, data),

  // Delete scout
  delete: (id) =>
    client.delete(`/scouts/${id}`),

  // Get expiring scouts
  getExpiring: (days = 60, params = {}) =>
    client.get('/scouts/expiring/list', {
      params: { days, ...params }
    }),

  // Get scouts by den
  getByDen: (den) =>
    client.get(`/scouts/den/${den}`),

  // Filter scouts by status
  getByStatus: (status, params = {}) =>
    client.get('/scouts', {
      params: { status, ...params }
    }),

  // Filter scouts by rank
  getByRank: (rank, params = {}) =>
    client.get('/scouts', {
      params: { rank, ...params }
    }),

  // Search scouts
  search: (query, params = {}) =>
    client.get('/scouts', {
      params: { search: query, ...params }
    }),
};
