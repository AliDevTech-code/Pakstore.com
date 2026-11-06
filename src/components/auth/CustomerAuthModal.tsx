import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { X, User, Phone, Mail, Lock, MapPin, CheckCircle, ArrowRight } from 'lucide-react';

export const CustomerAuthModal: React.FC = () => {
  const {
    isAuthModalOpen,
    authModalMode,
    setAuthModal,
    customerLogin,
    customerSignUp,
  } = useStore();

  const [mode, setMode] = useState<'login' | 'signup'>(authModalMode);
  
  // Login State (Empty inputs, no auto-filled demo data)
  const [loginIdentifier, setLoginIdentifier] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  // Sign Up State
  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('');
  const [email, setEmail] = useState('');
  const [city, setCity] = useState('');
  const [address, setAddress] = useState('');
  const [signupPassword, setSignupPassword] = useState('');

  const [error, setError] = useState('');

  if (!isAuthModalOpen) return null;

  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!loginIdentifier.trim()) {
      setError('Please enter your mobile number or email.');
      return;
    }

    const success = customerLogin(loginIdentifier.trim(), loginPassword);
    if (!success) {
      setError('Invalid credentials. Password must be at least 4 characters.');
    }
  };

  const handleSignUpSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!fullName.trim()) {
      setError('Full name is required.');
      return;
    }

    if (!phone.trim() || phone.length < 10) {
      setError('Valid Pakistani phone number is required (03xx-xxxxxxx).');
      return;
    }

    if (!address.trim()) {
      setError('Delivery address is required for 1-click Cash on Delivery.');
      return;
    }

    const success = customerSignUp(
      {
        fullName: fullName.trim(),
        phone: phone.trim(),
        email: email.trim() || `${phone.replace(/[^0-9]/g, '')}@pakstore.pk`,
        city,
        address: address.trim(),
      },
      signupPassword || 'customer123'
    );

    if (!success) {
      setError('Could not create account. Please try again.');
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in fade-in duration-200">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div>
            <div className="flex items-center gap-1.5 mb-0.5">
              <span className="bg-emerald-600 text-white text-[10px] font-black px-1.5 py-0.2 rounded">PAK</span>
              <span className="text-xs font-bold text-slate-300">Customer Account</span>
            </div>
            <h2 className="text-lg font-bold tracking-tight">
              {mode === 'login' ? 'Customer Sign In' : 'Create Customer Account'}
            </h2>
          </div>
          <button
            onClick={() => setAuthModal(false)}
            className="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Mode Tabs */}
        <div className="grid grid-cols-2 border-b border-slate-200 bg-slate-50 text-xs font-bold">
          <button
            type="button"
            onClick={() => {
              setMode('login');
              setError('');
            }}
            className={`py-3 text-center border-b-2 transition-colors cursor-pointer ${
              mode === 'login'
                ? 'border-emerald-600 text-emerald-700 bg-white'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            Sign In
          </button>
          <button
            type="button"
            onClick={() => {
              setMode('signup');
              setError('');
            }}
            className={`py-3 text-center border-b-2 transition-colors cursor-pointer ${
              mode === 'signup'
                ? 'border-emerald-600 text-emerald-700 bg-white'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            }`}
          >
            Create Account
          </button>
        </div>

        {/* Content Body */}
        <div className="p-6">
          {error && (
            <div className="p-3 text-xs bg-rose-50 text-rose-700 border border-rose-200 rounded-lg mb-4">
              {error}
            </div>
          )}

          {mode === 'login' ? (
            /* Login Form */
            <form onSubmit={handleLoginSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Mobile Number or Email
                </label>
                <div className="relative">
                  <User className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    required
                    placeholder="0300-1234567 or email@domain.com"
                    value={loginIdentifier}
                    onChange={(e) => setLoginIdentifier(e.target.value)}
                    className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 font-medium"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Password
                </label>
                <div className="relative">
                  <Lock className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="password"
                    required
                    placeholder="••••••••"
                    value={loginPassword}
                    onChange={(e) => setLoginPassword(e.target.value)}
                    className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600"
                  />
                </div>
              </div>

              <div className="pt-1 flex items-center justify-between text-[11px] text-slate-500">
                <span>100% Cash on Delivery Ready</span>
                <span>Secure Customer Session</span>
              </div>

              <button
                type="submit"
                className="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-xs flex items-center justify-center gap-1.5 cursor-pointer mt-2"
              >
                <span>Sign In to Pakstore</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </form>
          ) : (
            /* Sign Up Form */
            <form onSubmit={handleSignUpSubmit} className="space-y-3.5">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Full Name *
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Usman Tariq"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600"
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    Mobile / WhatsApp *
                  </label>
                  <input
                    type="tel"
                    required
                    placeholder="0300-1234567"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 font-mono"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    City (Your City) *
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Lahore, Karachi..."
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    className="w-full px-2.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Delivery Address (For 1-Click COD Orders) *
                </label>
                <textarea
                  rows={2}
                  required
                  placeholder="House #, Street #, Sector / Area, Landmark..."
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                  className="w-full px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600"
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Password *
                </label>
                <input
                  type="password"
                  required
                  placeholder="Choose a password"
                  value={signupPassword}
                  onChange={(e) => setSignupPassword(e.target.value)}
                  className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600"
                />
              </div>

              <button
                type="submit"
                className="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition-colors shadow-xs flex items-center justify-center gap-1.5 cursor-pointer mt-2"
              >
                <span>Create Account & Save Address</span>
                <CheckCircle className="w-3.5 h-3.5" />
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
};
