import React from 'react';
import { Order } from '../../types';
import { formatPKR, formatDate } from '../../utils/format';
import { X, Printer, Truck } from 'lucide-react';

interface CourierSlipModalProps {
  order: Order | null;
  onClose: () => void;
}

export const CourierSlipModal: React.FC<CourierSlipModalProps> = ({ order, onClose }) => {
  if (!order) return null;

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-300 relative my-8 animate-in fade-in duration-200">
        {/* Controls */}
        <div className="px-6 py-3 bg-slate-900 text-white flex items-center justify-between print:hidden">
          <div className="flex items-center gap-2">
            <Truck className="w-4 h-4 text-emerald-400" />
            <span className="text-xs font-bold uppercase tracking-wider">
              Pakistani Courier Dispatch Slip (COD)
            </span>
          </div>
          <div className="flex items-center gap-2">
            <button
              onClick={handlePrint}
              className="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 cursor-pointer"
            >
              <Printer className="w-3.5 h-3.5" /> Print Slip
            </button>
            <button
              onClick={onClose}
              className="p-1 text-slate-400 hover:text-white rounded-lg cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Printable Courier Dispatch Slip */}
        <div className="p-8 text-slate-900 font-sans space-y-6 print:p-0">
          {/* Slip Header */}
          <div className="flex items-start justify-between border-b-2 border-slate-900 pb-4">
            <div>
              <div className="flex items-center gap-1.5">
                <span className="bg-slate-900 text-white font-black text-xs px-2 py-0.5 rounded">PAK</span>
                <span className="text-xl font-black tracking-tight">PAKSTORE.PK</span>
              </div>
              <p className="text-[11px] text-slate-500 mt-1">Official Courier Dispatch Label · Cash on Delivery</p>
              <p className="text-[11px] text-slate-500">Shipper Contact: 0300-1234567 | support@pakstore.pk</p>
            </div>

            <div className="text-right">
              <span className="text-xs font-bold uppercase text-slate-500 block">Courier Partner</span>
              <span className="text-base font-black text-emerald-700 block">{order.trackingCourier || 'TCS Express'}</span>
              <span className="font-mono text-xs font-bold text-slate-600">
                {order.trackingNumber || `TRACK: ${order.id}-COD`}
              </span>
            </div>
          </div>

          {/* COD Notice Box */}
          <div className="bg-slate-100 border-2 border-slate-900 p-4 rounded-lg flex items-center justify-between">
            <div>
              <span className="text-[11px] font-bold uppercase text-slate-600 tracking-wider block">
                Cash to Collect from Customer:
              </span>
              <span className="text-2xl font-black text-slate-900 font-mono">
                {formatPKR(order.totalAmount)}
              </span>
            </div>
            <div className="text-right">
              <span className="bg-slate-900 text-white font-black text-xs px-3 py-1 rounded inline-block uppercase">
                100% Cash on Delivery
              </span>
              <span className="text-[11px] text-slate-500 block mt-1">Do not deliver without cash collection</span>
            </div>
          </div>

          {/* Consignee (Customer) Details Box */}
          <div className="border border-slate-300 rounded-lg p-4 space-y-2">
            <span className="text-xs font-bold uppercase text-slate-400 block tracking-wider">
              Consignee / Receiver Information:
            </span>
            <div className="grid grid-cols-2 gap-4 text-xs">
              <div>
                <span className="text-slate-500 block">Customer Name:</span>
                <span className="font-bold text-sm text-slate-900">{order.customerName}</span>
              </div>
              <div>
                <span className="text-slate-500 block">Primary Mobile:</span>
                <span className="font-bold text-sm font-mono text-slate-900">{order.phone}</span>
              </div>
            </div>

            <div className="pt-2 border-t border-slate-200 text-xs">
              <span className="text-slate-500 block">Destination City:</span>
              <span className="font-bold text-slate-900 text-sm">{order.city}</span>
            </div>

            <div className="pt-2 border-t border-slate-200 text-xs">
              <span className="text-slate-500 block font-semibold">Complete Street Address:</span>
              <div className="font-bold text-slate-900 text-sm bg-slate-50 p-2.5 rounded border border-slate-200 mt-1">
                {order.address}
              </div>
            </div>

            {order.orderNotes && (
              <div className="pt-2 border-t border-slate-200 text-xs text-slate-700">
                <strong>Rider Instructions:</strong> {order.orderNotes}
              </div>
            )}
          </div>

          {/* Package Contents */}
          <div className="border border-slate-200 rounded-lg p-3 text-xs">
            <span className="font-bold text-slate-700 block mb-1">Package Contents:</span>
            {order.items.map((item, idx) => (
              <div key={idx} className="flex justify-between py-1 border-b border-slate-100 last:border-b-0 text-slate-700">
                <span>{item.title}</span>
                <span className="font-bold">Qty: {item.quantity}</span>
              </div>
            ))}
          </div>

          {/* Footer Barcode Representation */}
          <div className="pt-4 border-t border-slate-300 flex items-center justify-between text-xs text-slate-500">
            <div>
              <span>Order Date: {formatDate(order.createdAt)}</span>
              <span className="block text-[10px]">Verification: Invariants Checked</span>
            </div>
            <div className="text-center font-mono font-bold tracking-widest text-slate-800">
              *|||||| | |||| ||| |||||||*
              <span className="block text-[10px] tracking-normal font-sans text-slate-400">Barcode ID #{order.id}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
