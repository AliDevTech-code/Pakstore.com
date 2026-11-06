import React, { useState, useEffect } from 'react';
import { useStore } from '../../context/StoreContext';
import { Truck, ShieldCheck, RefreshCw, Headphones, Zap, ArrowDown, ChevronLeft, ChevronRight, LayoutGrid } from 'lucide-react';
import { Product } from '../../types';

interface HeroProps {
  onQuickOrder: (product: Product) => void;
}

export const Hero: React.FC<HeroProps> = ({ onQuickOrder }) => {
  const { products, banners, categories, setActiveCategory } = useStore();
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isCategoryDrawerOpen, setIsCategoryDrawerOpen] = useState(false);

  const heroBanners = banners.length > 0 ? banners : [
    {
      id: 'ban-1',
      title: 'Premium Lifestyle & Audio',
      subtitle: 'Pay Cash at Doorstep across Pakistan.',
      image: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1600&auto=format&fit=crop&q=80',
      category: 'Electronics',
      badge: '100% Cash on Delivery',
    },
    {
      id: 'ban-2',
      title: 'Smart Tech & Titanium Wearables',
      subtitle: 'AMOLED Always-On Displays with 7-Day Battery Life.',
      image: 'https://images.unsplash.com/photo-1508615039623-a25605d2b022?w=1600&auto=format&fit=crop&q=80',
      category: 'Watches',
      badge: 'Up to 35% Off',
    },
    {
      id: 'ban-3',
      title: 'Latest Fashion & Arabian Oud',
      subtitle: 'Hand-crafted Pakistani cowhide leather goods and concentrated attar.',
      image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1600&auto=format&fit=crop&q=80',
      category: "Men's Fashion",
      badge: 'New Season 2026',
    },
  ];

  // Auto-slide background images every 4 seconds behind the header text
  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % heroBanners.length);
    }, 4000);
    return () => clearInterval(timer);
  }, [heroBanners.length]);

  const scrollToCatalog = () => {
    const el = document.getElementById('catalog-section');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleCategorySelect = (categoryName: string) => {
    setActiveCategory(categoryName);
    setIsCategoryDrawerOpen(false);
    scrollToCatalog();
  };

  return (
    <section className="relative overflow-hidden bg-slate-950 text-white border-b border-slate-800 min-h-[460px] sm:min-h-[520px] flex flex-col justify-between">
      {/* Background Auto-Sliding Images behind the header text */}
      <div className="absolute inset-0 z-0 overflow-hidden">
        {heroBanners.map((banner, index) => {
          const isActive = index === currentSlide;
          return (
            <div
              key={banner.id}
              className={`absolute inset-0 transition-opacity duration-1000 ease-in-out ${
                isActive ? 'opacity-100 scale-100' : 'opacity-0 scale-105 pointer-events-none'
              } transition-transform duration-7000 ease-linear`}
            >
              <img
                src={banner.image}
                alt={banner.title}
                className="w-full h-full object-cover object-center"
              />
            </div>
          );
        })}

        {/* High-Contrast Gradient Dark Overlay so text is perfectly readable */}
        <div className="absolute inset-0 bg-gradient-to-r from-slate-950/95 via-slate-950/85 to-slate-950/70" />
        <div className="absolute inset-0 bg-radial from-transparent via-slate-950/60 to-slate-950/90" />
      </div>

      {/* Slide Navigation Controls */}
      <div className="absolute right-4 top-1/2 -translate-y-1/2 z-20 hidden md:flex flex-col gap-2">
        <button
          onClick={() => setCurrentSlide((prev) => (prev - 1 + heroBanners.length) % heroBanners.length)}
          className="p-2.5 rounded-full bg-slate-900/80 hover:bg-[#00A651] text-white border border-slate-700/80 transition-colors backdrop-blur-md cursor-pointer"
          aria-label="Previous slide"
        >
          <ChevronLeft className="w-4 h-4" />
        </button>
        <button
          onClick={() => setCurrentSlide((prev) => (prev + 1) % heroBanners.length)}
          className="p-2.5 rounded-full bg-slate-900/80 hover:bg-[#00A651] text-white border border-slate-700/80 transition-colors backdrop-blur-md cursor-pointer"
          aria-label="Next slide"
        >
          <ChevronRight className="w-4 h-4" />
        </button>
      </div>

      {/* Main Content Area sitting right over the sliding images - Perfectly Centered on Tablet & Desktop */}
      <div className="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 sm:pt-12 sm:pb-16 flex-1 flex flex-col justify-center items-center">
        <div className="max-w-3xl mx-auto text-center flex flex-col items-center space-y-4 sm:space-y-6">
          {/* Tag & Menu Toggle Button */}
          <div className="flex flex-wrap items-center justify-center gap-2">
            <div className="inline-flex items-center gap-2 text-xs font-bold text-emerald-300 bg-emerald-950/90 border border-emerald-500/40 px-3.5 py-1.5 rounded-full backdrop-blur-md shadow-xs">
              <span className="w-2 h-2 rounded-full bg-[#00A651] animate-pulse" />
              <span>🇵🇰 100% Cash on Delivery All Over Pakistan</span>
            </div>

            {/* Menu Toggle Button (Slides category drawer) */}
            <button
              onClick={() => setIsCategoryDrawerOpen(!isCategoryDrawerOpen)}
              className="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-[#FF6B00] hover:bg-[#e05e00] px-3.5 py-1.5 rounded-full transition-all cursor-pointer shadow-md"
              title="Toggle Categories Menu"
            >
              <LayoutGrid className="w-3.5 h-3.5" />
              <span>Categories Menu {isCategoryDrawerOpen ? '✕ Close' : '☰ Explore'}</span>
            </button>
          </div>

          {/* Heading */}
          <h1 className="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-[1.1] text-balance">
            Premium Lifestyle & Audio. <br />
            <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#00A651] via-emerald-300 to-[#FF6B00]">
              Pay Cash at Doorstep.
            </span>
          </h1>

          {/* Description */}
          <p className="text-xs sm:text-sm md:text-base text-slate-200 leading-relaxed max-w-2xl mx-auto">
            Authentic aerospace-titanium smartwatches, active noise-cancelling acoustics, genuine cowhide leather accessories, and royal agarwood perfumes. Check parcel and pay cash to rider upon arrival.
          </p>

          {/* Action Buttons */}
          <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
            <button
              onClick={scrollToCatalog}
              className="px-6 py-3 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs sm:text-sm transition-all shadow-lg flex items-center gap-2 cursor-pointer"
            >
              <span>Explore Catalog</span>
              <ArrowDown className="w-4 h-4" />
            </button>

            <button
              onClick={() => {
                if (products[0]) onQuickOrder(products[0]);
              }}
              className="px-5 py-3 bg-[#FF6B00] hover:bg-[#e05e00] text-white font-bold rounded-xl text-xs sm:text-sm transition-all shadow-md flex items-center gap-2 cursor-pointer"
            >
              <Zap className="w-4 h-4 fill-current" />
              <span>Quick COD Order</span>
            </button>
          </div>

          {/* Slide Indicator Dots */}
          <div className="flex items-center justify-center gap-1.5 pt-2">
            {heroBanners.map((_, idx) => (
              <button
                key={idx}
                onClick={() => setCurrentSlide(idx)}
                className={`transition-all rounded-full h-1.5 cursor-pointer ${
                  currentSlide === idx ? 'w-7 bg-[#FF6B00]' : 'w-2 bg-white/40 hover:bg-white/70'
                }`}
                aria-label={`Go to slide ${idx + 1}`}
              />
            ))}
          </div>
        </div>
      </div>

      {/* Slide-out Categories Drawer (Opens when user clicks Menu toggle) */}
      {isCategoryDrawerOpen && (
        <div className="absolute inset-0 z-30 bg-slate-950/80 backdrop-blur-md flex animate-in slide-in-from-left duration-200">
          <div className="w-full max-w-sm bg-white text-slate-900 h-full p-6 shadow-2xl flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between pb-4 border-b border-slate-200">
                <div className="flex items-center gap-2">
                  <span className="bg-[#00A651] text-white p-1 rounded font-black text-xs">PAK</span>
                  <h3 className="font-bold text-base text-slate-900">Shop By Category</h3>
                </div>
                <button
                  onClick={() => setIsCategoryDrawerOpen(false)}
                  className="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer"
                >
                  ✕
                </button>
              </div>

              <div className="mt-4 space-y-1.5 max-h-[60vh] overflow-y-auto">
                <button
                  onClick={() => handleCategorySelect('All Products')}
                  className="w-full text-left px-3.5 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-800 hover:text-[#00A651] font-semibold text-xs flex items-center justify-between transition-colors cursor-pointer"
                >
                  <span>All Categories</span>
                  <span className="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">
                    {products.length} Items
                  </span>
                </button>

                {categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => handleCategorySelect(cat.name)}
                    className="w-full text-left px-3.5 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-800 hover:text-[#00A651] font-semibold text-xs flex items-center justify-between transition-colors cursor-pointer"
                  >
                    <span>{cat.name}</span>
                    <span className="text-[#FF6B00] text-xs">→</span>
                  </button>
                ))}
              </div>
            </div>

            <div className="pt-4 border-t border-slate-200 bg-slate-50 p-3 rounded-xl text-center text-xs text-slate-500">
              <span className="font-bold text-slate-800 block">100% Cash on Delivery</span>
              <span>All cities across Pakistan with TCS 2-4 days courier.</span>
            </div>
          </div>
          <div
            className="flex-1 cursor-pointer"
            onClick={() => setIsCategoryDrawerOpen(false)}
          />
        </div>
      )}

      {/* 4-Column Full-Width Daraz-Style Trust Guarantee Bar */}
      <div className="relative z-10 bg-slate-900/90 backdrop-blur-md border-t border-slate-800 py-3.5 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto grid grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-lg bg-emerald-500/10 text-[#00A651] shrink-0">
              <Truck className="w-4 h-4" />
            </div>
            <div>
              <h4 className="font-bold text-white text-xs">Nationwide COD</h4>
              <p className="text-[10px] text-slate-400">Pay cash upon delivery</p>
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-lg bg-amber-500/10 text-[#FF6B00] shrink-0">
              <ShieldCheck className="w-4 h-4" />
            </div>
            <div>
              <h4 className="font-bold text-white text-xs">Inspected & Sealed</h4>
              <p className="text-[10px] text-slate-400">100% genuine tested</p>
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-lg bg-emerald-500/10 text-[#00A651] shrink-0">
              <RefreshCw className="w-4 h-4" />
            </div>
            <div>
              <h4 className="font-bold text-white text-xs">7-Day Replacement</h4>
              <p className="text-[10px] text-slate-400">Hassle-free warranty</p>
            </div>
          </div>

          <div className="flex items-center gap-2.5">
            <div className="p-2 rounded-lg bg-amber-500/10 text-[#FF6B00] shrink-0">
              <Headphones className="w-4 h-4" />
            </div>
            <div>
              <h4 className="font-bold text-white text-xs">24/7 Helpline</h4>
              <p className="text-[10px] text-slate-400">Customer order support</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
