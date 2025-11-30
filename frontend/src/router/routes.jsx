import React from 'react';
import { ProtectedRoute } from '../components/ProtectedRoute';

// Pages - will be created next
const Dashboard = React.lazy(() => import('../pages/Dashboard'));
const FamilyList = React.lazy(() => import('../pages/families/FamilyList'));
const FamilyDetail = React.lazy(() => import('../pages/families/FamilyDetail'));
const FamilyForm = React.lazy(() => import('../pages/families/FamilyForm'));
const PersonList = React.lazy(() => import('../pages/persons/PersonList'));
const PersonDetail = React.lazy(() => import('../pages/persons/PersonDetail'));
const PersonForm = React.lazy(() => import('../pages/persons/PersonForm'));
const ScoutList = React.lazy(() => import('../pages/scouts/ScoutList'));
const LeaderList = React.lazy(() => import('../pages/leaders/LeaderList'));
const UserManagement = React.lazy(() => import('../pages/admin/UserManagement'));
const NotFound = React.lazy(() => import('../pages/NotFound'));

export const routes = [
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <Dashboard />
      </ProtectedRoute>
    ),
  },
  {
    path: '/families',
    element: (
      <ProtectedRoute>
        <FamilyList />
      </ProtectedRoute>
    ),
  },
  {
    path: '/families/new',
    element: (
      <ProtectedRoute>
        <FamilyForm />
      </ProtectedRoute>
    ),
  },
  {
    path: '/families/:id',
    element: (
      <ProtectedRoute>
        <FamilyDetail />
      </ProtectedRoute>
    ),
  },
  {
    path: '/families/:id/edit',
    element: (
      <ProtectedRoute>
        <FamilyForm />
      </ProtectedRoute>
    ),
  },
  {
    path: '/persons',
    element: (
      <ProtectedRoute>
        <PersonList />
      </ProtectedRoute>
    ),
  },
  {
    path: '/persons/new',
    element: (
      <ProtectedRoute>
        <PersonForm />
      </ProtectedRoute>
    ),
  },
  {
    path: '/persons/:id',
    element: (
      <ProtectedRoute>
        <PersonDetail />
      </ProtectedRoute>
    ),
  },
  {
    path: '/persons/:id/edit',
    element: (
      <ProtectedRoute>
        <PersonForm />
      </ProtectedRoute>
    ),
  },
  {
    path: '/scouts',
    element: (
      <ProtectedRoute>
        <ScoutList />
      </ProtectedRoute>
    ),
  },
  {
    path: '/leaders',
    element: (
      <ProtectedRoute>
        <LeaderList />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/users',
    element: (
      <ProtectedRoute requiredRole="admin">
        <UserManagement />
      </ProtectedRoute>
    ),
  },
  {
    path: '*',
    element: <NotFound />,
  },
];
