import client from './client';

export const personsAPI = {
  // Get all persons
  getAll: (params = {}) =>
    client.get('/persons', { params }),

  // Get single person
  getById: (id) =>
    client.get(`/persons/${id}`),

  // Create person
  create: (data) =>
    client.post('/persons', data),

  // Update person
  update: (id, data) =>
    client.put(`/persons/${id}`, data),

  // Delete person
  delete: (id) =>
    client.delete(`/persons/${id}`),

  // Get persons by family
  getByFamily: (familyId, params = {}) =>
    client.get('/persons', {
      params: { family_id: familyId, ...params }
    }),

  // Get persons by type
  getByType: (personType, params = {}) =>
    client.get('/persons', {
      params: { person_type: personType, ...params }
    }),

  // Search orphaned persons
  searchOrphaned: (query = '', params = {}) =>
    client.get('/persons/orphaned/search', {
      params: { search: query, ...params }
    }),

  // Merge persons
  merge: (primaryId, mergeId) =>
    client.post('/persons/merge', {
      primary_id: primaryId,
      merge_id: mergeId,
    }),

  // Search persons
  search: (query, params = {}) =>
    client.get('/persons', {
      params: { search: query, ...params }
    }),
};
