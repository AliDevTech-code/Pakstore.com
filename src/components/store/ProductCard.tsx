import React from 'react';
import { Product } from '../../types';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { ShoppingBag, Eye, Zap, Heart, Star } from 'lucide-react';

interface ProductCardProps {
  product: Product;
  onQuickOrder: (product: Product) => void;
}

export const ProductCard: React.FC<ProductCardProps> = ({ product, onQuickOrder }) => {
  const { addToCart, setSelectedProductPage, wishlist, toggleWishlist, settings } = useStore();

  const isFavorited = wishlist.includes(product.id);

  const discountPercent = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  return (
    <div className="group bg-white rounded-xl sm:rounded-2xl border border-slate-200/90 overflow-hidden flex flex-col justify-between hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 shadow-xs relative">
      {/* Product Image */}
      <div
        onClick={() => {
          setSelectedProductPage(product);
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }}
        className="relative aspect-square bg-slate-50 overflow-hidden cursor-pointer"
      >
        <img
          src={product.image}
          alt={product.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          referrerPolicy="no-referrer"
          loading="lazy"
        />

        {/* Badge */}
        {product.badge && (
          <span className="absolute top-2 left-2 bg-slate-900 text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded shadow-xs">
            {product.badge}
          </span>
        )}

        {discountPercent > 0 && (
          <span className="absolute top-2 right-10 sm:right-11 bg-[#FF6B00] text-white text-[9px] sm:text-[10px] font-black px-1.5 py-0.5 rounded shadow-xs">
            -{discountPercent}%
          </span>
        )}

        {/* Wishlist Heart Button */}
        <button
          onClick={(e) => {
            e.stopPropagation();
            toggleWishlist(product.id);
          }}
          className={`absolute top-2 right-2 p-1.5 rounded-full backdrop-blur-md transition-all cursor-pointer shadow-xs ${
            isFavorited
              ? 'bg-rose-500 text-white'
              : 'bg-white/90 text-slate-500 hover:text-rose-500 hover:bg-white'
          }`}
          title={isFavorited ? 'Remove from Wishlist' : 'Add to Wishlist'}
        >
          <Heart className={`w-3.5 h-3.5 ${isFavorited ? 'fill-white' : ''}`} />
        </button>

        {/* Hover Quick View overlay */}
        <div className="absolute inset-0 bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:flex items-center justify-center pointer-events-none">
          <span className="bg-white/95 text-slate-900 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm flex items-center gap-1.5">
            <Eye className="w-3.5 h-3.5 text-[#00A651]" /> View Details & Reviews
          </span>
        </div>
      </div>

      {/* Details Container */}
      <div className="p-2.5 sm:p-3.5 flex-1 flex flex-col justify-between">
        <div>
          <div className="flex items-center justify-between text-[10px] sm:text-xs text-slate-500 mb-1">
            <span className="font-bold uppercase tracking-wider text-slate-400 truncate max-w-[90px]">
              {product.category}
            </span>
            <span className={product.inStock ? 'text-[#00A651] font-semibold text-[10px]' : 'text-rose-500 font-semibold'}>
              {product.inStock ? 'In Stock' : 'Out'}
            </span>
          </div>

          <h3
            onClick={() => {
              setSelectedProductPage(product);
              window.scrollTo({ top: 0, behavior: 'smooth' });
            }}
            className="text-xs sm:text-sm font-bold text-slate-900 line-clamp-2 min-h-[32px] sm:min-h-[38px] hover:text-[#00A651] cursor-pointer transition-colors leading-snug"
          >
            {product.title}
          </h3>

          {/* Rating ⭐ */}
          <div className="mt-1 flex items-center gap-1">
            <div className="flex items-center text-amber-400">
              <Star className="w-3 h-3 fill-amber-400" />
            </div>
            <span className="text-[10px] font-bold text-slate-700 font-mono">
              {product.rating || 4.9}
            </span>
            <span className="text-[10px] text-slate-400">
              ({product.reviewsCount || 42})
            </span>
          </div>
        </div>

        {/* Price & Action Buttons */}
        <div className="mt-2.5 pt-2 border-t border-slate-100">
          <div className="flex flex-wrap items-baseline gap-1.5 mb-2">
            <span className="text-xs sm:text-sm font-black text-slate-900 font-mono tabular-nums">
              {formatPKR(product.price)}
            </span>
            {product.originalPrice && (
              <span className="text-[10px] text-slate-400 line-through font-mono tabular-nums">
                {formatPKR(product.originalPrice)}
              </span>
            )}
          </div>

          {/* Action Row */}
          <div className="flex items-center gap-1.5">
            {/* Quick Order COD */}
            <button
              onClick={() => onQuickOrder(product)}
              disabled={!product.inStock}
              className="flex-1 py-1.5 sm:py-2 px-1.5 text-[11px] font-bold rounded-lg bg-[#00A651] hover:bg-[#008f45] text-white disabled:opacity-50 transition-colors flex items-center justify-center gap-1 cursor-pointer whitespace-nowrap shadow-xs"
              title="Instant Cash on Delivery Checkout"
            >
              <Zap className="w-3 h-3 fill-current shrink-0" />
              <span>Order COD</span>
            </button>

            {/* Add to Cart */}
            <button
              onClick={() => addToCart(product, 1)}
              disabled={!product.inStock}
              className="p-1.5 sm:p-2 rounded-lg border border-slate-200 text-slate-700 hover:text-slate-900 hover:bg-slate-50 disabled:opacity-50 flex items-center justify-center cursor-pointer shrink-0 transition-colors"
              title="Add to shopping cart"
            >
              <ShoppingBag className="w-3.5 h-3.5 text-slate-700" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
