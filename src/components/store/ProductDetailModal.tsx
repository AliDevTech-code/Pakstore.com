import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { X, ShoppingBag, Zap, ShieldCheck, Truck, RefreshCw, CheckCircle2 } from 'lucide-react';
import { Product } from '../../types';

interface ProductDetailModalProps {
  onQuickOrder: (product: Product, quantity: number) => void;
}

export const ProductDetailModal: React.FC<ProductDetailModalProps> = ({ onQuickOrder }) => {
  const { selectedProductForDetail, setSelectedProductForDetail, addToCart } = useStore();
  const [quantity, setQuantity] = useState(1);

  if (!selectedProductForDetail) return null;

  const product = selectedProductForDetail;
  const discountPercent = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  const handleAddToCart = () => {
    addToCart(product, quantity);
    setSelectedProductForDetail(null);
  };

  const handleBuyNow = () => {
    onQuickOrder(product, quantity);
    setSelectedProductForDetail(null);
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-200 relative animate-in fade-in duration-200">
        {/* Close Button */}
        <button
          onClick={() => setSelectedProductForDetail(null)}
          className="absolute top-4 right-4 z-10 p-2 text-slate-400 hover:text-slate-700 bg-white/80 hover:bg-white rounded-full transition-colors cursor-pointer"
          aria-label="Close modal"
        >
          <X className="w-5 h-5" />
        </button>

        <div className="grid grid-cols-1 md:grid-cols-2">
          {/* Product Media Column */}
          <div className="relative aspect-square md:aspect-auto bg-slate-100 flex items-center justify-center overflow-hidden">
            <img
              src={product.image}
              alt={product.title}
              className="w-full h-full object-cover"
              referrerPolicy="no-referrer"
            />
            {product.badge && (
              <span className="absolute top-4 left-4 bg-slate-900 text-white text-xs font-semibold px-2.5 py-1 rounded shadow-sm">
                {product.badge}
              </span>
            )}
          </div>

          {/* Product Purchasing Module */}
          <div className="p-6 md:p-8 flex flex-col justify-between">
            <div>
              <div className="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span className="font-semibold text-emerald-700 uppercase">{product.category}</span>
                <span aria-hidden="true">·</span>
                <span className="font-mono">{product.sku}</span>
              </div>

              <h2 className="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                {product.title}
              </h2>

              {/* Price & Savings */}
              <div className="mt-3 flex items-baseline gap-3">
                <span className="text-2xl font-bold text-slate-900 font-mono tabular-nums">
                  {formatPKR(product.price)}
                </span>
                {product.originalPrice && (
                  <span className="text-sm text-slate-400 line-through font-mono tabular-nums">
                    {formatPKR(product.originalPrice)}
                  </span>
                )}
                {discountPercent > 0 && (
                  <span className="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    Save {discountPercent}%
                  </span>
                )}
              </div>

              <div className="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1.5">
                <CheckCircle2 className="w-3.5 h-3.5" />
                <span>In Stock ({product.stock} units ready for immediate courier dispatch)</span>
              </div>

              <p className="mt-4 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                {product.description}
              </p>

              {/* Feature Highlights */}
              {product.features && product.features.length > 0 && (
                <div className="mt-4 space-y-1.5">
                  <span className="text-xs font-bold text-slate-900 block mb-1">Key Specifications:</span>
                  {product.features.map((feat, idx) => (
                    <div key={idx} className="flex items-start gap-2 text-xs text-slate-600">
                      <span className="text-emerald-500 font-bold shrink-0">✓</span>
                      <span>{feat}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Quantity and Actions */}
            <div className="mt-6 pt-4 border-t border-slate-100">
              <div className="flex items-center gap-4 mb-4">
                <span className="text-xs font-semibold text-slate-700">Quantity:</span>
                <div className="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-slate-50">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="px-3 py-1 text-slate-600 hover:bg-slate-200 text-sm font-bold"
                  >
                    -
                  </button>
                  <span className="px-4 py-1 text-xs font-mono font-bold text-slate-900 bg-white">
                    {quantity}
                  </span>
                  <button
                    onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                    className="px-3 py-1 text-slate-600 hover:bg-slate-200 text-sm font-bold"
                  >
                    +
                  </button>
                </div>
                <span className="text-xs text-slate-400">
                  Subtotal: <strong className="text-slate-800 font-mono">{formatPKR(product.price * quantity)}</strong>
                </span>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <button
                  onClick={handleAddToCart}
                  className="py-2.5 px-4 text-xs font-bold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 transition-colors flex items-center justify-center gap-2 cursor-pointer"
                >
                  <ShoppingBag className="w-4 h-4" /> Add to Cart
                </button>

                <button
                  onClick={handleBuyNow}
                  className="py-2.5 px-4 text-xs font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-sm"
                >
                  <Zap className="w-4 h-4" /> Buy with COD
                </button>
              </div>

              {/* Trust Callouts */}
              <div className="mt-4 pt-3 border-t border-slate-100 grid grid-cols-3 gap-2 text-[10px] text-slate-500 text-center">
                <div className="flex flex-col items-center">
                  <Truck className="w-3.5 h-3.5 text-emerald-600 mb-1" />
                  <span>2-4 Days TCS Delivery</span>
                </div>
                <div className="flex flex-col items-center">
                  <ShieldCheck className="w-3.5 h-3.5 text-emerald-600 mb-1" />
                  <span>Pay Cash on Delivery</span>
                </div>
                <div className="flex flex-col items-center">
                  <RefreshCw className="w-3.5 h-3.5 text-emerald-600 mb-1" />
                  <span>7 Days Return</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
