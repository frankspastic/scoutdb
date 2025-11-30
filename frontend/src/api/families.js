import client from './client';

export const familiesAPI = {
  // Get all families
  getAll: (params = {}) =>
    client.get('/families', { params }),

  // Get single family
  getById: (id) =>
    client.get(`/families/${id}`),

  // Create family
  create: (data) =>
    client.post('/families', data),

  // Update family
  update: (id, data) =>
    client.put(`/families/${id}`, data),

  // Delete family
  delete: (id) =>
    client.delete(`/families/${id}`),

  // Merge families
  merge: (primaryId, mergeId) =>
    client.post('/families/merge', {
      primary_id: primaryId,
      merge_id: mergeId,
    }),

  // Search families
  search: (query, params = {}) =>
    client.get('/families', {
      params: { search: query, ...params }
    }),
};
