import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { AdminOrdersTab } from './AdminOrdersTab';
import { AdminProductsTab } from './AdminProductsTab';
import { AdminCategoriesTab } from './AdminCategoriesTab';
import { AdminBannersTab } from './AdminBannersTab';
import { AdminUsersTab } from './AdminUsersTab';
import { AdminAnalyticsTab } from './AdminAnalyticsTab';
import { AdminInfinityFreeTab } from './AdminInfinityFreeTab';
import { AdminSettingsTab } from './AdminSettingsTab';
import { formatPKR } from '../../utils/format';
import {
  Package,
  ShoppingBag,
  BarChart3,
  Server,
  ArrowLeft,
  RotateCcw,
  Sliders,
  LogOut,
  Tag,
  Sparkles,
  Users,
  DollarSign,
  TrendingUp,
} from 'lucide-react';

export const AdminDashboard: React.FC = () => {
  const {
    setIsAdminView,
    orders,
    products,
    categories,
    banners,
    users,
    resetAllData,
    adminLogout,
    settings,
  } = useStore();

  const [activeTab, setActiveTab] = useState<
    'orders' | 'products' | 'categories' | 'banners' | 'users' | 'analytics' | 'settings' | 'infinityfree'
  >('orders');

  const pendingOrdersCount = orders.filter((o) => o.status === 'Pending').length;

  // KPI Calculations
  const totalProducts = products.length;
  const totalOrders = orders.length;
  const totalUsers = users.length;
  const totalRevenue = orders
    .filter((o) => o.status !== 'Cancelled')
    .reduce((sum, o) => sum + o.totalAmount, 0);

  return (
    <div className="min-h-screen bg-slate-100 text-slate-900 pb-16">
      {/* Admin Top Navigation */}
      <header className="bg-slate-900 text-white sticky top-0 z-40 border-b border-slate-800 shadow-md">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16 gap-4">
            {/* Left: Return to Store & Brand */}
            <div className="flex items-center gap-3 shrink-0">
              <button
                onClick={() => setIsAdminView(false)}
                className="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1.5 text-xs font-semibold cursor-pointer"
                title="Return to Customer Store"
              >
                <ArrowLeft className="w-4 h-4" />
                <span className="hidden sm:inline">Storefront</span>
              </button>

              <div className="h-5 w-px bg-slate-800" aria-hidden="true" />

              <div className="flex items-center gap-1.5">
                <span className="bg-[#00A651] text-white font-black text-xs px-2 py-0.5 rounded">
                  ADMIN
                </span>
                <span className="font-bold text-sm sm:text-base tracking-tight text-white">
                  PakStore Control
                </span>
              </div>
            </div>

            {/* Navigation Tabs (Scrollable on small screens) */}
            <nav className="flex items-center gap-1 overflow-x-auto py-2">
              <button
                onClick={() => setActiveTab('orders')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'orders'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Package className="w-3.5 h-3.5" />
                <span>Orders</span>
                {pendingOrdersCount > 0 && (
                  <span className="bg-[#FF6B00] text-white text-[10px] font-black px-1.5 py-0.2 rounded-full">
                    {pendingOrdersCount}
                  </span>
                )}
              </button>

              <button
                onClick={() => setActiveTab('products')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'products'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <ShoppingBag className="w-3.5 h-3.5" />
                <span>Products ({products.length})</span>
              </button>

              <button
                onClick={() => setActiveTab('categories')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'categories'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Tag className="w-3.5 h-3.5" />
                <span>Categories ({categories.length})</span>
              </button>

              <button
                onClick={() => setActiveTab('banners')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'banners'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Sparkles className="w-3.5 h-3.5 text-[#FF6B00]" />
                <span>Banners</span>
              </button>

              <button
                onClick={() => setActiveTab('users')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'users'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Users className="w-3.5 h-3.5" />
                <span>Users ({users.length})</span>
              </button>

              <button
                onClick={() => setActiveTab('settings')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'settings'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Sliders className="w-3.5 h-3.5" />
                <span>Logo & Settings</span>
              </button>

              <button
                onClick={() => setActiveTab('infinityfree')}
                className={`px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer whitespace-nowrap shrink-0 ${
                  activeTab === 'infinityfree'
                    ? 'bg-[#00A651] text-white shadow-xs'
                    : 'text-slate-300 hover:text-white hover:bg-slate-800'
                }`}
              >
                <Server className="w-3.5 h-3.5 text-emerald-400" />
                <span>InfinityFree PHP</span>
              </button>
            </nav>

            {/* Right: Actions */}
            <div className="flex items-center gap-2 shrink-0">
              <button
                onClick={() => {
                  if (confirm('Reset store demo data (orders, products, settings) to initial defaults?')) {
                    resetAllData();
                  }
                }}
                className="p-1.5 text-slate-400 hover:text-slate-200 hover:bg-slate-800 rounded-lg text-xs transition-colors cursor-pointer"
                title="Reset Demo Data"
              >
                <RotateCcw className="w-4 h-4" />
              </button>

              <button
                onClick={adminLogout}
                className="px-3 py-1.5 bg-rose-900/60 hover:bg-rose-800 text-rose-200 border border-rose-700/60 text-xs font-semibold rounded-lg transition-colors cursor-pointer flex items-center gap-1"
                title="Log out of Admin Panel"
              >
                <LogOut className="w-3.5 h-3.5" />
                <span className="hidden md:inline">Log out</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Admin Dashboard Container */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        {/* KPI Summary Cards: Total Products, Total Orders, Total Users, Total Revenue */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div className="p-3 rounded-lg bg-emerald-50 text-[#00A651]">
              <ShoppingBag className="w-5 h-5" />
            </div>
            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Products
              </span>
              <span className="text-xl font-black text-slate-900 font-mono">
                {totalProducts}
              </span>
            </div>
          </div>

          <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div className="p-3 rounded-lg bg-amber-50 text-[#FF6B00]">
              <Package className="w-5 h-5" />
            </div>
            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Orders
              </span>
              <span className="text-xl font-black text-slate-900 font-mono">
                {totalOrders}
              </span>
            </div>
          </div>

          <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div className="p-3 rounded-lg bg-blue-50 text-blue-600">
              <Users className="w-5 h-5" />
            </div>
            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Users
              </span>
              <span className="text-xl font-black text-slate-900 font-mono">
                {totalUsers}
              </span>
            </div>
          </div>

          <div className="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div className="p-3 rounded-lg bg-emerald-50 text-[#00A651]">
              <TrendingUp className="w-5 h-5" />
            </div>
            <div>
              <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                Total Revenue
              </span>
              <span className="text-lg font-black text-[#00A651] font-mono">
                {formatPKR(totalRevenue)}
              </span>
            </div>
          </div>
        </div>

        {/* Tab Views */}
        {activeTab === 'orders' && <AdminOrdersTab />}
        {activeTab === 'products' && <AdminProductsTab />}
        {activeTab === 'categories' && <AdminCategoriesTab />}
        {activeTab === 'banners' && <AdminBannersTab />}
        {activeTab === 'users' && <AdminUsersTab />}
        {activeTab === 'analytics' && <AdminAnalyticsTab />}
        {activeTab === 'settings' && <AdminSettingsTab />}
        {activeTab === 'infinityfree' && <AdminInfinityFreeTab />}
      </main>
    </div>
  );
};
