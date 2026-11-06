import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { CheckCircle, Save, Sliders, Upload, Image as ImageIcon, Trash2, Key, Lock, AlertCircle } from 'lucide-react';

export const AdminSettingsTab: React.FC = () => {
  const { settings, updateSettings, adminCredentials, updateAdminCredentials } = useStore();

  const [storeName, setStoreName] = useState(settings.storeName);
  const [logoUrl, setLogoUrl] = useState(settings.logoUrl || '');
  const [topBarText, setTopBarText] = useState(settings.topBarText || 'Cash on Delivery All Over Pakistan');
  const [phone, setPhone] = useState(settings.phone);
  const [whatsapp, setWhatsapp] = useState(settings.whatsapp);
  const [email, setEmail] = useState(settings.email);
  const [deliveryCharges, setDeliveryCharges] = useState(
    (settings.deliveryCharges !== undefined ? settings.deliveryCharges : 199).toString()
  );

  const [savedSuccess, setSavedSuccess] = useState(false);

  // Admin Login Credentials State (Full control over admin login)
  const [adminEmail, setAdminEmail] = useState(adminCredentials.email);
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [credSuccess, setCredSuccess] = useState(false);
  const [credError, setCredError] = useState('');

  // File upload handler for logo
  const handleLogoFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        if (typeof reader.result === 'string') {
          setLogoUrl(reader.result);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    updateSettings({
      storeName: storeName.trim(),
      logoUrl: logoUrl.trim() || undefined,
      topBarText: topBarText.trim(),
      phone: phone.trim(),
      whatsapp: whatsapp.trim().replace(/[^0-9]/g, ''),
      email: email.trim(),
      deliveryCharges: parseInt(deliveryCharges, 10) || 0,
    });

    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  const handleUpdateAdminCredentials = (e: React.FormEvent) => {
    e.preventDefault();
    setCredError('');
    if (!adminEmail.trim()) {
      setCredError('Admin email cannot be empty');
      return;
    }
    if (newPassword && newPassword !== confirmPassword) {
      setCredError('Password aur Confirm Password match nahi kar rahe!');
      return;
    }
    const finalPassword = newPassword.trim() ? newPassword.trim() : adminCredentials.password;
    updateAdminCredentials(adminEmail.trim(), finalPassword);
    setCredSuccess(true);
    setNewPassword('');
    setConfirmPassword('');
    setTimeout(() => setCredSuccess(false), 4000);
  };

  return (
    <div className="space-y-6 max-w-4xl">
      <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div className="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-[#00A651] flex items-center gap-1.5">
              <Sliders className="w-4 h-4" /> Store Settings & Branding Control
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-1">Website Logo & Store Settings</h2>
            <p className="text-xs text-slate-500 mt-0.5">
              Har cheez admin panel se control hoti hai. Logo upload karein ya text, top bar, whatsapp number aur delivery charges tabdeel karein.
            </p>
          </div>

          {savedSuccess && (
            <div className="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 font-bold animate-in fade-in">
              <CheckCircle className="w-4 h-4 text-[#00A651]" />
              <span>Settings Saved!</span>
            </div>
          )}
        </div>

        <form onSubmit={handleSave} className="space-y-6 pt-6">
          {/* Logo Upload Section */}
          <div className="bg-slate-50 p-5 rounded-xl border border-slate-200 space-y-4">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <label className="text-xs font-bold text-slate-900 block">
                  Website Logo (Upload or URL)
                </label>
                <span className="text-[11px] text-slate-500">
                  Apna logo upload karein ya image URL dein. Agar logo khali ho to default Pak (Green) + Store (Orange) show hoga.
                </span>
              </div>

              {/* Live Preview */}
              <div className="bg-white p-3 rounded-xl border border-slate-200 flex items-center gap-3 shadow-xs">
                <span className="text-[10px] text-slate-400 font-bold uppercase">Header Preview:</span>
                {logoUrl ? (
                  <img
                    src={logoUrl}
                    alt="Logo Preview"
                    className="h-9 max-w-[140px] object-contain rounded"
                  />
                ) : (
                  <div className="flex items-center">
                    <span className="text-xl font-black text-[#00A651]">Pak</span>
                    <span className="text-xl font-black text-[#FF6B00]">Store</span>
                  </div>
                )}
              </div>
            </div>

            {/* Upload File Button or Paste URL */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              <div>
                <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                  Option 1: Upload Image from Device
                </label>
                <label className="flex items-center justify-center gap-2 px-4 py-2 border-2 border-dashed border-slate-300 hover:border-[#00A651] rounded-lg bg-white text-xs font-semibold text-slate-700 cursor-pointer transition-colors">
                  <Upload className="w-4 h-4 text-[#00A651]" />
                  <span>Choose Logo File (PNG / JPG / SVG)</span>
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleLogoFileUpload}
                    className="hidden"
                  />
                </label>
              </div>

              <div>
                <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                  Option 2: Paste Image URL
                </label>
                <div className="flex gap-2">
                  <input
                    type="text"
                    placeholder="https://example.com/logo.png..."
                    value={logoUrl}
                    onChange={(e) => setLogoUrl(e.target.value)}
                    className="flex-1 px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white text-slate-800 font-mono"
                  />
                  {logoUrl && (
                    <button
                      type="button"
                      onClick={() => setLogoUrl('')}
                      className="p-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg cursor-pointer"
                      title="Clear Logo"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Store Name & Top Bar Text */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Website Name *
              </label>
              <input
                type="text"
                required
                value={storeName}
                onChange={(e) => setStoreName(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Top Bar Text *
              </label>
              <input
                type="text"
                required
                value={topBarText}
                onChange={(e) => setTopBarText(e.target.value)}
                placeholder="Cash on Delivery All Over Pakistan"
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"
              />
              <span className="text-[10px] text-slate-400 mt-0.5 block">
                Yeh text website k sabse upar announcement bar mein show hoga.
              </span>
            </div>
          </div>

          {/* Contact Numbers & WhatsApp */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                WhatsApp Number (for Order Button) *
              </label>
              <input
                type="text"
                required
                placeholder="923001234567"
                value={whatsapp}
                onChange={(e) => setWhatsapp(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]"
              />
              <span className="text-[10px] text-slate-400 mt-0.5 block">
                e.g. 923001234567 (Country code 92 k sath, bina + k)
              </span>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Display Phone Number
              </label>
              <input
                type="text"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]"
              />
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Customer Support Email
              </label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"
              />
            </div>
          </div>

          {/* Delivery Charges (Admin set kare, free delivery ka koi fixed rule na ho) */}
          <div className="p-4 bg-emerald-50/60 rounded-xl border border-emerald-200">
            <label className="block text-xs font-bold text-slate-900 mb-1">
              Nationwide Delivery Charges (PKR) *
            </label>
            <div className="relative max-w-xs">
              <span className="absolute left-3 top-2 text-xs text-slate-500 font-mono">Rs.</span>
              <input
                type="number"
                required
                min="0"
                value={deliveryCharges}
                onChange={(e) => setDeliveryCharges(e.target.value)}
                className="w-full pl-10 pr-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651] bg-white font-bold"
              />
            </div>
            <p className="text-[11px] text-slate-600 mt-1.5">
              Admin jo charges rakhe wohi customer k cart aur checkout par lagenge. Free delivery ka koi automatic fixed rule nahi hoga. Agar 0 rakhein to delivery muft hogi.
            </p>
          </div>

          {/* Save Button */}
          <div className="pt-4 border-t border-slate-200 flex justify-end">
            <button
              type="submit"
              className="px-6 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-xs rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
              <Save className="w-4 h-4" />
              <span>Save Website Logo & Settings</span>
            </button>
          </div>
        </form>
      </div>

      {/* Dedicated Admin Portal Credentials & Security Control */}
      <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div className="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-[#FF6B00] flex items-center gap-1.5">
              <Key className="w-4 h-4" /> Admin Login & Access Control
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-1">Admin Email & Password Settings</h2>
            <p className="text-xs text-slate-500 mt-0.5">
              Admin panel ka full control aapke paas hai. Yahan jo email aur password set karenge, sirf usi se admin login hoga. Koi demo ya dummy credential nahi rahega.
            </p>
          </div>

          {credSuccess && (
            <div className="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 font-bold animate-in fade-in">
              <CheckCircle className="w-4 h-4 text-[#00A651]" />
              <span>Admin Login Updated!</span>
            </div>
          )}
        </div>

        {credError && (
          <div className="mt-4 p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0 text-rose-600" />
            <span>{credError}</span>
          </div>
        )}

        <form onSubmit={handleUpdateAdminCredentials} className="mt-5 space-y-4 text-xs">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-slate-800 mb-1">
                Admin Login Email *
              </label>
              <input
                type="email"
                required
                value={adminEmail}
                onChange={(e) => setAdminEmail(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] font-mono"
                placeholder="e.g. admin@pakstore.com ya aapki apni email"
              />
              <span className="text-[10px] text-slate-500 mt-0.5 block">
                Current active admin email: <strong className="text-slate-800">{adminCredentials.email}</strong>
              </span>
            </div>

            <div>
              <label className="block text-xs font-bold text-slate-800 mb-1">
                New Admin Password (Leave blank to keep current)
              </label>
              <input
                type="password"
                value={newPassword}
                onChange={(e) => setNewPassword(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] font-mono"
                placeholder="Naya password likhein"
              />
            </div>
          </div>

          {newPassword && (
            <div className="max-w-sm">
              <label className="block text-xs font-bold text-slate-800 mb-1">
                Confirm New Password *
              </label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] font-mono"
                placeholder="Dobara password likhein"
              />
            </div>
          )}

          <div className="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <span className="text-[11px] text-slate-500">
              InfinityFree MySQL me bhi <code className="bg-slate-100 px-1 py-0.5 rounded font-mono">admin_users</code> table me yehi email aur password update ho jayega.
            </span>
            <button
              type="submit"
              className="px-5 py-2.5 bg-[#FF6B00] hover:bg-[#e05e00] text-white font-bold text-xs rounded-xl transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
            >
              <Lock className="w-3.5 h-3.5" />
              <span>Update Admin Credentials</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
