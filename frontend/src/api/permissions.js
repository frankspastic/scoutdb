import client from './client';

export const permissionsAPI = {
  // Get all permissions
  getAll: (params = {}) =>
    client.get('/permissions', { params }),

  // Get single permission
  getById: (id) =>
    client.get(`/permissions/${id}`),

  // Create permission
  create: (data) =>
    client.post('/permissions', data),

  // Update permission
  update: (id, data) =>
    client.put(`/permissions/${id}`, data),

  // Delete permission
  delete: (id) =>
    client.delete(`/permissions/${id}`),

  // Get permissions by role
  getByRole: (role, params = {}) =>
    client.get(`/permissions/role/${role}`, { params }),

  // Get permission by WordPress user
  getByWordPressUser: (wordpressUserId) =>
    client.get(`/permissions/wordpress/${wordpressUserId}`),

  // Get all admins
  getAdmins: () =>
    client.get('/permissions/admins/list'),

  // Search permissions
  search: (query, params = {}) =>
    client.get('/permissions', {
      params: { search: query, ...params }
    }),
};
