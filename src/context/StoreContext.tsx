import React, { createContext, useContext, useState, useEffect } from 'react';
import {
  Product,
  CartItem,
  Order,
  OrderStatus,
  StoreSettings,
  CustomerUser,
  AdminUser,
  AdminCredentials,
  ProductReview,
  CategoryItem,
  HeroBanner,
} from '../types';
import {
  INITIAL_PRODUCTS,
  INITIAL_ORDERS,
  INITIAL_SETTINGS,
  INITIAL_REVIEWS,
  INITIAL_CATEGORIES,
  INITIAL_BANNERS,
  INITIAL_USERS,
} from '../data/initialData';

interface StoreContextType {
  products: Product[];
  orders: Order[];
  cart: CartItem[];
  settings: StoreSettings;
  reviews: ProductReview[];
  categories: CategoryItem[];
  banners: HeroBanner[];
  wishlist: string[]; // Product IDs
  users: CustomerUser[];
  activeCategory: string;
  searchQuery: string;
  isCartOpen: boolean;
  isCheckoutOpen: boolean;
  isWishlistOpen: boolean;
  isOrderTrackerOpen: boolean;
  selectedProductForDetail: Product | null;
  selectedProductPage: Product | null;
  lastPlacedOrder: Order | null;
  isAdminView: boolean;

  // Reviews
  addReview: (review: Omit<ProductReview, 'id' | 'date'>) => void;
  setSelectedProductPage: (product: Product | null) => void;

  // Customer Auth
  currentUser: CustomerUser | null;
  isAuthModalOpen: boolean;
  authModalMode: 'login' | 'signup';
  isMyOrdersOpen: boolean;
  setAuthModal: (open: boolean, mode?: 'login' | 'signup') => void;
  setIsMyOrdersOpen: (open: boolean) => void;
  customerLogin: (emailOrPhone: string, pass: string) => boolean;
  customerSignUp: (
    userData: { fullName: string; phone: string; email: string; city: string; address: string },
    pass: string
  ) => boolean;
  customerLogout: () => void;
  deleteUser: (userId: string) => void;

  // Admin Auth & Dynamic Credentials
  currentAdmin: AdminUser | null;
  adminCredentials: AdminCredentials;
  isAdminLoginOpen: boolean;
  setIsAdminLoginOpen: (open: boolean) => void;
  adminLogin: (user: string, pass: string) => boolean;
  updateAdminCredentials: (email: string, pass: string) => boolean;
  adminLogout: () => void;

  // State setters
  setActiveCategory: (cat: string) => void;
  setSearchQuery: (query: string) => void;
  setIsCartOpen: (open: boolean) => void;
  setIsCheckoutOpen: (open: boolean) => void;
  setIsWishlistOpen: (open: boolean) => void;
  setIsOrderTrackerOpen: (open: boolean) => void;
  setSelectedProductForDetail: (product: Product | null) => void;
  setLastPlacedOrder: (order: Order | null) => void;
  setIsAdminView: (isAdmin: boolean) => void;

  // Wishlist
  toggleWishlist: (productId: string) => void;
  isInWishlist: (productId: string) => boolean;

  // Cart operations
  addToCart: (product: Product, quantity?: number) => void;
  updateCartQuantity: (productId: string, quantity: number) => void;
  removeFromCart: (productId: string) => void;
  clearCart: () => void;
  cartTotal: number;
  cartCount: number;
  shippingFee: number;
  finalTotal: number;

  // Order operations
  placeOrder: (customerData: {
    customerName: string;
    phone: string;
    email?: string;
    city: string;
    address: string;
    orderNotes?: string;
    directItem?: { product: Product; quantity: number };
  }) => Order;
  updateOrderStatus: (
    orderId: string,
    status: OrderStatus,
    courier?: Order['trackingCourier'],
    trackingNum?: string
  ) => void;
  deleteOrder: (orderId: string) => void;

