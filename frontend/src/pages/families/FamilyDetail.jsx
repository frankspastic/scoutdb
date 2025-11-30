import React from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import { familiesAPI } from '../../api/families';

export default function FamilyDetail() {
  const { id } = useParams();
  const navigate = useNavigate();

  const { data: family, isLoading, error } = useQuery({
    queryKey: ['family', id],
    queryFn: () => familiesAPI.getById(id),
  });

  if (isLoading) return <div>Loading...</div>;
  if (error) return <div className="text-red-600">Error loading family</div>;

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">{family?.data?.name}</h1>
        <div className="space-x-2">
          <Link
            to={`/families/${id}/edit`}
            className="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
          >
            Edit
          </Link>
          <button
            onClick={() => navigate('/families')}
            className="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
          >
            Back
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Family Info */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold mb-4">Family Information</h2>
          <div className="space-y-3">
            <div>
              <label className="text-sm font-semibold text-gray-600">Address</label>
              <p className="text-gray-900">
                {family?.data?.street_address && `${family.data.street_address}, `}
                {family?.data?.city && `${family.data.city}, `}
                {family?.data?.state && `${family.data.state} `}
                {family?.data?.zip}
              </p>
            </div>
            <div>
              <label className="text-sm font-semibold text-gray-600">Phone</label>
              <p className="text-gray-900">{family?.data?.primary_phone}</p>
            </div>
            {family?.data?.notes && (
              <div>
                <label className="text-sm font-semibold text-gray-600">Notes</label>
                <p className="text-gray-900">{family.data.notes}</p>
              </div>
            )}
          </div>
        </div>

        {/* Family Members */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-xl font-bold mb-4">Family Members</h2>
          <div className="space-y-2">
            <p className="text-sm">
              <span className="font-semibold">Scouts:</span> {family?.data?.scouts?.length || 0}
            </p>
            <p className="text-sm">
              <span className="font-semibold">Parents:</span> {family?.data?.parents?.length || 0}
            </p>
            <p className="text-sm">
              <span className="font-semibold">Siblings:</span> {family?.data?.siblings?.length || 0}
            </p>
            <p className="text-sm">
              <span className="font-semibold">Leaders:</span> {family?.data?.leaders?.length || 0}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
