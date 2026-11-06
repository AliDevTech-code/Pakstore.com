import React, { useState, useEffect, useRef } from 'react';
import { ChevronLeft, ChevronRight, ShieldCheck, Zap } from 'lucide-react';
import { useStore } from '../../context/StoreContext';
import { HeroBanner } from '../../types';

export const HeroImageSlider: React.FC = () => {
  const { banners, setActiveCategory, setSelectedProductPage, products } = useStore();
  const [currentSlide, setCurrentSlide] = useState(0);
  const [isPaused, setIsPaused] = useState(false);
  const touchStartX = useRef<number | null>(null);

  const activeBanners = banners.length > 0 ? banners : [
    {
      id: 'ban-default',
      title: 'PakStore Mega Online Deals',
      subtitle: 'Pay Cash at Doorstep Across Pakistan. 100% Inspected & Sealed Parcels.',
      image: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1400&auto=format&fit=crop&q=80',
      category: 'Electronics',
      badge: 'Cash on Delivery',
      link: '#catalog-section',
    }
  ];

  // Auto slide every 3.5 seconds
  useEffect(() => {
    if (isPaused || activeBanners.length <= 1) return;

    const timer = setInterval(() => {
      setCurrentSlide((prev) => (prev + 1) % activeBanners.length);
    }, 3800);

    return () => clearInterval(timer);
  }, [isPaused, activeBanners.length]);

  const handlePrev = () => {
    setCurrentSlide((prev) => (prev - 1 + activeBanners.length) % activeBanners.length);
  };

  const handleNext = () => {
    setCurrentSlide((prev) => (prev + 1) % activeBanners.length);
  };

  const handleSlideClick = (slide: HeroBanner) => {
    if (slide.category) {
      setActiveCategory(slide.category);
      const matched = products.find((p) => p.category.toLowerCase() === slide.category.toLowerCase());
      if (matched) {
        setSelectedProductPage(matched);
      } else {
        const el = document.getElementById('catalog-section');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
      }
    } else {
      const el = document.getElementById('catalog-section');
      if (el) el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  // Touch handlers for mobile swipe
  const handleTouchStart = (e: React.TouchEvent) => {
    touchStartX.current = e.touches[0].clientX;
  };

  const handleTouchEnd = (e: React.TouchEvent) => {
    if (touchStartX.current === null) return;
    const diff = touchStartX.current - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 40) {
      if (diff > 0) {
        handleNext();
      } else {
        handlePrev();
      }
    }
    touchStartX.current = null;
  };

  return (
    <div
      className="relative w-full rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-900 group"
      onMouseEnter={() => setIsPaused(true)}
      onMouseLeave={() => setIsPaused(false)}
      onTouchStart={handleTouchStart}
      onTouchEnd={handleTouchEnd}
    >
      {/* Slider Viewport */}
      <div className="relative aspect-4/3 sm:aspect-16/10 md:aspect-16/9 w-full overflow-hidden">
        {activeBanners.map((slide, index) => {
          const isActive = index === (currentSlide % activeBanners.length);
          return (
            <div
              key={slide.id}
              className={`absolute inset-0 transition-opacity duration-700 ease-in-out ${
                isActive ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'
              }`}
            >
              {/* Background Image */}
              <img
                src={slide.image}
                alt={slide.title}
                className={`w-full h-full object-cover transition-transform duration-7000 ease-linear ${
                  isActive ? 'scale-105' : 'scale-100'
                }`}
                referrerPolicy="no-referrer"
              />

              {/* Gradient Overlay for Readability */}
              <div className="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent sm:bg-gradient-to-r sm:from-slate-950/90 sm:via-slate-950/40 sm:to-transparent" />

              {/* Text & Content Overlay */}
              <div className="absolute inset-0 p-5 sm:p-8 flex flex-col justify-end sm:justify-center max-w-lg z-20">
                <div className="inline-flex items-center gap-2 mb-2 w-fit">
                  <span className="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#00A651] bg-emerald-950/80 border border-emerald-500/40 px-2.5 py-0.5 rounded-full backdrop-blur-md">
                    {slide.category || 'Special Offer'}
                  </span>
                  {slide.badge && (
                    <span className="text-[10px] sm:text-xs font-bold text-white bg-[#FF6B00] px-2 py-0.5 rounded-full backdrop-blur-md hidden sm:inline-flex items-center gap-1">
                      <ShieldCheck className="w-3 h-3 text-white" />
                      <span>{slide.badge}</span>
                    </span>
                  )}
                </div>

                <h3 className="text-lg sm:text-2xl md:text-3xl font-black text-white tracking-tight leading-tight line-clamp-2">
                  {slide.title}
                </h3>

                <p className="text-xs sm:text-sm text-slate-300 mt-1 sm:mt-2 line-clamp-2 leading-relaxed hidden xs:block">
                  {slide.subtitle}
                </p>

                <div className="mt-3 sm:mt-4 flex items-center gap-3">
                  <button
                    onClick={() => handleSlideClick(slide)}
                    className="px-4 py-2 bg-[#FF6B00] hover:bg-[#e05e00] text-white font-bold text-xs rounded-xl transition-all shadow-md flex items-center gap-1.5 cursor-pointer"
                  >
                    <Zap className="w-3.5 h-3.5 fill-current" />
                    <span>Shop Now</span>
                  </button>
                  <span className="text-[11px] text-emerald-400 font-semibold hidden sm:inline">
                    Cash on Delivery Available
                  </span>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Prev / Next Arrows */}
      {activeBanners.length > 1 && (
        <>
          <button
            onClick={handlePrev}
            className="absolute left-2.5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-slate-950/60 hover:bg-slate-950/90 text-white flex items-center justify-center backdrop-blur-md border border-slate-700/60 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer shadow-md"
            aria-label="Previous slide"
          >
            <ChevronLeft className="w-4 h-4 sm:w-5 sm:h-5" />
          </button>

          <button
            onClick={handleNext}
            className="absolute right-2.5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-slate-950/60 hover:bg-slate-950/90 text-white flex items-center justify-center backdrop-blur-md border border-slate-700/60 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer shadow-md"
            aria-label="Next slide"
          >
            <ChevronRight className="w-4 h-4 sm:w-5 sm:h-5" />
          </button>

          {/* Dot Indicators */}
          <div className="absolute bottom-3 right-4 z-20 flex items-center gap-1.5">
            {activeBanners.map((_, idx) => (
              <button
                key={idx}
                onClick={() => setCurrentSlide(idx)}
                className={`transition-all cursor-pointer rounded-full ${
                  (currentSlide % activeBanners.length) === idx
                    ? 'w-6 h-1.5 bg-[#00A651]'
                    : 'w-1.5 h-1.5 bg-white/40 hover:bg-white/70'
                }`}
                aria-label={`Go to slide ${idx + 1}`}
              />
            ))}
          </div>
        </>
      )}
    </div>
  );
};
