import client from './client';

export const dashboardAPI = {
  // Get dashboard statistics
  getStatistics: () =>
    client.get('/dashboard/statistics'),

  // Get recent activity
  getRecentActivity: (limit = 10) =>
    client.get('/dashboard/activity', {
      params: { limit }
    }),

  // Get expiring records
  getExpiringRecords: (days = 60) =>
    client.get('/dashboard/expiring', {
      params: { days }
    }),

  // Get orphaned persons
  getOrphanedPersons: (params = {}) =>
    client.get('/dashboard/orphaned', { params }),

  // Get sync status
  getSyncStatus: () =>
    client.get('/dashboard/sync-status'),

  // Get sync history
  getSyncHistory: (type = null, limit = 10) =>
    client.get('/dashboard/sync-history', {
      params: {
        ...(type && { type }),
        limit
      }
    }),

  // Get family members summary
  getFamilyMembers: (familyId) =>
    client.get(`/dashboard/family/${familyId}`),

  // Get den membership statistics
  getDenMembership: () =>
    client.get('/dashboard/dens'),

  // Get rank distribution
  getRankDistribution: () =>
    client.get('/dashboard/ranks'),
};
