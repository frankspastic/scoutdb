import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';

export const Sidebar = () => {
  const location = useLocation();
  const { user } = useAuth();

  const isActive = (path) => location.pathname === path || location.pathname.startsWith(path + '/');

  const navItem = (path, label, icon) => (
    <Link
      to={path}
      className={`flex items-center space-x-3 px-4 py-3 rounded-lg transition ${
        isActive(path)
          ? 'bg-blue-600 text-white'
          : 'text-gray-700 hover:bg-gray-100'
      }`}
    >
      <span className="text-lg">{icon}</span>
      <span>{label}</span>
    </Link>
  );

  return (
    <aside className="w-64 bg-gray-50 border-r border-gray-200 min-h-screen">
      <nav className="p-4 space-y-2">
        {navItem('/', 'Dashboard', '📊')}
        {navItem('/families', 'Families', '👨‍👩‍👧‍👦')}
        {navItem('/persons', 'Persons', '👤')}
        {navItem('/scouts', 'Scouts', '🔔')}
        {navItem('/leaders', 'Leaders', '👔')}

        {user?.role === 'admin' && (
          <>
            <hr className="my-4" />
            <div className="px-4 py-2 text-sm font-semibold text-gray-600 uppercase">
              Administration
            </div>
            {navItem('/admin/users', 'User Management', '👥')}
          </>
        )}
      </nav>
    </aside>
  );
};
