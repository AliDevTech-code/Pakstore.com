import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { Users, Search, Trash2, MapPin, Phone, Mail, Calendar } from 'lucide-react';

export const AdminUsersTab: React.FC = () => {
  const { users, deleteUser } = useStore();
  const [searchTerm, setSearchTerm] = useState('');

  const filteredUsers = users.filter((u) => {
    const q = searchTerm.toLowerCase();
    return (
      u.fullName.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q) ||
      u.phone.includes(q) ||
      u.city.toLowerCase().includes(q)
    );
  });

  return (
    <div className="space-y-6 max-w-5xl">
      <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-[#00A651] flex items-center gap-1.5">
              <Users className="w-4 h-4" /> Customer Accounts
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-1">Registered Users & Customers</h2>
            <p className="text-xs text-slate-500 mt-0.5">
              PakStore par registered buyers ki complete details aur address directory.
            </p>
          </div>
          <span className="text-xs font-mono font-bold bg-emerald-50 text-[#00A651] px-2.5 py-1 rounded-lg border border-emerald-200 self-start sm:self-auto">
            {users.length} Total Registered Users
          </span>
        </div>

        {/* Search Bar */}
        <div className="mt-5 relative">
          <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5" />
          <input
            type="text"
            placeholder="Search users by name, phone, email, or city..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-4 py-2 text-xs border border-slate-300 rounded-xl focus:outline-[#00A651] bg-white text-slate-900"
          />
        </div>

        {/* Users Table */}
        <div className="mt-5 border border-slate-200 rounded-xl overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Customer Name</th>
                <th className="py-3 px-4">Contact Info</th>
                <th className="py-3 px-4">City & Delivery Address</th>
                <th className="py-3 px-4">Registered Date</th>
                <th className="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredUsers.length > 0 ? (
                filteredUsers.map((user) => (
                  <tr key={user.id} className="hover:bg-slate-50/80 transition-colors">
                    <td className="py-3.5 px-4">
                      <span className="font-bold text-slate-900 block">{user.fullName}</span>
                      <span className="text-[10px] text-slate-400 font-mono">ID: {user.id}</span>
                    </td>
                    <td className="py-3.5 px-4 space-y-0.5">
                      <div className="flex items-center gap-1.5 text-slate-700 font-mono">
                        <Phone className="w-3 h-3 text-[#00A651]" />
                        <span>{user.phone}</span>
                      </div>
                      <div className="flex items-center gap-1.5 text-slate-500">
                        <Mail className="w-3 h-3 text-slate-400" />
                        <span>{user.email}</span>
                      </div>
                    </td>
                    <td className="py-3.5 px-4 max-w-xs">
                      <div className="flex items-center gap-1 font-bold text-slate-800">
                        <MapPin className="w-3 h-3 text-[#FF6B00]" />
                        <span>{user.city}</span>
                      </div>
                      <p className="text-[11px] text-slate-500 truncate" title={user.address}>
                        {user.address}
                      </p>
                    </td>
                    <td className="py-3.5 px-4 font-mono text-slate-500">
                      <div className="flex items-center gap-1">
                        <Calendar className="w-3 h-3 text-slate-400" />
                        <span>{user.createdAt ? user.createdAt.split('T')[0] : '2026-10-15'}</span>
                      </div>
                    </td>
                    <td className="py-3.5 px-4 text-right">
                      <button
                        onClick={() => {
                          if (confirm(`Delete user account "${user.fullName}"?`)) {
                            deleteUser(user.id);
                          }
                        }}
                        className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg cursor-pointer transition-colors"
                        title="Delete User"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={5} className="py-8 text-center text-slate-400">
                    No users matching "{searchTerm}".
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
