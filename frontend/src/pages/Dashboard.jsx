import React from 'react';
import { useQuery } from '@tanstack/react-query';
import { dashboardAPI } from '../api/dashboard';

export default function Dashboard() {
  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard', 'statistics'],
    queryFn: dashboardAPI.getStatistics,
  });

  if (isLoading) {
    return <div>Loading dashboard...</div>;
  }

  return (
    <div>
      <h1 className="text-3xl font-bold mb-6">Dashboard</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {/* Families Card */}
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm font-medium">Total Families</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.families?.total || 0}</p>
            </div>
            <div className="text-4xl">👨‍👩‍👧‍👦</div>
          </div>
        </div>

        {/* Scouts Card */}
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm font-medium">Active Scouts</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.scouts?.active || 0}</p>
            </div>
            <div className="text-4xl">🔔</div>
          </div>
        </div>

        {/* Expiring Scouts Card */}
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm font-medium">Expiring Soon</p>
              <p className="text-3xl font-bold text-orange-600">{stats?.scouts?.expiring_soon || 0}</p>
            </div>
            <div className="text-4xl">⚠️</div>
          </div>
        </div>

        {/* Leaders Card */}
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-gray-600 text-sm font-medium">Adult Leaders</p>
              <p className="text-3xl font-bold text-gray-900">{stats?.leaders?.total || 0}</p>
            </div>
            <div className="text-4xl">👔</div>
          </div>
        </div>
      </div>

      {/* Detailed Stats */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Person Types */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold mb-4">Person Types</h2>
          <div className="space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Scouts</span>
              <span className="font-bold">{stats?.persons?.scouts || 0}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Parents</span>
              <span className="font-bold">{stats?.persons?.parents || 0}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Siblings</span>
              <span className="font-bold">{stats?.persons?.siblings || 0}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Leaders</span>
              <span className="font-bold">{stats?.persons?.leaders || 0}</span>
            </div>
            <div className="flex justify-between items-center border-t pt-3">
              <span className="text-gray-600 font-medium">Orphaned</span>
              <span className="font-bold text-orange-600">{stats?.persons?.orphaned || 0}</span>
            </div>
          </div>
        </div>

        {/* YPT Status */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold mb-4">YPT Status</h2>
          <div className="space-y-3">
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Current</span>
              <span className="font-bold text-green-600">{stats?.leaders?.ypt_current || 0}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Expiring Soon</span>
              <span className="font-bold text-orange-600">{stats?.leaders?.ypt_expiring_soon || 0}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-gray-600">Expired</span>
              <span className="font-bold text-red-600">{stats?.leaders?.ypt_expired || 0}</span>
            </div>
            <div className="flex justify-between items-center border-t pt-3">
              <span className="text-gray-600 font-medium">Unknown</span>
              <span className="font-bold text-gray-600">{stats?.leaders?.ypt_unknown || 0}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
