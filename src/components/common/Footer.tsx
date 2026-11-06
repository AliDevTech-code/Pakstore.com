import React from 'react';
import { useStore } from '../../context/StoreContext';
import { ShieldCheck, Truck, PhoneCall, Mail, Server } from 'lucide-react';

export const Footer: React.FC = () => {
  const { settings, setIsAdminLoginOpen, setIsOrderTrackerOpen, setActiveCategory } = useStore();

  const handleCategoryClick = (categoryName: string) => {
    setActiveCategory(categoryName);
    const el = document.getElementById('catalog-section');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <footer className="bg-slate-900 text-slate-300 border-t border-slate-800 pt-12 pb-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-slate-800">
          {/* Brand Info */}
          <div className="space-y-3">
            <div className="flex items-center gap-2">
              <span className="bg-emerald-600 text-white font-black text-xs px-2 py-0.5 rounded">
                PAK
              </span>
              <span className="text-xl font-bold tracking-tight text-white">Pakstore</span>
            </div>
            <p className="text-xs text-slate-400 leading-relaxed">
              Pakistan’s premier multi-category digital store. Offering 100% Cash on Delivery across Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, and all cities nationwide.
            </p>
            <div className="pt-2 text-xs text-emerald-400 font-semibold flex items-center gap-1.5">
              <ShieldCheck className="w-4 h-4" /> 100% Verified Cash on Delivery
            </div>
          </div>

          {/* Quick Categories */}
          <div className="space-y-3 text-xs">
            <h4 className="font-bold text-white uppercase tracking-wider text-[11px]">Categories</h4>
            <ul className="space-y-2 text-slate-400">
              <li>
                <button
                  onClick={() => handleCategoryClick('Electronics')}
                  className="hover:text-white transition-colors cursor-pointer"
                >
                  Electronics & Smart Gadgets
                </button>
              </li>
              <li>
                <button
                  onClick={() => handleCategoryClick('Audio')}
                  className="hover:text-white transition-colors cursor-pointer"
                >
                  Wireless Earbuds & Audio
                </button>
              </li>
              <li>
                <button
                  onClick={() => handleCategoryClick('Leather Goods')}
                  className="hover:text-white transition-colors cursor-pointer"
                >
                  Genuine Leather Wallets & Bags
                </button>
              </li>
              <li>
                <button
                  onClick={() => handleCategoryClick('Fragrances')}
                  className="hover:text-white transition-colors cursor-pointer"
                >
                  Arabian Oud & Attar Perfumes
                </button>
              </li>
            </ul>
          </div>

          {/* Customer Care */}
          <div className="space-y-3 text-xs">
            <h4 className="font-bold text-white uppercase tracking-wider text-[11px]">Customer Support</h4>
            <ul className="space-y-2 text-slate-400">
              <li>
                <button
                  onClick={() => setIsOrderTrackerOpen(true)}
                  className="hover:text-white transition-colors cursor-pointer"
                >
                  Track Existing Order
                </button>
              </li>
              <li>
                <span className="text-slate-400">Delivery via TCS & Leopards (2-4 Days)</span>
              </li>
              <li>
                <span className="text-slate-400">7 Days Return / Exchange Guarantee</span>
              </li>
              <li>
                <span className="text-slate-400">100% Cash on Delivery</span>
              </li>
            </ul>
          </div>

          {/* Contact Details */}
          <div className="space-y-3 text-xs">
            <h4 className="font-bold text-white uppercase tracking-wider text-[11px]">Direct Contact</h4>
            <div className="space-y-2 text-slate-400">
              <a
                href={`tel:${settings.phone}`}
                className="flex items-center gap-2 hover:text-white transition-colors"
              >
                <PhoneCall className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Helpline: {settings.phone}</span>
              </a>
              <div className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Email: {settings.email}</span>
              </div>
              <div className="flex items-center gap-2">
                <Truck className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Shipments: Dispatched Daily</span>
              </div>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
          <p>© {new Date().getFullYear()} Pakstore Pakistan. All rights reserved.</p>
          <div className="flex items-center gap-4 text-[11px]">
            <span>100% Cash on Delivery Nationwide</span>
            <span aria-hidden="true">·</span>
            <span>Karachi · Lahore · Islamabad</span>
            <span aria-hidden="true">·</span>
            {/* Discreet Staff Authorization Button */}
            <button
              onClick={() => setIsAdminLoginOpen(true)}
              id="admin-portal-trigger"
              className="text-slate-600 hover:text-slate-400 transition-colors flex items-center gap-1 cursor-pointer"
              title="Staff Authorization Portal"
            >
              <span>🔒 Staff Portal</span>
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
};
