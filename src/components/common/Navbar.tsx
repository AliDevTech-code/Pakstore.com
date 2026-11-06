import React, { useState, useRef, useEffect } from 'react';
import { useStore } from '../../context/StoreContext';
import {
  ShoppingBag,
  Search,
  PackageCheck,
  Menu,
  X,
  User,
  LogOut,
  Package,
  ChevronDown,
  LayoutGrid,
} from 'lucide-react';

export const Navbar: React.FC = () => {
  const {
    cartCount,
    setIsCartOpen,
    setIsOrderTrackerOpen,
    searchQuery,
    setSearchQuery,
    categories,
    activeCategory,
    setActiveCategory,
    currentUser,
    setAuthModal,
    customerLogout,
    setIsMyOrdersOpen,
    settings,
    setSelectedProductPage,
  } = useStore();

  const [userDropdownOpen, setUserDropdownOpen] = useState(false);
  const [isCategoryDrawerOpen, setIsCategoryDrawerOpen] = useState(false);

  const handleNavClick = (categoryName?: string) => {
    setSelectedProductPage(null);
    if (categoryName) {
      setActiveCategory(categoryName);
      const el = document.getElementById('catalog-section');
      if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
      }
    }
    setIsCategoryDrawerOpen(false);
  };

  const handleSearchSubmit = (e?: React.FormEvent) => {
    if (e) e.preventDefault();
    setSelectedProductPage(null);
    const el = document.getElementById('catalog-section');
    if (el) el.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <header className="sticky top-0 z-30 bg-white border-b border-slate-200 text-slate-800 shadow-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between gap-3 sm:gap-6 h-18">
          {/* Left: Menu Toggle + Dynamic Brand Logo */}
          <div className="flex items-center gap-2 sm:gap-3">
            {/* Categories Menu Slider Toggle Button */}
            <button
              onClick={() => setIsCategoryDrawerOpen(true)}
              className="p-2 rounded-lg text-slate-700 hover:text-[#00A651] hover:bg-emerald-50 transition-colors cursor-pointer flex items-center gap-1.5"
              title="Categories Menu"
              aria-label="Open Categories Menu"
            >
              <Menu className="w-5 h-5" />
              <span className="hidden lg:inline text-xs font-bold">Categories</span>
            </button>

            {/* Brand Logo */}
            <button
              onClick={() => handleNavClick('All Products')}
              className="flex items-center gap-1.5 text-left group cursor-pointer focus-visible:outline-[#00A651] shrink-0"
            >
              {settings.logoUrl ? (
                <img
                  src={settings.logoUrl}
                  alt={settings.storeName}
                  className="h-9 max-w-[150px] object-contain rounded"
                />
              ) : (
                <div className="flex items-center">
                  <span className="text-2xl sm:text-3xl font-black tracking-tight text-[#00A651]">
                    Pak
                  </span>
                  <span className="text-2xl sm:text-3xl font-black tracking-tight text-[#FF6B00]">
                    Store
                  </span>
                  <span className="ml-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 self-end mb-1 hidden sm:inline">
                    .pk
                  </span>
                </div>
              )}
            </button>
          </div>

          {/* Center: Search Bar (Desktop) */}
          <div className="flex-1 max-w-xl mx-2 hidden sm:block">
            <form onSubmit={handleSearchSubmit} className="relative flex items-center">
              <input
                type="text"
                placeholder="Search in PakStore (Mobiles, Watches, Shoes, Clothes, Fragrances...)"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="w-full pl-4 pr-12 py-2.5 text-xs bg-slate-100/90 border border-slate-300 rounded-lg focus:outline-none focus:border-[#00A651] focus:bg-white text-slate-900 placeholder-slate-400 transition-all shadow-inner"
              />
              <button
                type="submit"
                className="absolute right-1 top-1 bottom-1 px-3.5 bg-[#FF6B00] hover:bg-[#e05e00] text-white rounded-md flex items-center justify-center transition-colors cursor-pointer"
                title="Search Products"
              >
                <Search className="w-4 h-4" />
              </button>
              {searchQuery && (
                <button
                  type="button"
                  onClick={() => setSearchQuery('')}
                  className="absolute right-12 text-slate-400 hover:text-slate-600 text-sm font-bold px-1"
                >
                  ✕
                </button>
              )}
            </form>
          </div>

          {/* Right: Track Order, Cart, User Account */}
          <div className="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
            {/* Track Order */}
            <button
              onClick={() => setIsOrderTrackerOpen(true)}
              className="hidden lg:flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:text-[#00A651] hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
            >
              <PackageCheck className="w-4 h-4 text-[#00A651]" />
              <span>Track Order</span>
            </button>

            {/* Shopping Cart Trigger */}
            <button
              onClick={() => setIsCartOpen(true)}
              className="relative p-2 text-slate-700 hover:text-[#00A651] hover:bg-emerald-50 rounded-lg transition-colors cursor-pointer"
              aria-label="View Shopping Cart"
              title="Shopping Cart"
            >
              <ShoppingBag className="w-5 h-5 text-[#00A651]" />
              {cartCount > 0 && (
                <span className="absolute -top-1 -right-1 bg-[#FF6B00] text-white font-black text-[10px] w-5 h-5 rounded-full flex items-center justify-center shadow-xs">
                  {cartCount}
                </span>
              )}
            </button>

            {/* Customer User Account */}
            {currentUser ? (
              <div className="relative">
                <button
                  onClick={() => setUserDropdownOpen(!userDropdownOpen)}
                  className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-slate-800 transition-colors cursor-pointer"
                >
                  <User className="w-3.5 h-3.5 text-[#00A651]" />
                  <span className="max-w-[75px] sm:max-w-[100px] truncate">
                    {currentUser.fullName.split(' ')[0]}
                  </span>
                  <ChevronDown className="w-3 h-3 text-slate-500" />
                </button>

                {userDropdownOpen && (
                  <div className="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-xl py-2 z-50 text-xs">
                    <div className="px-3.5 py-2 border-b border-slate-100">
                      <span className="font-bold text-slate-900 block truncate">
                        {currentUser.fullName}
                      </span>
                      <span className="text-[10px] text-slate-500 font-mono">
                        {currentUser.phone}
                      </span>
                    </div>

                    <button
                      onClick={() => {
                        setIsMyOrdersOpen(true);
                        setUserDropdownOpen(false);
                      }}
                      className="w-full px-3.5 py-2 text-left hover:bg-emerald-50 flex items-center gap-2 text-slate-700 font-medium cursor-pointer"
                    >
                      <Package className="w-4 h-4 text-[#00A651]" />
                      <span>My Orders & Delivery</span>
                    </button>

                    <button
                      onClick={() => {
                        customerLogout();
                        setUserDropdownOpen(false);
                      }}
                      className="w-full px-3.5 py-2 text-left hover:bg-rose-50 flex items-center gap-2 text-rose-600 font-medium cursor-pointer"
                    >
                      <LogOut className="w-4 h-4" />
                      <span>Sign Out</span>
                    </button>
                  </div>
                )}
              </div>
            ) : (
              <button
                onClick={() => setAuthModal(true, 'login')}
                className="px-3 py-1.5 text-xs font-bold text-white bg-[#00A651] hover:bg-[#008f45] rounded-lg transition-colors flex items-center gap-1 cursor-pointer whitespace-nowrap shadow-xs"
              >
                <User className="w-3.5 h-3.5 text-white" />
                <span>Login</span>
              </button>
            )}
          </div>
        </div>

        {/* Mobile Search Bar: Directly visible in Navbar on Mobile just like Desktop */}
        <div className="sm:hidden pb-3 pt-1">
          <form onSubmit={handleSearchSubmit} className="relative flex items-center">
            <input
              type="text"
              placeholder="Search in PakStore (Mobiles, Watches, Shoes, Clothes...)"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-3.5 pr-12 py-2 text-xs bg-slate-100 border border-slate-300 rounded-lg focus:outline-none focus:border-[#00A651] focus:bg-white text-slate-900 placeholder-slate-400 transition-all shadow-inner"
            />
            <button
              type="submit"
              className="absolute right-1 top-1 bottom-1 px-3 bg-[#FF6B00] hover:bg-[#e05e00] text-white rounded-md flex items-center justify-center transition-colors cursor-pointer"
              title="Search Products"
            >
              <Search className="w-3.5 h-3.5" />
            </button>
            {searchQuery && (
              <button
                type="button"
                onClick={() => setSearchQuery('')}
                className="absolute right-11 text-slate-400 hover:text-slate-600 text-xs font-bold px-1"
              >
                ✕
              </button>
            )}
          </form>
        </div>
      </div>

      {/* Categories Quick Navigation Strip */}
      <div className="bg-slate-50 border-t border-slate-200 overflow-x-auto scrollbar-none">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-4 py-2 text-xs font-semibold text-slate-700">
          <button
            onClick={() => setIsCategoryDrawerOpen(true)}
            className="flex items-center gap-1.5 text-[#00A651] hover:underline cursor-pointer font-bold shrink-0"
          >
            <LayoutGrid className="w-3.5 h-3.5" />
            <span>All Categories ☰</span>
          </button>

          <span className="text-slate-300">|</span>

          {categories.map((cat) => (
            <button
              key={cat.id}
              onClick={() => handleNavClick(cat.name)}
              className={`cursor-pointer whitespace-nowrap transition-colors hover:text-[#00A651] ${
                activeCategory === cat.name ? 'text-[#00A651] font-bold border-b-2 border-[#00A651]' : ''
              }`}
            >
              {cat.name}
            </button>
          ))}
        </div>
      </div>

      {/* Slide-out Category Drawer (Menu toggle) */}
      {isCategoryDrawerOpen && (
        <div className="fixed inset-0 z-50 overflow-hidden bg-slate-950/60 backdrop-blur-xs flex animate-in fade-in duration-200">
          <div className="w-full max-w-xs sm:max-w-sm bg-white text-slate-900 h-full p-5 shadow-2xl flex flex-col justify-between animate-in slide-in-from-left duration-200">
            <div>
              <div className="flex items-center justify-between pb-3 border-b border-slate-200">
                <div className="flex items-center gap-2">
                  <span className="bg-[#00A651] text-white font-black text-xs px-2 py-0.5 rounded">PAK</span>
                  <h3 className="font-bold text-sm text-slate-900">All Store Categories</h3>
                </div>
                <button
                  onClick={() => setIsCategoryDrawerOpen(false)}
                  className="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              <div className="mt-4 space-y-1 overflow-y-auto max-h-[70vh]">
                <button
                  onClick={() => handleNavClick('All Products')}
                  className={`w-full text-left px-3.5 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-between transition-colors cursor-pointer ${
                    activeCategory === 'All Products'
                      ? 'bg-emerald-50 text-[#00A651] font-bold'
                      : 'hover:bg-slate-100 text-slate-800'
                  }`}
                >
                  <span>All Categories</span>
                  <span className="text-[#00A651] text-xs">→</span>
                </button>

                {categories.map((cat) => (
                  <button
                    key={cat.id}
                    onClick={() => handleNavClick(cat.name)}
                    className={`w-full text-left px-3.5 py-2.5 rounded-xl font-semibold text-xs flex items-center justify-between transition-colors cursor-pointer ${
                      activeCategory === cat.name
                        ? 'bg-emerald-50 text-[#00A651] font-bold'
                        : 'hover:bg-slate-100 text-slate-800'
                    }`}
                  >
                    <span>{cat.name}</span>
                    <span className="text-[#FF6B00] text-xs">→</span>
                  </button>
                ))}
              </div>
            </div>

            <div className="pt-4 border-t border-slate-200 bg-slate-50 p-3 rounded-xl text-center text-xs text-slate-600">
              <span className="font-bold text-[#00A651] block">Cash on Delivery All Over Pakistan</span>
              <span className="text-[11px] text-slate-500">Pay cash upon parcel inspection at doorstep.</span>
            </div>
          </div>

          <div
            className="flex-1 cursor-pointer"
            onClick={() => setIsCategoryDrawerOpen(false)}
          />
        </div>
      )}
    </header>
  );
};
