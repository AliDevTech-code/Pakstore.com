import React from 'react';
import { useStore } from '../../context/StoreContext';
import { Truck, ShieldCheck, PhoneCall } from 'lucide-react';

export const AnnouncementBar: React.FC = () => {
  const { settings } = useStore();

  return (
    <div className="bg-[#003d1e] text-emerald-100 text-xs py-1.5 px-4 border-b border-emerald-900/40">
      <div className="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
        <div className="flex items-center gap-2 font-semibold">
          <Truck className="w-3.5 h-3.5 text-[#FF6B00] shrink-0" />
          <span className="tracking-wide">
            {settings.topBarText || 'Cash on Delivery All Over Pakistan'}
          </span>
        </div>
        <div className="hidden md:flex items-center gap-4 text-emerald-200 text-[11px]">
          <span className="flex items-center gap-1.5 font-medium">
            <ShieldCheck className="w-3.5 h-3.5 text-[#FF6B00]" /> 100% Cash on Delivery
          </span>
          <span aria-hidden="true" className="text-emerald-700">·</span>
          <a
            href={`tel:${settings.phone}`}
            className="flex items-center gap-1.5 hover:text-white transition-colors"
          >
            <PhoneCall className="w-3 h-3 text-[#FF6B00]" /> Helpline: {settings.phone}
          </a>
        </div>
      </div>
    </div>
  );
};
