import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { Product } from '../../types';
import { X, ShieldCheck, MapPin, Phone, User, FileText, CheckCircle2 } from 'lucide-react';
import confetti from 'canvas-confetti';

interface CheckoutModalProps {
  directProduct?: { product: Product; quantity: number } | null;
  onClose: () => void;
  onSuccess: () => void;
}

export const CheckoutModal: React.FC<CheckoutModalProps> = ({
  directProduct,
  onClose,
  onSuccess,
}) => {
  const { cart, cartTotal, settings, placeOrder, currentUser } = useStore();

  const [customerName, setCustomerName] = useState(currentUser?.fullName || '');
  const [phone, setPhone] = useState(currentUser?.phone || '');
  // User writes their city directly in an input box - NO dropdown or predefined list!
  const [city, setCity] = useState(currentUser?.city || '');
  const [address, setAddress] = useState(currentUser?.address || '');
  const [orderNotes, setOrderNotes] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  // Determine items and totals
  const isDirectOrder = !!directProduct;
  const subtotal = isDirectOrder
    ? directProduct.product.price * directProduct.quantity
    : cartTotal;
  // Strict delivery charge from Admin Panel settings
  const shippingFee = settings.deliveryCharges || 0;
  const grandTotal = subtotal + shippingFee;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMessage('');

    if (!customerName.trim()) {
      setErrorMessage('Please enter your full name (Aapka poora naam).');
      return;
    }

    if (!phone.trim() || phone.trim().length < 10) {
      setErrorMessage('Please enter a valid Pakistani mobile number (e.g. 0300-1234567).');
      return;
    }

    if (!city.trim()) {
      setErrorMessage('Please write your city name (Apna city likhein e.g. Lahore, Karachi, Rawalpindi).');
      return;
    }

    if (!address.trim() || address.trim().length < 8) {
      setErrorMessage('Please enter your complete street address (House #, Street, Area) so the courier can deliver without delay.');
      return;
    }

    setIsSubmitting(true);

    try {
      // Place Order into store state and localStorage
      placeOrder({
        customerName: customerName.trim(),
        phone: phone.trim(),
        city: city.trim(),
        address: address.trim(),
        orderNotes: orderNotes.trim(),
        directItem: isDirectOrder ? directProduct : undefined,
      });

      // Trigger celebratory confetti
      confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.6 },
      });

      onSuccess();
    } catch (err: any) {
      setErrorMessage('Failed to place order. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in fade-in duration-200">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div>
            <span className="text-[#00A651] font-bold text-xs uppercase tracking-wider block">
              100% Cash on Delivery
            </span>
            <h2 className="text-lg font-bold tracking-tight">Complete Your Order</h2>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
            aria-label="Close checkout"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-5">
          {errorMessage && (
            <div className="p-3 text-xs bg-rose-50 text-rose-700 border border-rose-200 rounded-lg">
              {errorMessage}
            </div>
          )}

          {/* Items Summary preview */}
          <div className="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
            <span className="text-xs font-bold text-slate-700 block mb-2">Order Summary:</span>
            {isDirectOrder ? (
              <div className="flex items-center justify-between text-xs">
                <div className="flex items-center gap-2">
                  <img
                    src={directProduct.product.image}
                    alt={directProduct.product.title}
                    className="w-10 h-10 rounded object-cover border border-slate-200"
                  />
                  <div>
                    <span className="font-semibold text-slate-900 block truncate max-w-xs">
                      {directProduct.product.title}
                    </span>
                    <span className="text-slate-500">Qty: {directProduct.quantity}</span>
                  </div>
                </div>
                <span className="font-mono font-bold text-slate-900">
                  {formatPKR(directProduct.product.price * directProduct.quantity)}
                </span>
              </div>
            ) : (
              <div className="space-y-1.5 max-h-28 overflow-y-auto text-xs">
                {cart.map((item) => (
                  <div key={item.product.id} className="flex items-center justify-between">
                    <span className="text-slate-700 truncate max-w-xs">
                      {item.product.title} × {item.quantity}
                    </span>
                    <span className="font-mono font-bold text-slate-900">
                      {formatPKR(item.product.price * item.quantity)}
                    </span>
                  </div>
                ))}
              </div>
            )}

            <div className="mt-3 pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
              <span className="text-slate-600">
                Delivery Charges: <strong className="text-slate-900 font-mono">{formatPKR(shippingFee)}</strong>
              </span>
              <span className="text-sm font-bold text-slate-900">
                Total Payable (COD): <span className="text-[#00A651] font-mono">{formatPKR(grandTotal)}</span>
              </span>
            </div>
          </div>

          {/* Pakistan Delivery Form Fields */}
          <div className="space-y-4">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
              <MapPin className="w-4 h-4 text-[#00A651]" />
              <span>Customer & Delivery Details (Pakistan)</span>
            </h3>

            {/* Name */}
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Full Name *
              </label>
              <div className="relative">
                <User className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                <input
                  type="text"
                  required
                  placeholder="e.g. Muhammad Bilal Khan"
                  value={customerName}
                  onChange={(e) => setCustomerName(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]"
                />
              </div>
            </div>

            {/* Mobile / WhatsApp Number & City (Text input, NO dropdown) */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  Mobile / WhatsApp Number *
                </label>
                <div className="relative">
                  <Phone className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="tel"
                    required
                    placeholder="0300-1234567"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651] font-mono"
                  />
                </div>
                <span className="text-[10px] text-slate-500 mt-0.5 block">
                  Courier rider will call you on this number before delivery.
                </span>
              </div>

              {/* City Input (Free text, NO dropdown per exact requirement) */}
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  City (Write City Name) *
                </label>
                <div className="relative">
                  <MapPin className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                  <input
                    type="text"
                    required
                    placeholder="e.g. Lahore, Karachi, Islamabad, Multan..."
                    value={city}
                    onChange={(e) => setCity(e.target.value)}
                    className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]"
                  />
                </div>
                <span className="text-[10px] text-slate-500 mt-0.5 block">
                  Apna city likhein, TCS rider is city me parcel le kar aayega.
                </span>
              </div>
            </div>

            {/* Full Street Address (Crucial for Admin Panel) */}
            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="text-xs font-semibold text-slate-700">
                  Full Street Address (House / Flat #, Street, Mohalla / Area) *
                </label>
                <span className="text-[10px] text-[#00A651] font-bold bg-emerald-50 px-1.5 py-0.5 rounded">
                  Admin Panel me show hoga
                </span>
              </div>
              <textarea
                required
                rows={3}
                placeholder="e.g. House #42-B, Street #7, Sector C, Near Grand Jamia Mosque, Bahria Town..."
                value={address}
                onChange={(e) => setAddress(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]"
              />
              <span className="text-[10px] text-slate-500 mt-0.5 block">
                Aapka yeh address admin panel me order k sath exact show hoga courier booking k liye.
              </span>
            </div>

            {/* Order Notes / Landmark */}
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Order Notes / Landmark / Instructions (Optional)
              </label>
              <div className="relative">
                <FileText className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                <input
                  type="text"
                  placeholder="e.g. Deliver between 2 PM to 5 PM, or call on alternate number"
                  value={orderNotes}
                  onChange={(e) => setOrderNotes(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"
                />
              </div>
            </div>
          </div>

          {/* Payment Method Section (Cash on Delivery) */}
          <div className="p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-[#00A651] text-white flex items-center justify-center font-bold text-xs">
                Rs.
              </div>
              <div>
                <span className="text-xs font-bold text-slate-900 block">
                  Payment Method: Cash on Delivery (COD)
                </span>
                <span className="text-[11px] text-slate-600">
                  Parcel haath me le kar courier rider ko cash dein. Koi advance online payment nahi.
                </span>
              </div>
            </div>
            <CheckCircle2 className="w-5 h-5 text-[#00A651] shrink-0" />
          </div>

          {/* Action Button */}
          <div className="pt-2">
            <button
              type="submit"
              disabled={isSubmitting}
              className="w-full py-3.5 bg-[#00A651] hover:bg-[#008f45] disabled:opacity-50 text-white font-bold rounded-xl text-sm transition-colors shadow-md flex items-center justify-center gap-2 cursor-pointer"
            >
              <ShieldCheck className="w-4 h-4" />
              <span>
                {isSubmitting ? 'Processing Order...' : `Confirm Order (${formatPKR(grandTotal)} - Cash on Delivery)`}
              </span>
            </button>
            <p className="text-[11px] text-slate-400 text-center mt-2">
              Order confirm hone par Order ID PK-XXXX generate hogi.
            </p>
          </div>
        </form>
      </div>
    </div>
  );
};
