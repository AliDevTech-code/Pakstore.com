import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR, formatDate } from '../../utils/format';
import { X, Search, Package, CheckCircle2, Clock, Truck, ShieldAlert } from 'lucide-react';
import { Order } from '../../types';

export const OrderTrackerModal: React.FC = () => {
  const { orders, isOrderTrackerOpen, setIsOrderTrackerOpen } = useStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [foundOrder, setFoundOrder] = useState<Order | null>(null);
  const [searched, setSearched] = useState(false);

  if (!isOrderTrackerOpen) return null;

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setSearched(true);
    const term = searchTerm.trim().toLowerCase();

    if (!term) return;

    // Search by Order ID or phone
    const cleanTerm = term.replace(/[^a-z0-9]/gi, '');
    const matched = orders.find((o) => {
      const cleanId = o.id.toLowerCase().replace(/[^a-z0-9]/gi, '');
      const cleanPhone = o.phone.replace(/[^0-9]/g, '');
      return cleanId.includes(cleanTerm) || cleanPhone.includes(cleanTerm);
    });

    setFoundOrder(matched || null);
  };

  const getStatusStep = (status: Order['status']) => {
    switch (status) {
      case 'Pending':
        return 1;
      case 'Processing':
        return 2;
      case 'Shipped':
        return 3;
      case 'Delivered':
        return 4;
      case 'Cancelled':
        return 0;
      default:
        return 1;
    }
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in fade-in duration-200">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Package className="w-5 h-5 text-emerald-400" />
            <h2 className="text-base font-bold tracking-tight">Track Your Order Status</h2>
          </div>
          <button
            onClick={() => setIsOrderTrackerOpen(false)}
            className="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <div className="p-6 space-y-6">
          {/* Search Box */}
          <form onSubmit={handleSearch} className="space-y-2">
            <label className="block text-xs font-semibold text-slate-700">
              Enter Order Tracking ID or Mobile Number:
            </label>
            <div className="flex gap-2">
              <input
                type="text"
                placeholder="e.g. PAK-78219 or 03001234567"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="flex-1 px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 focus:border-emerald-600"
              />
              <button
                type="submit"
                className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-colors flex items-center gap-1.5 cursor-pointer"
              >
                <Search className="w-3.5 h-3.5" />
                <span>Track</span>
              </button>
            </div>
          </form>

          {/* Results display */}
          {foundOrder ? (
            <div className="space-y-5 pt-2">
              {/* Order Status Badge & Meta */}
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div className="flex items-center justify-between">
                  <div>
                    <span className="text-[11px] text-slate-400 font-mono block">Order ID</span>
                    <span className="font-mono font-bold text-slate-900 text-base">#{foundOrder.id}</span>
                  </div>
                  <span
                    className={`text-xs font-bold px-2.5 py-1 rounded-md ${
                      foundOrder.status === 'Delivered'
                        ? 'bg-emerald-100 text-emerald-800'
                        : foundOrder.status === 'Shipped'
                        ? 'bg-blue-100 text-blue-800'
                        : foundOrder.status === 'Processing'
                        ? 'bg-indigo-100 text-indigo-800'
                        : foundOrder.status === 'Cancelled'
                        ? 'bg-rose-100 text-rose-800'
                        : 'bg-amber-100 text-amber-800'
                    }`}
                  >
                    {foundOrder.status}
                  </span>
                </div>

                <div className="mt-3 text-xs text-slate-500 flex justify-between">
                  <span>Placed on: {formatDate(foundOrder.createdAt)}</span>
                  <span className="font-bold text-slate-800">{formatPKR(foundOrder.totalAmount)} (COD)</span>
                </div>
              </div>

              {/* Progress Steps */}
              {foundOrder.status !== 'Cancelled' ? (
                <div className="py-2">
                  <span className="text-xs font-bold text-slate-800 block mb-3">Delivery Timeline:</span>
                  <div className="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    {/* Step 1 */}
                    <div className="relative">
                      <div className={`absolute -left-6 top-0 w-4 h-4 rounded-full flex items-center justify-center text-[10px] ${
                        getStatusStep(foundOrder.status) >= 1 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'
                      }`}>
                        ✓
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">Order Placed (Cash on Delivery)</h4>
                      <p className="text-[11px] text-slate-500">Order received in Pakstore system.</p>
                    </div>

                    {/* Step 2 */}
                    <div className="relative">
                      <div className={`absolute -left-6 top-0 w-4 h-4 rounded-full flex items-center justify-center text-[10px] ${
                        getStatusStep(foundOrder.status) >= 2 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'
                      }`}>
                        {getStatusStep(foundOrder.status) >= 2 ? '✓' : '2'}
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">Confirmed & Packed</h4>
                      <p className="text-[11px] text-slate-500">Inventory reserved and package sealed.</p>
                    </div>

                    {/* Step 3 */}
                    <div className="relative">
                      <div className={`absolute -left-6 top-0 w-4 h-4 rounded-full flex items-center justify-center text-[10px] ${
                        getStatusStep(foundOrder.status) >= 3 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'
                      }`}>
                        {getStatusStep(foundOrder.status) >= 3 ? '✓' : '3'}
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">
                        Dispatched via {foundOrder.trackingCourier || 'TCS'}
                      </h4>
                      <p className="text-[11px] text-slate-500">
                        {foundOrder.trackingNumber ? `Tracking: ${foundOrder.trackingNumber}` : 'In transit with courier rider.'}
                      </p>
                    </div>

                    {/* Step 4 */}
                    <div className="relative">
                      <div className={`absolute -left-6 top-0 w-4 h-4 rounded-full flex items-center justify-center text-[10px] ${
                        getStatusStep(foundOrder.status) >= 4 ? 'bg-emerald-600 text-white' : 'bg-slate-300 text-slate-600'
                      }`}>
                        {getStatusStep(foundOrder.status) >= 4 ? '✓' : '4'}
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">Delivered & Cash Collected</h4>
                      <p className="text-[11px] text-slate-500">Handed over to customer.</p>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="p-3 bg-rose-50 text-rose-700 text-xs rounded-lg">
                  This order was marked as cancelled. Please contact customer support on WhatsApp for queries.
                </div>
              )}

              {/* Destination Address Preview */}
              <div className="p-3.5 bg-slate-50 rounded-xl text-xs border border-slate-200">
                <span className="font-bold text-slate-700 block mb-1">Delivery Destination:</span>
                <p className="text-slate-900 font-medium">{foundOrder.customerName} ({foundOrder.phone})</p>
                <p className="text-slate-600 mt-0.5">{foundOrder.address}, {foundOrder.city}</p>
              </div>
            </div>
          ) : searched ? (
            <div className="p-6 text-center bg-slate-50 rounded-xl border border-slate-200">
              <ShieldAlert className="w-8 h-8 text-slate-400 mx-auto mb-2" />
              <h4 className="text-xs font-bold text-slate-800">No order found</h4>
              <p className="text-[11px] text-slate-500 mt-1">
                Please verify your tracking ID (e.g. PAK-78219) or mobile number and try again.
              </p>
            </div>
          ) : (
            <div className="text-center py-6 text-xs text-slate-500">
              Try searching with sample orders like <span className="font-mono font-bold text-slate-800">PAK-78219</span> or phone <span className="font-mono font-bold text-slate-800">0300-4567891</span>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
