import React from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR, formatDate } from '../../utils/format';
import { CheckCircle, ShieldCheck, Printer, ArrowRight, Shield } from 'lucide-react';

interface OrderConfirmationModalProps {
  onClose: () => void;
}

export const OrderConfirmationModal: React.FC<OrderConfirmationModalProps> = ({ onClose }) => {
  const { lastPlacedOrder, setIsAdminView } = useStore();

  if (!lastPlacedOrder) return null;

  const order = lastPlacedOrder;

  const handlePrint = () => {
    window.print();
  };

  const handleOpenAdmin = () => {
    onClose();
    setIsAdminView(true);
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in zoom-in-95 duration-200 print:shadow-none print:border-none">
        {/* Success Banner */}
        <div className="bg-emerald-600 text-white p-6 text-center">
          <div className="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <CheckCircle className="w-8 h-8 text-white" />
          </div>
          <span className="text-xs uppercase font-bold tracking-widest text-emerald-200 block">
            Order Successfully Placed
          </span>
          <h2 className="text-2xl font-bold tracking-tight mt-1">Shukriya, {order.customerName}!</h2>
          <p className="text-xs text-emerald-100 mt-1 max-w-md mx-auto">
            Aapka Cash on Delivery order receive ho gaya hai. Humara delivery agent parcel dispatch se pehle confirm karega.
          </p>
        </div>

        {/* Order Details Body */}
        <div className="p-6 space-y-5">
          {/* Tracking ID & Status Box */}
          <div className="flex flex-wrap items-center justify-between gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div>
              <span className="text-xs text-slate-500 font-medium block">Order Tracking ID:</span>
              <span className="font-mono text-base font-black text-slate-900 tracking-wider">
                #{order.id}
              </span>
            </div>
            <div className="text-right">
              <span className="text-xs text-slate-500 font-medium block">Payment Method:</span>
              <span className="text-xs font-bold text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-md inline-block">
                Cash on Delivery (COD)
              </span>
            </div>
          </div>

          {/* User Address Highlight - "User Jo address likhy wo admin penal mein order k sath show" */}
          <div className="p-4 bg-amber-50/70 border border-amber-200 rounded-xl text-xs space-y-1.5">
            <div className="flex items-center justify-between font-bold text-slate-900">
              <span className="text-amber-900 uppercase tracking-wider text-[11px]">
                Shipping & Delivery Address
              </span>
              <span className="text-emerald-700 font-mono">{order.phone}</span>
            </div>
            <p className="font-medium text-slate-800">
              {order.customerName} · {order.city}
            </p>
            <p className="text-slate-700 font-normal bg-white p-2.5 rounded border border-amber-200 leading-relaxed">
              {order.address}
            </p>
            {order.orderNotes && (
              <p className="text-slate-500 text-[11px] pt-1">
                <strong>Delivery Note:</strong> {order.orderNotes}
              </p>
            )}
          </div>

          {/* Items Summary */}
          <div>
            <span className="text-xs font-bold text-slate-700 block mb-2">Ordered Items:</span>
            <div className="space-y-2 border border-slate-200 rounded-xl p-3 bg-white">
              {order.items.map((item, idx) => (
                <div key={idx} className="flex items-center justify-between text-xs py-1 border-b border-slate-100 last:border-b-0">
                  <div className="flex items-center gap-2">
                    <img
                      src={item.image}
                      alt={item.title}
                      className="w-9 h-9 rounded object-cover border border-slate-100"
                    />
                    <span className="text-slate-800 font-medium truncate max-w-xs">{item.title} × {item.quantity}</span>
                  </div>
                  <span className="font-mono font-bold text-slate-900">
                    {formatPKR(item.price * item.quantity)}
                  </span>
                </div>
              ))}

              <div className="pt-2 border-t border-slate-200 flex justify-between text-xs font-semibold text-slate-600">
                <span>Shipping Charges</span>
                <span>{order.shippingFee === 0 ? 'FREE' : formatPKR(order.shippingFee)}</span>
              </div>
              <div className="flex justify-between text-sm font-bold text-slate-900 pt-1">
                <span>Total Cash to Pay on Delivery</span>
                <span className="font-mono text-emerald-700 text-base">{formatPKR(order.totalAmount)}</span>
              </div>
            </div>
          </div>

          {/* Verification in Admin Panel Note */}
          <div className="p-3 bg-slate-100 rounded-xl text-xs text-slate-600 flex items-center justify-between print:hidden">
            <span className="flex items-center gap-2">
              <Shield className="w-4 h-4 text-emerald-600" />
              <span>Yeh order Admin Panel me customer address k sath save ho gaya hai.</span>
            </span>
            <button
              onClick={handleOpenAdmin}
              className="text-emerald-700 font-bold hover:underline cursor-pointer whitespace-nowrap"
            >
              View in Admin →
            </button>
          </div>

          {/* Action Buttons */}
          <div className="grid grid-cols-2 gap-3 pt-2 print:hidden">
            <button
              onClick={handlePrint}
              className="py-2.5 px-4 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors flex items-center justify-center gap-2 cursor-pointer"
            >
              <Printer className="w-4 h-4" /> Print Receipt
            </button>
            <button
              onClick={onClose}
              className="py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center justify-center gap-2 cursor-pointer"
            >
              <span>Back to Store</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
