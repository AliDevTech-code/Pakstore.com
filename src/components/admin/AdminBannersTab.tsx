import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { Plus, Trash2, Image as ImageIcon, Sparkles, Upload } from 'lucide-react';
import { HeroBanner } from '../../types';

export const AdminBannersTab: React.FC = () => {
  const { banners, addBanner, deleteBanner, categories } = useStore();

  const [title, setTitle] = useState('');
  const [subtitle, setSubtitle] = useState('');
  const [image, setImage] = useState('');
  const [category, setCategory] = useState(categories[0]?.name || 'Electronics');
  const [badge, setBadge] = useState('Cash on Delivery');

  // File upload for banner image
  const handleBannerUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onloadend = () => {
        if (typeof reader.result === 'string') {
          setImage(reader.result);
        }
      };
      reader.readAsDataURL(file);
    }
  };

  const handleAdd = (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !image.trim()) return;

    addBanner({
      title: title.trim(),
      subtitle: subtitle.trim(),
      image: image.trim(),
      category: category.trim(),
      badge: badge.trim(),
      link: '#catalog-section',
    });

    setTitle('');
    setSubtitle('');
    setImage('');
    setBadge('Cash on Delivery');
  };

  return (
    <div className="space-y-6 max-w-5xl">
      <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-[#00A651] flex items-center gap-1.5">
              <Sparkles className="w-4 h-4" /> Hero Slider Banners
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-1">Manage Homepage Slider Banners</h2>
            <p className="text-xs text-slate-500 mt-0.5">
              Hero slider k images aur deals add ya delete karein. Homepage par auto-slide hota hai.
            </p>
          </div>
          <span className="text-xs font-mono font-bold bg-emerald-50 text-[#00A651] px-2.5 py-1 rounded-lg border border-emerald-200 self-start sm:self-auto">
            {banners.length} Active Banners
          </span>
        </div>

        {/* Add Banner Form */}
        <form onSubmit={handleAdd} className="mt-5 p-5 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
          <span className="text-xs font-bold text-slate-900 block">Add New Slider Banner</span>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Banner Headline Title *
              </label>
              <input
                type="text"
                required
                placeholder="e.g. Summer Mega Deals 2026"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white"
              />
            </div>

            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Subtitle / Description
              </label>
              <input
                type="text"
                placeholder="e.g. Pay Cash at Doorstep Across Pakistan."
                value={subtitle}
                onChange={(e) => setSubtitle(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Target Category
              </label>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white"
              >
                {categories.map((c) => (
                  <option key={c.id} value={c.name}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Badge Tag
              </label>
              <input
                type="text"
                placeholder="e.g. Up to 40% Off / COD"
                value={badge}
                onChange={(e) => setBadge(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white"
              />
            </div>

            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Upload Banner Image
              </label>
              <label className="flex items-center justify-center gap-1.5 px-3 py-2 border border-slate-300 hover:border-[#00A651] rounded-lg bg-white text-xs font-semibold text-slate-700 cursor-pointer">
                <Upload className="w-3.5 h-3.5 text-[#00A651]" />
                <span className="truncate">Upload Image File</span>
                <input
                  type="file"
                  accept="image/*"
                  onChange={handleBannerUpload}
                  className="hidden"
                />
              </label>
            </div>
          </div>

          <div>
            <label className="block text-[11px] font-semibold text-slate-700 mb-1">
              Banner Image URL (or upload above) *
            </label>
            <input
              type="text"
              required
              placeholder="https://images.unsplash.com/photo-..."
              value={image}
              onChange={(e) => setImage(e.target.value)}
              className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white font-mono"
            />
          </div>

          <button
            type="submit"
            className="px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold rounded-xl flex items-center gap-2 cursor-pointer shadow-xs transition-colors"
          >
            <Plus className="w-4 h-4" />
            <span>Add Banner to Slider</span>
          </button>
        </form>

        {/* Current Active Banners Grid */}
        <div className="mt-8 space-y-3">
          <span className="text-xs font-bold text-slate-700 block">Active Slider Banners ({banners.length})</span>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {banners.map((ban) => (
              <div
                key={ban.id}
                className="relative rounded-xl overflow-hidden border border-slate-200 bg-slate-900 group shadow-xs"
              >
                <div className="relative aspect-16/9 overflow-hidden">
                  <img
                    src={ban.image}
                    alt={ban.title}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/40 to-transparent p-4 flex flex-col justify-end">
                    <span className="text-[10px] uppercase font-bold text-[#00A651] bg-emerald-950/80 border border-emerald-500/40 px-2 py-0.5 rounded w-fit mb-1">
                      {ban.category} · {ban.badge}
                    </span>
                    <h4 className="text-sm font-bold text-white leading-snug line-clamp-1">
                      {ban.title}
                    </h4>
                    <p className="text-[11px] text-slate-300 line-clamp-1 mt-0.5">
                      {ban.subtitle}
                    </p>
                  </div>

                  <button
                    onClick={() => {
                      if (confirm(`Delete banner "${ban.title}"?`)) {
                        deleteBanner(ban.id);
                      }
                    }}
                    className="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600/90 hover:bg-rose-600 text-white cursor-pointer shadow-sm transition-opacity"
                    title="Delete Banner"
                  >
                    <Trash2 className="w-4 h-4" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
