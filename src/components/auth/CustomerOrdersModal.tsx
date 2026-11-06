import React from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR, formatDate } from '../../utils/format';
import { X, Package, Truck, Clock, CheckCircle2, MessageCircle } from 'lucide-react';

export const CustomerOrdersModal: React.FC = () => {
  const { isMyOrdersOpen, setIsMyOrdersOpen, orders, currentUser, settings } = useStore();

  if (!isMyOrdersOpen) return null;

  // Filter orders belonging to this user (or if none, show matching phone or all sample orders for demo)
  const userOrders = currentUser
    ? orders.filter(
        (o) =>
          o.phone.replace(/[^0-9]/g, '') === currentUser.phone.replace(/[^0-9]/g, '') ||
          o.customerName.toLowerCase().includes(currentUser.fullName.toLowerCase())
      )
    : orders;

  const displayOrders = userOrders.length > 0 ? userOrders : orders.slice(0, 2);

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in fade-in duration-200">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Package className="w-5 h-5 text-emerald-400" />
            <div>
              <h2 className="text-base font-bold tracking-tight">My Orders & Tracking</h2>
              <span className="text-[11px] text-slate-400">
                {currentUser ? `Welcome back, ${currentUser.fullName}` : 'Order History'}
              </span>
            </div>
          </div>
          <button
            onClick={() => setIsMyOrdersOpen(false)}
            className="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content */}
        <div className="p-6 max-h-[75vh] overflow-y-auto space-y-4">
          {displayOrders.length > 0 ? (
            displayOrders.map((order) => (
              <div
                key={order.id}
                className="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3"
              >
                <div className="flex items-center justify-between pb-2 border-b border-slate-200">
                  <div>
                    <span className="font-mono font-bold text-slate-900 text-sm">#{order.id}</span>
                    <span className="text-xs text-slate-400 block">{formatDate(order.createdAt)}</span>
                  </div>
                  <span
                    className={`text-xs font-bold px-2.5 py-1 rounded-md ${
                      order.status === 'Delivered'
                        ? 'bg-emerald-100 text-emerald-800'
                        : order.status === 'Shipped'
                        ? 'bg-blue-100 text-blue-800'
                        : order.status === 'Processing'
                        ? 'bg-indigo-100 text-indigo-800'
                        : order.status === 'Cancelled'
                        ? 'bg-rose-100 text-rose-800'
                        : 'bg-amber-100 text-amber-800'
                    }`}
                  >
                    {order.status}
                  </span>
                </div>

                {/* Items */}
                <div className="space-y-1.5">
                  {order.items.map((item, idx) => (
                    <div key={idx} className="flex justify-between text-xs text-slate-700">
                      <span>{item.title} × {item.quantity}</span>
                      <span className="font-mono font-bold">{formatPKR(item.price * item.quantity)}</span>
                    </div>
                  ))}
                </div>

                {/* Address */}
                <div className="pt-2 border-t border-slate-200 text-xs">
                  <span className="text-slate-500 font-semibold block">Delivery Address (COD):</span>
                  <p className="text-slate-800 font-medium mt-0.5">{order.address}, {order.city}</p>
                </div>

                {/* Total & WhatsApp Help */}
                <div className="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                  <div>
                    <span className="text-slate-500">Payable on Delivery:</span>
                    <span className="font-mono font-bold text-emerald-700 text-sm ml-1.5">
                      {formatPKR(order.totalAmount)}
                    </span>
                  </div>

                  <a
                    href={`https://wa.me/${settings.whatsapp}?text=Order%20${order.id}%20Update`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-emerald-700 font-semibold flex items-center gap-1 hover:underline"
                  >
                    <MessageCircle className="w-3.5 h-3.5" />
                    <span>WhatsApp Support</span>
                  </a>
                </div>
              </div>
            ))
          ) : (
            <div className="py-8 text-center text-xs text-slate-500">
              You have not placed any orders yet.
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
