import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { X, ShieldAlert, KeyRound, UserCheck, Lock } from 'lucide-react';

export const AdminLoginModal: React.FC = () => {
  const { isAdminLoginOpen, setIsAdminLoginOpen, adminLogin, adminCredentials } = useStore();
  const [username, setUsername] = useState(adminCredentials.email);
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  if (!isAdminLoginOpen) return null;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    const success = adminLogin(username.trim(), password);
    if (!success) {
      setError('Access Denied. Ghalat Admin Email ya Password.');
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div className="bg-slate-900 text-white rounded-2xl max-w-sm w-full overflow-hidden shadow-2xl border border-slate-700 relative animate-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="p-6 pb-4 border-b border-slate-800 text-center relative">
          <button
            onClick={() => setIsAdminLoginOpen(false)}
            className="absolute top-4 right-4 p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="w-12 h-12 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-center mx-auto mb-3 text-emerald-400">
            <Lock className="w-6 h-6" />
          </div>

          <span className="text-[10px] uppercase font-bold tracking-widest text-emerald-400 block">
            Store Management Portal
          </span>
          <h2 className="text-xl font-bold tracking-tight text-white mt-1">Admin Portal Login</h2>
          <p className="text-xs text-slate-400 mt-1">
            Apna set kiya hua Admin Email aur Password darj karein.
          </p>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {error && (
            <div className="p-3 text-xs bg-rose-950/80 text-rose-300 border border-rose-800 rounded-lg flex items-center gap-2">
              <ShieldAlert className="w-4 h-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          <div>
            <label className="block text-xs font-semibold text-slate-300 mb-1">
              Admin Login Email
            </label>
            <input
              type="email"
              required
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              placeholder="e.g. admin@pakstore.com"
              className="w-full px-3.5 py-2 text-xs bg-slate-800 border border-slate-700 rounded-lg text-white focus:outline-emerald-500 focus:border-emerald-500 font-mono"
            />
          </div>

          <div>
            <label className="block text-xs font-semibold text-slate-300 mb-1">
              Admin Password
            </label>
            <input
              type="password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Enter your admin password"
              className="w-full px-3.5 py-2 text-xs bg-slate-800 border border-slate-700 rounded-lg text-white focus:outline-emerald-500 focus:border-emerald-500"
            />
          </div>

          <div className="flex justify-between items-center text-[11px] pt-1 text-slate-400">
            <span>InfinityFree MySQL Sync</span>
            <span>Encrypted Session</span>
          </div>

          <button
            type="submit"
            className="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs transition-colors shadow-md flex items-center justify-center gap-2 cursor-pointer mt-2"
          >
            <KeyRound className="w-4 h-4" />
            <span>Authenticate & Access Dashboard</span>
          </button>
        </form>
      </div>
    </div>
  );
};
