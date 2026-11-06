import React from 'react';
import { useStore } from '../../context/StoreContext';
import { Home, LayoutGrid, PackageCheck, ShoppingBag, User } from 'lucide-react';

export const MobileBottomNav: React.FC = () => {
  const {
    cartCount,
    setIsCartOpen,
    currentUser,
    setAuthModal,
    setIsMyOrdersOpen,
    setSelectedProductPage,
    setIsOrderTrackerOpen,
  } = useStore();

  const handleHomeClick = () => {
    setSelectedProductPage(null);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const handleCategoriesClick = () => {
    setSelectedProductPage(null);
    const el = document.getElementById('catalog-section');
    if (el) {
      el.scrollIntoView({ behavior: 'smooth' });
    }
  };

  const handleTrackClick = () => {
    setIsOrderTrackerOpen(true);
  };

  const handleAccountClick = () => {
    if (currentUser) {
      setIsMyOrdersOpen(true);
    } else {
      setAuthModal(true, 'login');
    }
  };

  return (
    <div className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-2 py-1.5 shadow-lg">
      <div className="grid grid-cols-5 gap-1">
        {/* Home */}
        <button
          onClick={handleHomeClick}
          className="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors cursor-pointer"
        >
          <Home className="w-5 h-5" />
          <span className="text-[10px] font-semibold mt-0.5">Home</span>
        </button>

        {/* Categories */}
        <button
          onClick={handleCategoriesClick}
          className="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors cursor-pointer"
        >
          <LayoutGrid className="w-5 h-5" />
          <span className="text-[10px] font-semibold mt-0.5">Categories</span>
        </button>

        {/* Track Order */}
        <button
          onClick={handleTrackClick}
          className="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors cursor-pointer"
          title="Track Order Status"
        >
          <PackageCheck className="w-5 h-5" />
          <span className="text-[10px] font-semibold mt-0.5">Track</span>
        </button>

        {/* Cart */}
        <button
          onClick={() => setIsCartOpen(true)}
          className="relative flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors cursor-pointer"
        >
          <div className="relative">
            <ShoppingBag className="w-5 h-5 text-[#00A651]" />
            {cartCount > 0 && (
              <span className="absolute -top-1.5 -right-2 bg-[#FF6B00] text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center">
                {cartCount}
              </span>
            )}
          </div>
          <span className="text-[10px] font-semibold mt-0.5 text-[#00A651]">Cart</span>
        </button>

        {/* Account */}
        <button
          onClick={handleAccountClick}
          className="flex flex-col items-center justify-center py-1 text-slate-600 hover:text-[#00A651] transition-colors cursor-pointer"
        >
          <User className="w-5 h-5" />
          <span className="text-[10px] font-semibold mt-0.5 truncate max-w-[50px]">
            {currentUser ? currentUser.fullName.split(' ')[0] : 'Account'}
          </span>
        </button>
      </div>
    </div>
  );
};
