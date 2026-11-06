export interface ProductReview {
  id: string;
  productId: string;
  author: string;
  city: string;
  rating: number; // 1 to 5
  date: string;
  comment: string;
  verifiedBuyer: boolean;
}

export interface Product {
  id: string;
  title: string;
  category: string;
  price: number; // in PKR
  originalPrice?: number;
  stock: number;
  image: string;
  gallery?: string[];
  badge?: 'Bestseller' | 'Sale' | 'New' | 'Trending' | 'Top Rated';
  description: string;
  features: string[];
  sku: string;
  inStock: boolean;
  rating: number; // e.g. 4.9
  reviewsCount: number; // e.g. 128
  createdAt: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export interface OrderItem {
  productId: string;
  title: string;
  price: number;
  quantity: number;
  image: string;
}

export type OrderStatus = 'Pending' | 'Processing' | 'Shipped' | 'Delivered' | 'Cancelled';

export interface Order {
  id: string; // e.g. PK-8421
  customerName: string;
  phone: string;
  email?: string;
  city: string; // User-written city text
  address: string; // Detailed street address
  orderNotes?: string;
  paymentMethod: 'Cash on Delivery';
  items: OrderItem[];
  subtotal: number;
  shippingFee: number;
  totalAmount: number;
  status: OrderStatus;
  createdAt: string;
  trackingCourier?: string;
  trackingNumber?: string;
}

export interface HeroBanner {
  id: string;
  title: string;
  subtitle: string;
  image: string;
  category: string;
  badge: string;
  link?: string;
}

export interface CategoryItem {
  id: string;
  name: string;
  slug: string;
  icon?: string;
}

export interface StoreSettings {
  storeName: string;
  logoUrl?: string; // If set, displays custom uploaded logo; else dynamic SVG/Text logo
  topBarText: string;
  phone: string;
  whatsapp: string;
  email: string;
  deliveryCharges: number; // Admin decides exact delivery charges
  announcement: string;
}

export interface CustomerUser {
  id: string;
  fullName: string;
  phone: string;
  email: string;
  city: string;
  address: string;
  createdAt: string;
}

export interface AdminUser {
  username: string;
  role: 'SuperAdmin' | 'StoreManager';
  lastLogin: string;
}

export interface AdminCredentials {
  email: string;
  password: string;
  updatedAt?: string;
}
