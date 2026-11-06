import React from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { Product } from '../../types';
import { X, Heart, ShoppingBag, Zap, Trash2 } from 'lucide-react';

interface WishlistDrawerProps {
  onQuickOrder: (product: Product) => void;
}

export const WishlistDrawer: React.FC<WishlistDrawerProps> = ({ onQuickOrder }) => {
  const {
    wishlist,
    toggleWishlist,
    products,
    isWishlistOpen,
    setIsWishlistOpen,
    addToCart,
    setSelectedProductPage,
  } = useStore();

  if (!isWishlistOpen) return null;

  const wishlistedProducts = products.filter((p) => wishlist.includes(p.id));

  return (
    <div className="fixed inset-0 z-50 overflow-hidden bg-slate-950/60 backdrop-blur-xs flex justify-end animate-in fade-in duration-200">
      <div className="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between border-l border-slate-200">
        {/* Drawer Header */}
        <div className="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <div className="flex items-center gap-2">
            <Heart className="w-5 h-5 text-rose-500 fill-rose-500" />
            <h3 className="font-bold text-base text-slate-900">
              My Wishlist ({wishlistedProducts.length})
            </h3>
          </div>
          <button
            onClick={() => setIsWishlistOpen(false)}
            className="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer"
            aria-label="Close Wishlist"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Drawer Body */}
        <div className="flex-1 overflow-y-auto p-4 space-y-3">
          {wishlistedProducts.length > 0 ? (
            wishlistedProducts.map((product) => (
              <div
                key={product.id}
                className="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-200 hover:border-slate-300 transition-all shadow-xs"
              >
                <img
                  src={product.image}
                  alt={product.title}
                  onClick={() => {
                    setSelectedProductPage(product);
                    setIsWishlistOpen(false);
                  }}
                  className="w-16 h-16 rounded-lg object-cover cursor-pointer shrink-0 border border-slate-100"
                />

                <div className="flex-1 min-w-0">
                  <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">
                    {product.category}
                  </span>
                  <h4
                    onClick={() => {
                      setSelectedProductPage(product);
                      setIsWishlistOpen(false);
                    }}
                    className="text-xs font-bold text-slate-900 truncate hover:text-[#00A651] cursor-pointer"
                  >
                    {product.title}
                  </h4>
                  <div className="flex items-center gap-2 mt-1">
                    <span className="text-xs font-mono font-bold text-slate-900">
                      {formatPKR(product.price)}
                    </span>
                    {product.originalPrice && (
                      <span className="text-[10px] text-slate-400 line-through font-mono">
                        {formatPKR(product.originalPrice)}
                      </span>
                    )}
                  </div>
                </div>

                {/* Actions */}
                <div className="flex flex-col gap-1.5 shrink-0">
                  <button
                    onClick={() => {
                      setIsWishlistOpen(false);
                      onQuickOrder(product);
                    }}
                    className="p-2 rounded-lg bg-[#00A651] hover:bg-[#008f45] text-white cursor-pointer shadow-xs"
                    title="Order with Cash on Delivery"
                  >
                    <Zap className="w-3.5 h-3.5 fill-current" />
                  </button>

                  <button
                    onClick={() => {
                      addToCart(product, 1);
                    }}
                    className="p-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 cursor-pointer"
                    title="Add to Shopping Cart"
                  >
                    <ShoppingBag className="w-3.5 h-3.5 text-[#00A651]" />
                  </button>

                  <button
                    onClick={() => toggleWishlist(product.id)}
                    className="p-2 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 cursor-pointer"
                    title="Remove from Wishlist"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="py-16 text-center text-slate-400 space-y-3">
              <Heart className="w-12 h-12 mx-auto text-slate-300 stroke-1" />
              <p className="text-sm font-semibold text-slate-600">Your wishlist is empty</p>
              <p className="text-xs text-slate-400 max-w-xs mx-auto">
                Explore our catalog and tap the heart icon on any product to save it here.
              </p>
            </div>
          )}
        </div>

        {/* Drawer Footer */}
        <div className="p-4 border-t border-slate-200 bg-slate-50">
          <button
            onClick={() => setIsWishlistOpen(false)}
            className="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer"
          >
            Continue Shopping
          </button>
        </div>
      </div>
    </div>
  );
};
