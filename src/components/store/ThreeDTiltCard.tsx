import React, { useState, useRef } from 'react';
import { motion, useMotionValue, useSpring, useTransform } from 'motion/react';
import { Product } from '../../types';
import { formatPKR } from '../../utils/format';
import { ShieldCheck, Zap, Sparkles } from 'lucide-react';

interface ThreeDTiltCardProps {
  products: Product[];
  onOrder: (product: Product) => void;
  onViewDetail: (product: Product) => void;
}

export const ThreeDTiltCard: React.FC<ThreeDTiltCardProps> = ({
  products,
  onOrder,
  onViewDetail,
}) => {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const cardRef = useRef<HTMLDivElement>(null);

  const activeProduct = products[selectedIndex] || products[0];

  // Motion Values for 3D Perspective Tilt
  const x = useMotionValue(0);
  const y = useMotionValue(0);

  const mouseXSpring = useSpring(x, { stiffness: 250, damping: 25 });
  const mouseYSpring = useSpring(y, { stiffness: 250, damping: 25 });

  const rotateX = useTransform(mouseYSpring, [-0.5, 0.5], ['14deg', '-14deg']);
  const rotateY = useTransform(mouseXSpring, [-0.5, 0.5], ['-14deg', '14deg']);
  const glareX = useTransform(mouseXSpring, [-0.5, 0.5], ['0%', '100%']);
  const glareY = useTransform(mouseYSpring, [-0.5, 0.5], ['0%', '100%']);

  const handleMouseMove = (e: React.MouseEvent<HTMLDivElement>) => {
    if (!cardRef.current) return;
    const rect = cardRef.current.getBoundingClientRect();
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;

    const xPct = mouseX / rect.width - 0.5;
    const yPct = mouseY / rect.height - 0.5;

    x.set(xPct);
    y.set(yPct);
  };

  const handleMouseLeave = () => {
    x.set(0);
    y.set(0);
  };

  return (
    <div className="relative w-full max-w-lg mx-auto flex flex-col items-center">
      {/* 3D Container with perspective */}
      <div
        className="w-full relative [perspective:1200px]"
        onMouseMove={handleMouseMove}
        onMouseLeave={handleMouseLeave}
      >
        <motion.div
          ref={cardRef}
          style={{
            rotateX,
            rotateY,
            transformStyle: 'preserve-3d',
          }}
          className="relative w-full rounded-3xl bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 p-6 sm:p-7 border border-slate-700/60 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] text-white overflow-hidden transition-shadow duration-300"
        >
          {/* Dynamic Glare Reflection */}
          <motion.div
            style={{
              background: `radial-gradient(circle at ${glareX} ${glareY}, rgba(255,255,255,0.15) 0%, transparent 60%)`,
            }}
            className="absolute inset-0 pointer-events-none z-30"
          />

          {/* Floating Top Elements [translateZ: 50px] */}
          <div
            style={{ transform: 'translateZ(50px)' }}
            className="flex items-center justify-between gap-2 mb-4"
          >
            <span className="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-950/80 border border-emerald-500/40 px-3 py-1 rounded-full shadow-xs">
              <Sparkles className="w-3.5 h-3.5 text-emerald-400" />
              <span>Flagship Selection</span>
            </span>

            <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider font-mono">
              {activeProduct.sku}
            </span>
          </div>

          {/* 3D Floating Product Image Layer [translateZ: 70px] */}
          <div
            style={{ transform: 'translateZ(70px)' }}
            className="relative w-full aspect-4/3 rounded-2xl overflow-hidden bg-slate-950/80 border border-slate-800 shadow-2xl group cursor-pointer"
            onClick={() => onViewDetail(activeProduct)}
          >
            <img
              src={activeProduct.image}
              alt={activeProduct.title}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            {/* Glowing Corner Badge */}
            <div className="absolute top-3 left-3 bg-slate-900/90 backdrop-blur-md text-emerald-400 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-500/30 flex items-center gap-1.5 shadow-md">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>100% Cash on Delivery</span>
            </div>

            {/* Floating Price Pill */}
            <div className="absolute bottom-3 right-3 bg-slate-900/95 backdrop-blur-md text-white text-sm font-bold px-3 py-1 rounded-lg border border-slate-700 shadow-md font-mono">
              {formatPKR(activeProduct.price)}
            </div>
          </div>

          {/* 3D Floating Details & CTAs [translateZ: 55px] */}
          <div style={{ transform: 'translateZ(55px)' }} className="mt-5 space-y-4">
            <div>
              <div className="text-xs text-emerald-400 font-semibold uppercase tracking-wider mb-1">
                {activeProduct.category}
              </div>
              <h3 className="text-lg sm:text-xl font-bold tracking-tight text-white line-clamp-1">
                {activeProduct.title}
              </h3>
              <p className="text-xs text-slate-300 line-clamp-2 mt-1 leading-relaxed">
                {activeProduct.description}
              </p>
            </div>

            {/* Action Buttons */}
            <div className="grid grid-cols-2 gap-3 pt-1">
              <button
                onClick={() => onViewDetail(activeProduct)}
                className="py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-200 bg-slate-800/80 hover:bg-slate-750 border border-slate-700 transition-colors cursor-pointer text-center"
              >
                View Details
              </button>

              <button
                onClick={() => onOrder(activeProduct)}
                className="py-2.5 px-4 rounded-xl text-xs font-bold text-slate-950 bg-gradient-to-r from-emerald-400 to-emerald-500 hover:from-emerald-300 hover:to-emerald-400 transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <Zap className="w-3.5 h-3.5 fill-current" />
                <span>Order with COD</span>
              </button>
            </div>
          </div>
        </motion.div>
      </div>

      {/* Flagship Selector Thumbnails below 3D card */}
      <div className="mt-5 flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        {products.slice(0, 4).map((item, idx) => (
          <button
            key={item.id}
            onClick={() => setSelectedIndex(idx)}
            className={`p-1.5 rounded-xl border transition-all flex items-center gap-2 cursor-pointer ${
              selectedIndex === idx
                ? 'bg-slate-900 text-white border-emerald-500 ring-2 ring-emerald-500/30'
                : 'bg-white/80 backdrop-blur-xs text-slate-600 border-slate-200 hover:border-slate-300'
            }`}
          >
            <img
              src={item.image}
              alt={item.title}
              className="w-8 h-8 rounded-lg object-cover"
            />
            <span className="text-[11px] font-semibold pr-2 max-w-[90px] truncate hidden sm:inline">
              {item.title}
            </span>
          </button>
        ))}
      </div>
    </div>
  );
};
