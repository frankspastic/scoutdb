import client from './client';

export const leadersAPI = {
  // Get all leaders
  getAll: (params = {}) =>
    client.get('/leaders', { params }),

  // Get single leader
  getById: (id) =>
    client.get(`/leaders/${id}`),

  // Create leader
  create: (data) =>
    client.post('/leaders', data),

  // Update leader
  update: (id, data) =>
    client.put(`/leaders/${id}`, data),

  // Delete leader
  delete: (id) =>
    client.delete(`/leaders/${id}`),

  // Get leaders with expiring YPT
  getExpiringYPT: (days = 30, params = {}) =>
    client.get('/leaders/expiring/soon', {
      params: { days, ...params }
    }),

  // Filter by YPT status
  getByYPTStatus: (status, params = {}) =>
    client.get('/leaders', {
      params: { ypt_status: status, ...params }
    }),

  // Add position to leader
  addPosition: (leaderId, position) =>
    client.post(`/leaders/${leaderId}/positions`, {
      position
    }),

  // Remove position from leader
  removePosition: (leaderId, position) =>
    client.delete(`/leaders/${leaderId}/positions`, {
      data: { position }
    }),

  // Search leaders
  search: (query, params = {}) =>
    client.get('/leaders', {
      params: { search: query, ...params }
    }),
};
