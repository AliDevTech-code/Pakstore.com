import React, { useMemo } from 'react';
import { useStore } from '../../context/StoreContext';
import { formatPKR } from '../../utils/format';
import { DollarSign, ShoppingBag, Clock, CheckCircle2, TrendingUp, MapPin } from 'lucide-react';

export const AdminAnalyticsTab: React.FC = () => {
  const { orders, products } = useStore();

  const metrics = useMemo(() => {
    const validOrders = orders.filter((o) => o.status !== 'Cancelled');
    const totalRevenue = validOrders.reduce((sum, o) => sum + o.totalAmount, 0);
    const deliveredOrders = orders.filter((o) => o.status === 'Delivered');
    const deliveredRevenue = deliveredOrders.reduce((sum, o) => sum + o.totalAmount, 0);
    const pendingOrders = orders.filter((o) => o.status === 'Pending');
    const avgOrderValue = validOrders.length > 0 ? Math.round(totalRevenue / validOrders.length) : 0;

    // City distribution
    const cityCountMap: Record<string, number> = {};
    orders.forEach((o) => {
      cityCountMap[o.city] = (cityCountMap[o.city] || 0) + 1;
    });

    const cityStats = Object.entries(cityCountMap)
      .map(([city, count]) => ({ city, count }))
      .sort((a, b) => b.count - a.count);

    // Product sales count
    const productSalesMap: Record<string, { title: string; count: number; revenue: number }> = {};
    orders.forEach((o) => {
      if (o.status !== 'Cancelled') {
        o.items.forEach((item) => {
          if (!productSalesMap[item.productId]) {
            productSalesMap[item.productId] = {
              title: item.title,
              count: 0,
              revenue: 0,
            };
          }
          productSalesMap[item.productId].count += item.quantity;
          productSalesMap[item.productId].revenue += item.price * item.quantity;
        });
      }
    });

    const topSelling = Object.values(productSalesMap).sort((a, b) => b.count - a.count);

    return {
      totalRevenue,
      deliveredRevenue,
      totalOrders: orders.length,
      pendingOrdersCount: pendingOrders.length,
      deliveredOrdersCount: deliveredOrders.length,
      avgOrderValue,
      cityStats,
      topSelling,
    };
  }, [orders]);

  return (
    <div className="space-y-6">
      {/* 4-Column KPI Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Total Revenue */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">Total Sales (COD)</span>
            <div className="p-2 rounded-lg bg-emerald-50 text-emerald-700">
              <DollarSign className="w-4 h-4" />
            </div>
          </div>
          <div className="text-2xl font-black text-slate-900 font-mono">
            {formatPKR(metrics.totalRevenue)}
          </div>
          <span className="text-[11px] text-emerald-600 font-medium block mt-1">
            Cash on Delivery Across Pakistan
          </span>
        </div>

        {/* Total Orders */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">Total Orders</span>
            <div className="p-2 rounded-lg bg-blue-50 text-blue-700">
              <ShoppingBag className="w-4 h-4" />
            </div>
          </div>
          <div className="text-2xl font-black text-slate-900 font-mono">
            {metrics.totalOrders}
          </div>
          <span className="text-[11px] text-slate-500 block mt-1">
            {metrics.deliveredOrdersCount} Successfully Delivered
          </span>
        </div>

        {/* Pending Orders */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">Pending Orders</span>
            <div className="p-2 rounded-lg bg-amber-50 text-amber-700">
              <Clock className="w-4 h-4" />
            </div>
          </div>
          <div className="text-2xl font-black text-amber-700 font-mono">
            {metrics.pendingOrdersCount}
          </div>
          <span className="text-[11px] text-amber-700 font-medium block mt-1">
            Awaiting phone verification
          </span>
        </div>

        {/* Average Order Value */}
        <div className="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 mb-2">
            <span className="text-xs font-semibold uppercase tracking-wider">Avg Order Value</span>
            <div className="p-2 rounded-lg bg-indigo-50 text-indigo-700">
              <TrendingUp className="w-4 h-4" />
            </div>
          </div>
          <div className="text-2xl font-black text-slate-900 font-mono">
            {formatPKR(metrics.avgOrderValue)}
          </div>
          <span className="text-[11px] text-slate-500 block mt-1">
            Per Pakistani customer
          </span>
        </div>
      </div>

      {/* 2-Column Section: Top Products & City Breakdown */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Products */}
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <h3 className="font-bold text-slate-900 text-sm mb-4">Top Selling Items in Pakistan</h3>
          {metrics.topSelling.length > 0 ? (
            <div className="space-y-3">
              {metrics.topSelling.map((prod, idx) => (
                <div
                  key={idx}
                  className="flex items-center justify-between py-2 border-b border-slate-100 last:border-b-0 text-xs"
                >
                  <div className="flex items-center gap-3">
                    <span className="font-bold text-slate-400 font-mono w-4">#{idx + 1}</span>
                    <span className="font-semibold text-slate-800 truncate max-w-xs">{prod.title}</span>
                  </div>
                  <div className="text-right">
                    <span className="font-mono font-bold text-slate-900 block">{prod.count} units</span>
                    <span className="text-[10px] text-slate-400">{formatPKR(prod.revenue)}</span>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <p className="text-xs text-slate-500">No product sales data yet.</p>
          )}
        </div>

        {/* City Breakdown */}
        <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
          <h3 className="font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
            <MapPin className="w-4 h-4 text-emerald-600" />
            <span>Orders by Pakistani Cities</span>
          </h3>

          <div className="space-y-3">
            {metrics.cityStats.map((item, idx) => {
              const percentage = Math.round((item.count / (orders.length || 1)) * 100);
              return (
                <div key={idx} className="space-y-1 text-xs">
                  <div className="flex justify-between font-medium">
                    <span className="text-slate-800 font-semibold">{item.city}</span>
                    <span className="text-slate-500 font-mono">
                      {item.count} orders ({percentage}%)
                    </span>
                  </div>
                  <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div
                      className="bg-emerald-600 h-full rounded-full transition-all"
                      style={{ width: `${percentage}%` }}
                    />
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
};
