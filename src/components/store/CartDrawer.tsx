import React from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { X, Trash2, ShoppingBag, ArrowRight, Truck } from 'lucide-react';

interface CartDrawerProps {
  onProceedToCheckout: () => void;
}

export const CartDrawer: React.FC<CartDrawerProps> = ({ onProceedToCheckout }) => {
  const {
    cart,
    isCartOpen,
    setIsCartOpen,
    updateCartQuantity,
    removeFromCart,
    clearCart,
    cartTotal,
    shippingFee,
    finalTotal,
    settings,
  } = useStore();

  if (!isCartOpen) return null;

  return (
    <div className="fixed inset-0 z-50 overflow-hidden bg-slate-950/50 backdrop-blur-xs flex justify-end">
      <div className="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between border-l border-slate-200 animate-in slide-in-from-right duration-200">
        {/* Drawer Header */}
        <div className="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <ShoppingBag className="w-5 h-5 text-[#00A651]" />
            <h2 className="font-bold text-slate-900 text-base">Shopping Bag ({cart.length})</h2>
          </div>
          <button
            onClick={() => setIsCartOpen(false)}
            className="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
            aria-label="Close cart"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Delivery Info Banner */}
        <div className="bg-emerald-50 px-6 py-2.5 border-b border-emerald-100 text-xs flex items-center justify-between text-slate-700 font-medium">
          <div className="flex items-center gap-1.5">
            <Truck className="w-4 h-4 text-[#00A651]" />
            <span>Delivery: <strong className="text-slate-900">{shippingFee === 0 ? 'FREE' : formatPKR(shippingFee)}</strong> nationwide</span>
          </div>
          <span className="text-[10px] font-bold uppercase bg-[#FF6B00] text-white px-2 py-0.5 rounded">
            COD
          </span>
        </div>

        {/* Items List */}
        <div className="flex-1 overflow-y-auto p-6 space-y-4">
          {cart.length > 0 ? (
            cart.map((item) => (
              <div
                key={item.product.id}
                className="flex items-center gap-4 pb-4 border-b border-slate-100 last:border-b-0"
              >
                <img
                  src={item.product.image}
                  alt={item.product.title}
                  className="w-16 h-16 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-200"
                />

                <div className="flex-1 min-w-0">
                  <h4 className="text-xs font-semibold text-slate-900 truncate">
                    {item.product.title}
                  </h4>
                  <p className="text-xs font-mono font-bold text-slate-900 mt-0.5">
                    {formatPKR(item.product.price)}
                  </p>

                  <div className="flex items-center justify-between mt-2">
                    {/* Stepper */}
                    <div className="flex items-center border border-slate-200 rounded-md bg-slate-50 text-xs">
                      <button
                        onClick={() => updateCartQuantity(item.product.id, item.quantity - 1)}
                        className="px-2 py-0.5 text-slate-600 hover:bg-slate-200 cursor-pointer font-bold"
                      >
                        -
                      </button>
                      <span className="px-2.5 py-0.5 font-mono font-bold text-slate-900 bg-white">
                        {item.quantity}
                      </span>
                      <button
                        onClick={() => updateCartQuantity(item.product.id, item.quantity + 1)}
                        className="px-2 py-0.5 text-slate-600 hover:bg-slate-200 cursor-pointer font-bold"
                      >
                        +
                      </button>
                    </div>

                    <button
                      onClick={() => removeFromCart(item.product.id)}
                      className="text-slate-400 hover:text-rose-600 p-1 cursor-pointer transition-colors"
                      title="Remove item"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            ))
          ) : (
            <div className="h-full flex flex-col items-center justify-center text-center py-12">
              <div className="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-4">
                <ShoppingBag className="w-8 h-8" />
              </div>
              <h3 className="text-sm font-bold text-slate-800">Your bag is empty</h3>
              <p className="text-xs text-slate-500 mt-1 max-w-xs">
                Explore our catalog and choose your favorite items with Cash on Delivery.
              </p>
              <button
                onClick={() => setIsCartOpen(false)}
                className="mt-4 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 cursor-pointer"
              >
                Start Shopping
              </button>
            </div>
          )}
        </div>

        {/* Drawer Footer & Checkout Action */}
        {cart.length > 0 && (
          <div className="p-6 bg-slate-50 border-t border-slate-200 space-y-3">
            <div className="space-y-1.5 text-xs">
              <div className="flex justify-between text-slate-600">
                <span>Subtotal</span>
                <span className="font-mono font-bold text-slate-900">{formatPKR(cartTotal)}</span>
              </div>
              <div className="flex justify-between text-slate-600">
                <span>Delivery Charges (Pakistan)</span>
                <span className="font-mono font-bold">
                  {shippingFee === 0 ? (
                    <span className="text-emerald-600 uppercase font-bold text-[11px]">Free</span>
                  ) : (
                    formatPKR(shippingFee)
                  )}
                </span>
              </div>
              <div className="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200">
                <span>Total Amount to Pay</span>
                <span className="font-mono text-emerald-700 text-base">{formatPKR(finalTotal)}</span>
              </div>
              <p className="text-[11px] text-slate-500 text-center pt-1">
                💵 100% Cash on Delivery (Pay only when courier arrives)
              </p>
            </div>

            <button
              onClick={() => {
                setIsCartOpen(false);
                onProceedToCheckout();
              }}
              className="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-sm"
            >
              <span>Proceed to COD Checkout</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </div>
        )}
      </div>
    </div>
  );
};
