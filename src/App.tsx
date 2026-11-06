import React, { useState } from 'react';
import { StoreProvider, useStore } from './context/StoreContext';
import { AnnouncementBar } from './components/common/AnnouncementBar';
import { Navbar } from './components/common/Navbar';
import { MobileBottomNav } from './components/common/MobileBottomNav';
import { Hero } from './components/store/Hero';
import { ProductGrid } from './components/store/ProductGrid';
import { Footer } from './components/common/Footer';
import { CartDrawer } from './components/store/CartDrawer';
import { WishlistDrawer } from './components/store/WishlistDrawer';
import { CheckoutModal } from './components/store/CheckoutModal';
import { ProductDetailModal } from './components/store/ProductDetailModal';
import { ProductDetailPage } from './components/store/ProductDetailPage';
import { OrderConfirmationModal } from './components/store/OrderConfirmationModal';
import { OrderTrackerModal } from './components/store/OrderTrackerModal';
import { CustomerAuthModal } from './components/auth/CustomerAuthModal';
import { CustomerOrdersModal } from './components/auth/CustomerOrdersModal';
import { AdminLoginModal } from './components/auth/AdminLoginModal';
import { AdminDashboard } from './components/admin/AdminDashboard';
import { Product } from './types';

const StoreContent: React.FC = () => {
  const {
    isAdminView,
    currentAdmin,
    isCheckoutOpen,
    setIsCheckoutOpen,
    lastPlacedOrder,
    setLastPlacedOrder,
    selectedProductPage,
    setSelectedProductPage,
  } = useStore();

  const [directCheckoutItem, setDirectCheckoutItem] = useState<{
    product: Product;
    quantity: number;
  } | null>(null);

  const handleQuickOrder = (product: Product, quantity = 1) => {
    setDirectCheckoutItem({ product, quantity });
    setIsCheckoutOpen(true);
  };

  const handleCartCheckout = () => {
    setDirectCheckoutItem(null);
    setIsCheckoutOpen(true);
  };

  const handleCheckoutClose = () => {
    setIsCheckoutOpen(false);
    setDirectCheckoutItem(null);
  };

  const handleCheckoutSuccess = () => {
    setIsCheckoutOpen(false);
    setDirectCheckoutItem(null);
  };

  // If in Admin Panel View and authenticated as Admin, render Admin Dashboard
  if (isAdminView && currentAdmin) {
    return <AdminDashboard />;
  }

  // Customer Storefront
  return (
    <div className="min-h-screen flex flex-col bg-slate-50 text-slate-900 selection:bg-[#00A651] selection:text-white pb-14 md:pb-0">
      <AnnouncementBar />
      <Navbar />

      {selectedProductPage ? (
        <ProductDetailPage
          product={selectedProductPage}
          onQuickOrder={handleQuickOrder}
          onBack={() => setSelectedProductPage(null)}
        />
      ) : (
        <main className="flex-1">
          <Hero onQuickOrder={handleQuickOrder} />
          <ProductGrid onQuickOrder={handleQuickOrder} />
        </main>
      )}

      <Footer />

      {/* Mobile Bottom Navigation (Daraz Style) */}
      <MobileBottomNav />

      {/* Slide-out Cart Drawer */}
      <CartDrawer onProceedToCheckout={handleCartCheckout} />

      {/* Slide-out Wishlist Drawer */}
      <WishlistDrawer onQuickOrder={handleQuickOrder} />

      {/* Cash on Delivery Checkout Modal */}
      {isCheckoutOpen && (
        <CheckoutModal
          directProduct={directCheckoutItem}
          onClose={handleCheckoutClose}
          onSuccess={handleCheckoutSuccess}
        />
      )}

      {/* Product Detail Modal (PDP) */}
      <ProductDetailModal onQuickOrder={handleQuickOrder} />

      {/* Order Confirmation Screen */}
      {lastPlacedOrder && (
        <OrderConfirmationModal onClose={() => setLastPlacedOrder(null)} />
      )}

      {/* Order Tracker Modal */}
      <OrderTrackerModal />

      {/* Customer User Login & Sign Up Modal */}
      <CustomerAuthModal />

      {/* Customer "My Orders" History Modal */}
      <CustomerOrdersModal />

      {/* Separate Dedicated Admin Portal Login */}
      <AdminLoginModal />
    </div>
  );
};

export default function App() {
  return (
    <StoreProvider>
      <StoreContent />
    </StoreProvider>
  );
}