  // Product operations (Admin)
  addProduct: (newProduct: Omit<Product, 'id' | 'createdAt'>) => void;
  updateProduct: (id: string, updated: Partial<Product>) => void;
  deleteProduct: (id: string) => void;
  toggleProductStock: (id: string) => void;

  // Category operations (Admin)
  addCategory: (name: string, icon?: string) => void;
  updateCategory: (id: string, name: string, icon?: string) => void;
  deleteCategory: (id: string) => void;

  // Banner operations (Admin)
  addBanner: (banner: Omit<HeroBanner, 'id'>) => void;
  updateBanner: (id: string, banner: Partial<HeroBanner>) => void;
  deleteBanner: (id: string) => void;

  // Settings
  updateSettings: (newSettings: Partial<StoreSettings>) => void;
  resetAllData: () => void;
}

const StoreContext = createContext<StoreContextType | undefined>(undefined);

export const StoreProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // Products State (7-8 demo products)
  const [products, setProducts] = useState<Product[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_products');
      return saved ? JSON.parse(saved) : INITIAL_PRODUCTS;
    } catch {
      return INITIAL_PRODUCTS;
    }
  });

  // Categories State
  const [categories, setCategories] = useState<CategoryItem[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_categories');
      return saved ? JSON.parse(saved) : INITIAL_CATEGORIES;
    } catch {
      return INITIAL_CATEGORIES;
    }
  });

  // Hero Banners State
  const [banners, setBanners] = useState<HeroBanner[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_banners');
      return saved ? JSON.parse(saved) : INITIAL_BANNERS;
    } catch {
      return INITIAL_BANNERS;
    }
  });

  // Wishlist State (starts empty, no dummy count)
  const [wishlist, setWishlist] = useState<string[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_wishlist');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // Registered Customers List
  const [users, setUsers] = useState<CustomerUser[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_users');
      return saved ? JSON.parse(saved) : INITIAL_USERS;
    } catch {
      return INITIAL_USERS;
    }
  });

  // Orders State
  const [orders, setOrders] = useState<Order[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_orders');
      return saved ? JSON.parse(saved) : INITIAL_ORDERS;
    } catch {
      return INITIAL_ORDERS;
    }
  });

  // Cart State
  const [cart, setCart] = useState<CartItem[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_cart');
      return saved ? JSON.parse(saved) : [];
    } catch {
      return [];
    }
  });

  // Store Settings (TopBar, Logo, Delivery Charges, WhatsApp)
  const [settings, setSettings] = useState<StoreSettings>(() => {
    try {
      const saved = localStorage.getItem('pakstore_settings');
      return saved ? JSON.parse(saved) : INITIAL_SETTINGS;
    } catch {
      return INITIAL_SETTINGS;
    }
  });

  // Active Customer User Auth
  const [currentUser, setCurrentUser] = useState<CustomerUser | null>(() => {
    try {
      const saved = localStorage.getItem('pakstore_current_user');
      return saved ? JSON.parse(saved) : null;
    } catch {
      return null;
    }
  });

  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);
  const [authModalMode, setAuthModalMode] = useState<'login' | 'signup'>('login');
  const [isMyOrdersOpen, setIsMyOrdersOpen] = useState(false);
  const [isWishlistOpen, setIsWishlistOpen] = useState(false);

  // Dynamic Admin Credentials (Loaded from localStorage or initial default)
  const [adminCredentials, setAdminCredentials] = useState<AdminCredentials>(() => {
    try {
      const saved = localStorage.getItem('pakstore_admin_credentials');
      return saved ? JSON.parse(saved) : { email: 'admin@pakstore.com', password: 'admin123' };
    } catch {
      return { email: 'admin@pakstore.com', password: 'admin123' };
    }
  });

  const [currentAdmin, setCurrentAdmin] = useState<AdminUser | null>(() => {
    try {
      const saved = localStorage.getItem('pakstore_current_admin');
      return saved ? JSON.parse(saved) : null;
    } catch {
      return null;
    }
  });
  const [isAdminLoginOpen, setIsAdminLoginOpen] = useState(false);
  const [isAdminView, setIsAdminView] = useState<boolean>(false);

  const [activeCategory, setActiveCategory] = useState<string>('All Products');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [isCartOpen, setIsCartOpen] = useState<boolean>(false);
  const [isCheckoutOpen, setIsCheckoutOpen] = useState<boolean>(false);
  const [isOrderTrackerOpen, setIsOrderTrackerOpen] = useState<boolean>(false);

  // Reviews State
  const [reviews, setReviews] = useState<ProductReview[]>(() => {
    try {
      const saved = localStorage.getItem('pakstore_reviews');
      return saved ? JSON.parse(saved) : INITIAL_REVIEWS;
    } catch {
      return INITIAL_REVIEWS;
    }
  });

  const [selectedProductPage, setSelectedProductPage] = useState<Product | null>(null);
  const [selectedProductForDetail, setSelectedProductForDetail] = useState<Product | null>(null);
  const [lastPlacedOrder, setLastPlacedOrder] = useState<Order | null>(null);

  // Reviews Handler
  const addReview = (newRev: Omit<ProductReview, 'id' | 'date'>) => {
    const rev: ProductReview = {
      ...newRev,
      id: `REV-${Math.floor(1000 + Math.random() * 9000)}`,
      date: new Date().toISOString().split('T')[0],
    };
    setReviews((prev) => [rev, ...prev]);
  };

  // Sync to localStorage
  useEffect(() => {
    localStorage.setItem('pakstore_products', JSON.stringify(products));
  }, [products]);

  useEffect(() => {
    localStorage.setItem('pakstore_categories', JSON.stringify(categories));
  }, [categories]);

  useEffect(() => {
    localStorage.setItem('pakstore_banners', JSON.stringify(banners));
  }, [banners]);

  useEffect(() => {
    localStorage.setItem('pakstore_wishlist', JSON.stringify(wishlist));
  }, [wishlist]);

  useEffect(() => {
    localStorage.setItem('pakstore_users', JSON.stringify(users));
  }, [users]);

  useEffect(() => {
    localStorage.setItem('pakstore_orders', JSON.stringify(orders));
  }, [orders]);

  useEffect(() => {
    localStorage.setItem('pakstore_cart', JSON.stringify(cart));
  }, [cart]);

  useEffect(() => {
    localStorage.setItem('pakstore_settings', JSON.stringify(settings));
  }, [settings]);

  useEffect(() => {
    localStorage.setItem('pakstore_reviews', JSON.stringify(reviews));
  }, [reviews]);

  useEffect(() => {
    if (currentUser) {
      localStorage.setItem('pakstore_current_user', JSON.stringify(currentUser));
    } else {
      localStorage.removeItem('pakstore_current_user');
    }
  }, [currentUser]);

  useEffect(() => {
    if (currentAdmin) {
      localStorage.setItem('pakstore_current_admin', JSON.stringify(currentAdmin));
    } else {
      localStorage.removeItem('pakstore_current_admin');
    }
  }, [currentAdmin]);

  // Wishlist Actions
  const toggleWishlist = (productId: string) => {
    setWishlist((prev) =>
      prev.includes(productId) ? prev.filter((id) => id !== productId) : [...prev, productId]
    );
  };

  const isInWishlist = (productId: string): boolean => {
    return wishlist.includes(productId);
  };

  // Calculations: Free delivery has NO automatic rule, admin deliveryCharges is strictly used
  const cartTotal = cart.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
  const cartCount = cart.reduce((sum, item) => sum + item.quantity, 0);
  const shippingFee = cart.length > 0 ? (settings.deliveryCharges || 0) : 0;
  const finalTotal = cartTotal + shippingFee;

  // Customer Auth Methods
  const setAuthModal = (open: boolean, mode: 'login' | 'signup' = 'login') => {
    setAuthModalMode(mode);
    setIsAuthModalOpen(open);
  };

  const customerLogin = (emailOrPhone: string, pass: string): boolean => {
    const clean = emailOrPhone.trim().toLowerCase();
    // Check against existing users
    const matched = users.find(
      (u) => u.email.toLowerCase() === clean || u.phone.replace(/[^0-9]/g, '') === clean.replace(/[^0-9]/g, '')
    );
    if (matched) {
      setCurrentUser(matched);
      setIsAuthModalOpen(false);
      return true;
    }
    // Allow demo or new login
    if (pass.length >= 4) {
      const user: CustomerUser = {
        id: `CUST-${Math.floor(10000 + Math.random() * 90000)}`,
        fullName: clean.includes('@') ? clean.split('@')[0] : 'PakStore Customer',
        email: clean.includes('@') ? clean : 'customer@pakstore.com',
        phone: clean.startsWith('03') ? clean : '0300-1234567',
        city: 'Lahore',
        address: 'House #42, Street 8, Sector C, Lahore',
        createdAt: new Date().toISOString(),
      };
      setCurrentUser(user);
      setUsers((prev) => [user, ...prev]);
      setIsAuthModalOpen(false);
      return true;
    }
    return false;
  };

  const customerSignUp = (
    userData: { fullName: string; phone: string; email: string; city: string; address: string },
    _pass: string
  ): boolean => {
    const newUser: CustomerUser = {
      id: `CUST-${Math.floor(10000 + Math.random() * 90000)}`,
      ...userData,
      createdAt: new Date().toISOString(),
    };
    setCurrentUser(newUser);
    setUsers((prev) => [newUser, ...prev]);
    setIsAuthModalOpen(false);
    return true;
  };

  const customerLogout = () => {
    setCurrentUser(null);
  };

  const deleteUser = (userId: string) => {
    setUsers((prev) => prev.filter((u) => u.id !== userId));
  };

  // Admin Auth Methods: Strictly validates against dynamic credentials set in admin panel/database
  const adminLogin = (user: string, pass: string): boolean => {
    const cleanUser = user.trim().toLowerCase();
    const configuredEmail = adminCredentials.email.trim().toLowerCase();
    if (cleanUser === configuredEmail && pass === adminCredentials.password) {
      const admin: AdminUser = {
        username: cleanUser,
        role: 'SuperAdmin',
        lastLogin: new Date().toISOString(),
      };
      setCurrentAdmin(admin);
      setIsAdminLoginOpen(false);
      setIsAdminView(true);
      return true;
    }
    return false;
  };

  const updateAdminCredentials = (newEmail: string, newPass: string): boolean => {
    if (!newEmail.trim() || !newPass.trim()) return false;
    const updated: AdminCredentials = {
      email: newEmail.trim().toLowerCase(),
      password: newPass.trim(),
      updatedAt: new Date().toISOString(),
    };
    setAdminCredentials(updated);
    try {
      localStorage.setItem('pakstore_admin_credentials', JSON.stringify(updated));
    } catch {
      // Storage fallback
    }
    return true;
  };

  const adminLogout = () => {
    setCurrentAdmin(null);
    setIsAdminView(false);
  };

  // Cart operations
  const addToCart = (product: Product, quantity = 1) => {
    setCart((prevCart) => {
      const existing = prevCart.find((item) => item.product.id === product.id);
      if (existing) {
        return prevCart.map((item) =>
          item.product.id === product.id
            ? { ...item, quantity: item.quantity + quantity }
            : item
        );
      }
      return [...prevCart, { product, quantity }];
    });
    setIsCartOpen(true);
  };

  const updateCartQuantity = (productId: string, quantity: number) => {
    if (quantity <= 0) {
      removeFromCart(productId);
      return;
    }
    setCart((prevCart) =>
      prevCart.map((item) =>
        item.product.id === productId ? { ...item, quantity } : item
      )
    );
  };

  const removeFromCart = (productId: string) => {
    setCart((prevCart) => prevCart.filter((item) => item.product.id !== productId));
  };

  const clearCart = () => {
    setCart([]);
  };

  // Order Placement - strictly generates Order ID PK-XXXX
  const placeOrder = (customerData: {
    customerName: string;
    phone: string;
    email?: string;
    city: string;
    address: string;
    orderNotes?: string;
    directItem?: { product: Product; quantity: number };
  }): Order => {
    let itemsToOrder = cart.map((item) => ({
      productId: item.product.id,
      title: item.product.title,
      price: item.product.price,
      quantity: item.quantity,
      image: item.product.image,
    }));

    if (customerData.directItem) {
      itemsToOrder = [
        {
          productId: customerData.directItem.product.id,
          title: customerData.directItem.product.title,
          price: customerData.directItem.product.price,
          quantity: customerData.directItem.quantity,
          image: customerData.directItem.product.image,
        },
      ];
    }

    const calculatedSubtotal = itemsToOrder.reduce(
      (sum, item) => sum + item.price * item.quantity,
      0
    );
    // Strict admin delivery charges
    const calculatedShipping = settings.deliveryCharges || 0;
    const calculatedTotal = calculatedSubtotal + calculatedShipping;

    // Format: PK-XXXX (4-digit number)
    const randomNum = Math.floor(1000 + Math.random() * 9000);
    const orderId = `PK-${randomNum}`;

    const newOrder: Order = {
      id: orderId,
      customerName: customerData.customerName,
      phone: customerData.phone,
      email: customerData.email,
      city: customerData.city,
      address: customerData.address,
      orderNotes: customerData.orderNotes,
      paymentMethod: 'Cash on Delivery',
      items: itemsToOrder,
      subtotal: calculatedSubtotal,
      shippingFee: calculatedShipping,
      totalAmount: calculatedTotal,
      status: 'Pending',
      createdAt: new Date().toISOString(),
      trackingCourier: 'TCS',
    };

    setOrders((prev) => [newOrder, ...prev]);
    if (!customerData.directItem) {
      clearCart();
    }
    setLastPlacedOrder(newOrder);
    return newOrder;
  };

  const updateOrderStatus = (
    orderId: string,
    status: OrderStatus,
    courier?: Order['trackingCourier'],
    trackingNum?: string
  ) => {
    setOrders((prev) =>
      prev.map((order) => {
        if (order.id === orderId) {
          return {
            ...order,
            status,
            trackingCourier: courier || order.trackingCourier,
            trackingNumber: trackingNum !== undefined ? trackingNum : order.trackingNumber,
          };
        }
        return order;
      })
    );
  };

  const deleteOrder = (orderId: string) => {
    setOrders((prev) => prev.filter((order) => order.id !== orderId));
  };

  // Product Operations
  const addProduct = (newProduct: Omit<Product, 'id' | 'createdAt'>) => {
    const id = `prd-${Math.floor(200 + Math.random() * 800)}`;
    const product: Product = {
      ...newProduct,
      id,
      createdAt: new Date().toISOString(),
    };
    setProducts((prev) => [product, ...prev]);
  };

  const updateProduct = (id: string, updated: Partial<Product>) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === id ? { ...p, ...updated } : p))
    );
  };

  const deleteProduct = (id: string) => {
    setProducts((prev) => prev.filter((p) => p.id !== id));
  };

  const toggleProductStock = (id: string) => {
    setProducts((prev) =>
      prev.map((p) => (p.id === id ? { ...p, inStock: !p.inStock } : p))
    );
  };

  // Category Operations (Admin)
  const addCategory = (name: string, icon = 'Tag') => {
    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
    const newCat: CategoryItem = {
      id: `cat-${Date.now()}`,
      name,
      slug,
      icon,
    };
    setCategories((prev) => [...prev, newCat]);
  };

  const updateCategory = (id: string, name: string, icon?: string) => {
    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
    setCategories((prev) =>
      prev.map((c) => (c.id === id ? { ...c, name, slug, icon: icon || c.icon } : c))
    );
  };

  const deleteCategory = (id: string) => {
    setCategories((prev) => prev.filter((c) => c.id !== id));
  };

  // Banner Operations (Admin)
  const addBanner = (newBanner: Omit<HeroBanner, 'id'>) => {
    const banner: HeroBanner = {
      ...newBanner,
      id: `ban-${Date.now()}`,
    };
    setBanners((prev) => [banner, ...prev]);
  };

  const updateBanner = (id: string, updated: Partial<HeroBanner>) => {
    setBanners((prev) =>
      prev.map((b) => (b.id === id ? { ...b, ...updated } : b))
    );
  };

  const deleteBanner = (id: string) => {
    setBanners((prev) => prev.filter((b) => b.id !== id));
  };

  // Settings
  const updateSettings = (newSettings: Partial<StoreSettings>) => {
    setSettings((prev) => ({ ...prev, ...newSettings }));
  };

  const resetAllData = () => {
    setProducts(INITIAL_PRODUCTS);
    setCategories(INITIAL_CATEGORIES);
    setBanners(INITIAL_BANNERS);
    setUsers(INITIAL_USERS);
    setOrders(INITIAL_ORDERS);
    setSettings(INITIAL_SETTINGS);
    setCart([]);
    setWishlist([]);
    setCurrentUser(null);
    setCurrentAdmin(null);
    localStorage.removeItem('pakstore_products');
    localStorage.removeItem('pakstore_categories');
    localStorage.removeItem('pakstore_banners');
    localStorage.removeItem('pakstore_users');
    localStorage.removeItem('pakstore_wishlist');
    localStorage.removeItem('pakstore_orders');
    localStorage.removeItem('pakstore_settings');
    localStorage.removeItem('pakstore_cart');
    localStorage.removeItem('pakstore_current_user');
    localStorage.removeItem('pakstore_current_admin');
  };

  return (
    <StoreContext.Provider
      value={{
        products,
        orders,
        cart,
        settings,
        reviews,
        categories,
        banners,
        wishlist,
        users,
        selectedProductPage,
        setSelectedProductPage,
        addReview,
        activeCategory,
        searchQuery,
        isCartOpen,
        isCheckoutOpen,
        isWishlistOpen,
        isOrderTrackerOpen,
        selectedProductForDetail,
        lastPlacedOrder,
        isAdminView,

        currentUser,
        isAuthModalOpen,
        authModalMode,
        isMyOrdersOpen,
        setAuthModal,
        setIsMyOrdersOpen,
        customerLogin,
        customerSignUp,
        customerLogout,
        deleteUser,

        currentAdmin,
        adminCredentials,
        isAdminLoginOpen,
        setIsAdminLoginOpen,
        adminLogin,
        updateAdminCredentials,
        adminLogout,

        setActiveCategory,
        setSearchQuery,
        setIsCartOpen,
        setIsCheckoutOpen,
        setIsWishlistOpen,
        setIsOrderTrackerOpen,
        setSelectedProductForDetail,
        setLastPlacedOrder,
        setIsAdminView,

        toggleWishlist,
        isInWishlist,

        addToCart,
        updateCartQuantity,
        removeFromCart,
        clearCart,
        cartTotal,
        cartCount,
        shippingFee,
        finalTotal,
        placeOrder,
        updateOrderStatus,
        deleteOrder,

        addProduct,
        updateProduct,
        deleteProduct,
        toggleProductStock,

        addCategory,
        updateCategory,
        deleteCategory,

        addBanner,
        updateBanner,
        deleteBanner,

        updateSettings,
        resetAllData,
      }}
    >
      {children}
    </StoreContext.Provider>
  );
};

export const useStore = () => {
  const context = useContext(StoreContext);
  if (!context) {
    throw new Error('useStore must be used within a StoreProvider');
  }
  return context;
};
