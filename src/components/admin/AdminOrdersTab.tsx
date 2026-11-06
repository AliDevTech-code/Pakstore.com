import React, { useState, useMemo } from 'react';
import { useStore } from '../../context/StoreContext';
import { Order, OrderStatus } from '../../types';
import { formatPKR, formatDate, cleanPhoneForWhatsApp } from '../../utils/format';
import {
  Search,
  Printer,
  Trash2,
  MapPin,
  Phone,
  MessageCircle,
  Truck,
  CheckCircle,
  Clock,
  AlertCircle,
  FileText,
} from 'lucide-react';
import { CourierSlipModal } from './CourierSlipModal';

export const AdminOrdersTab: React.FC = () => {
  const { orders, updateOrderStatus, deleteOrder } = useStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState<string>('All');
  const [selectedSlipOrder, setSelectedSlipOrder] = useState<Order | null>(null);

  const filteredOrders = useMemo(() => {
    return orders.filter((order) => {
      // Status filter
      if (statusFilter !== 'All' && order.status !== statusFilter) {
        return false;
      }
      // Search term
      if (searchTerm.trim()) {
        const q = searchTerm.toLowerCase().trim();
        return (
          order.id.toLowerCase().includes(q) ||
          order.customerName.toLowerCase().includes(q) ||
          order.phone.includes(q) ||
          order.city.toLowerCase().includes(q) ||
          order.address.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [orders, statusFilter, searchTerm]);

  const handleStatusChange = (orderId: string, newStatus: OrderStatus) => {
    updateOrderStatus(orderId, newStatus);
  };

  const handleCourierChange = (
    orderId: string,
    courier: Order['trackingCourier'],
    trackingNum?: string
  ) => {
    const existing = orders.find((o) => o.id === orderId);
    if (existing) {
      updateOrderStatus(orderId, existing.status, courier, trackingNum);
    }
  };

  return (
    <div className="space-y-6">
      {/* Top Filter and Search Bar */}
      <div className="bg-white p-4 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        {/* Search */}
        <div className="relative flex-1 max-w-md">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
          <input
            type="text"
            placeholder="Search customer, phone, address, or order ID..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 focus:border-emerald-600 text-slate-900"
          />
        </div>

        {/* Status Filter Buttons */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
          {['All', 'Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'].map((status) => (
            <button
              key={status}
              onClick={() => setStatusFilter(status)}
              className={`px-3 py-1.5 rounded-lg font-medium transition-colors cursor-pointer whitespace-nowrap ${
                statusFilter === status
                  ? 'bg-slate-900 text-white shadow-xs'
                  : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200'
              }`}
            >
              {status} {status === 'All' ? `(${orders.length})` : ''}
            </button>
          ))}
        </div>
      </div>

      {/* Orders List */}
      {filteredOrders.length > 0 ? (
        <div className="space-y-5">
          {filteredOrders.map((order) => {
            const waPhone = cleanPhoneForWhatsApp(order.phone);
            const waMessage = encodeURIComponent(
              `Assalam-o-Alaikum ${order.customerName}, Pakstore se call/message kar rahay hain aapke Cash on Delivery Order #${order.id} ki verification k liye.`
            );
            const waUrl = `https://wa.me/${waPhone}?text=${waMessage}`;

            return (
              <div
                key={order.id}
                className="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 shadow-xs hover:border-slate-300 transition-all space-y-4"
              >
                {/* Header row: ID, Date, Status */}
                <div className="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
                  <div className="flex items-center gap-3">
                    <span className="font-mono text-base font-extrabold text-emerald-800">
                      #{order.id}
                    </span>
                    <span className="text-xs text-slate-400">
                      {formatDate(order.createdAt)}
                    </span>
                    <span className="text-[11px] font-bold bg-slate-100 text-slate-700 px-2 py-0.5 rounded">
                      💵 Cash on Delivery
                    </span>
                  </div>

                  {/* Status Dropdown Controller */}
                  <div className="flex items-center gap-2">
                    <span className="text-xs text-slate-500 font-medium">Status:</span>
                    <select
                      value={order.status}
                      onChange={(e) => handleStatusChange(order.id, e.target.value as OrderStatus)}
                      className={`text-xs font-bold px-3 py-1.5 rounded-lg border focus:outline-none cursor-pointer ${
                        order.status === 'Delivered'
                          ? 'bg-emerald-50 text-emerald-800 border-emerald-300'
                          : order.status === 'Shipped'
                          ? 'bg-blue-50 text-blue-800 border-blue-300'
                          : order.status === 'Processing'
                          ? 'bg-indigo-50 text-indigo-800 border-indigo-300'
                          : order.status === 'Cancelled'
                          ? 'bg-rose-50 text-rose-800 border-rose-300'
                          : 'bg-amber-50 text-amber-800 border-amber-300'
                      }`}
                    >
                      <option value="Pending">⏳ Pending</option>
                      <option value="Processing">⚙️ Processing</option>
                      <option value="Shipped">🚚 Shipped</option>
                      <option value="Delivered">🎉 Delivered</option>
                      <option value="Cancelled">❌ Cancelled</option>
                    </select>

                    <button
                      onClick={() => setSelectedSlipOrder(order)}
                      className="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-colors cursor-pointer"
                      title="Print Courier Dispatch Slip"
                    >
                      <Printer className="w-3.5 h-3.5" />
                      <span className="hidden sm:inline">Courier Slip</span>
                    </button>

                    <button
                      onClick={() => {
                        if (confirm(`Are you sure you want to delete order #${order.id}?`)) {
                          deleteOrder(order.id);
                        }
                      }}
                      className="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition-colors cursor-pointer"
                      title="Delete order"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>

                {/* 3-Column Split: Customer Address | Ordered Items | Courier & Total */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                  {/* Column 1: Customer & Address (Highlighted for courier dispatch) */}
                  <div className="lg:col-span-5 bg-amber-50/70 border border-amber-200/80 rounded-xl p-4 space-y-3">
                    <div className="flex items-center justify-between">
                      <span className="text-[11px] font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1">
                        <MapPin className="w-3.5 h-3.5 text-amber-700" /> Customer & Delivery Address
                      </span>
                      <a
                        href={waUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md flex items-center gap-1 transition-colors shadow-xs"
                      >
                        <MessageCircle className="w-3 h-3" /> WhatsApp Chat
                      </a>
                    </div>

                    <div>
                      <span className="text-xs text-slate-500 font-medium block">Customer Name:</span>
                      <span className="text-sm font-bold text-slate-900 block">{order.customerName}</span>
                    </div>

                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <div>
                        <span className="text-slate-500 font-medium block">Phone / WhatsApp:</span>
                        <span className="font-mono font-bold text-slate-900">{order.phone}</span>
                      </div>
                      <div>
                        <span className="text-slate-500 font-medium block">Destination City:</span>
                        <span className="font-bold text-slate-900">{order.city}</span>
                      </div>
                    </div>

                    {/* Full Address Container */}
                    <div className="pt-2 border-t border-amber-200">
                      <span className="text-xs font-semibold text-slate-700 block mb-1">
                        Full Delivery Address:
                      </span>
                      <div className="text-xs font-medium text-slate-900 bg-white p-2.5 rounded-lg border border-amber-200/90 leading-relaxed shadow-xs">
                        {order.address}
                      </div>
                    </div>

                    {order.orderNotes && (
                      <div className="text-xs text-slate-600 bg-amber-100/60 p-2 rounded-lg flex items-start gap-1.5">
                        <FileText className="w-3.5 h-3.5 text-amber-700 shrink-0 mt-0.5" />
                        <span>
                          <strong>Delivery Note:</strong> {order.orderNotes}
                        </span>
                      </div>
                    )}
                  </div>

                  {/* Column 2: Items Ordered */}
                  <div className="lg:col-span-4 flex flex-col justify-between">
                    <div>
                      <span className="text-xs font-bold text-slate-700 block mb-2">
                        Items in Parcel ({order.items.reduce((s, i) => s + i.quantity, 0)}):
                      </span>
                      <div className="space-y-2 border border-slate-200 rounded-xl p-3 bg-slate-50/50 max-h-48 overflow-y-auto">
                        {order.items.map((item, idx) => (
                          <div
                            key={idx}
                            className="flex items-center justify-between text-xs py-1.5 border-b border-slate-100 last:border-b-0"
                          >
                            <div className="flex items-center gap-2 min-w-0">
                              <img
                                src={item.image}
                                alt={item.title}
                                className="w-9 h-9 rounded object-cover border border-slate-200 shrink-0"
                              />
                              <div className="min-w-0">
                                <span className="font-semibold text-slate-800 block truncate max-w-[170px]">
                                  {item.title}
                                </span>
                                <span className="text-slate-400 font-mono text-[11px]">
                                  Qty: {item.quantity} × {formatPKR(item.price)}
                                </span>
                              </div>
                            </div>
                            <span className="font-mono font-bold text-slate-900 shrink-0">
                              {formatPKR(item.price * item.quantity)}
                            </span>
                          </div>
                        ))}
                      </div>
                    </div>

                    {/* Subtotal & Delivery Breakup */}
                    <div className="mt-3 pt-2 border-t border-slate-100 text-xs space-y-1">
                      <div className="flex justify-between text-slate-500">
                        <span>Items Subtotal:</span>
                        <span className="font-mono font-bold text-slate-800">{formatPKR(order.subtotal)}</span>
                      </div>
                      <div className="flex justify-between text-slate-500">
                        <span>Delivery Fee:</span>
                        <span className="font-mono font-bold">
                          {order.shippingFee === 0 ? 'FREE' : formatPKR(order.shippingFee)}
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Column 3: Total COD & Courier Dispatch Info */}
                  <div className="lg:col-span-3 bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-col justify-between">
                    <div>
                      <span className="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                        Cash to Collect (COD)
                      </span>
                      <span className="text-2xl font-black text-emerald-700 font-mono block mt-1">
                        {formatPKR(order.totalAmount)}
                      </span>
                      <span className="text-[10px] text-slate-400 block mt-0.5">
                        Collect full cash from rider
                      </span>
                    </div>

                    {/* Courier Assignment */}
                    <div className="mt-4 pt-3 border-t border-slate-200 space-y-2 text-xs">
                      <span className="font-bold text-slate-700 block">Courier Assignment:</span>
                      <div>
                        <select
                          value={order.trackingCourier || 'TCS'}
                          onChange={(e) =>
                            handleCourierChange(
                              order.id,
                              e.target.value as any,
                              order.trackingNumber
                            )
                          }
                          className="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded-md bg-white text-slate-800"
                        >
                          <option value="TCS">TCS Express</option>
                          <option value="Leopards Courier">Leopards Courier</option>
                          <option value="Trax Express">Trax Logistics</option>
                          <option value="M&P">M&P Express</option>
                          <option value="PostEx">PostEx Courier</option>
                        </select>
                      </div>

                      <div>
                        <input
                          type="text"
                          placeholder="Courier Tracking #..."
                          value={order.trackingNumber || ''}
                          onChange={(e) =>
                            handleCourierChange(
                              order.id,
                              order.trackingCourier || 'TCS',
                              e.target.value
                            )
                          }
                          className="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded-md bg-white font-mono"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      ) : (
        <div className="p-12 text-center bg-white rounded-2xl border border-slate-200">
          <Clock className="w-10 h-10 text-slate-400 mx-auto mb-3" />
          <h3 className="text-base font-bold text-slate-800">No matching orders found</h3>
          <p className="text-xs text-slate-500 mt-1">
            Try clearing the search filter or place a test order on the storefront.
          </p>
        </div>
      )}

      {/* Printable Courier Slip Modal */}
      <CourierSlipModal
        order={selectedSlipOrder}
        onClose={() => setSelectedSlipOrder(null)}
      />
    </div>
  );
};
