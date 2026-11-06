import { Product, Order, StoreSettings, ProductReview, CategoryItem, HeroBanner, CustomerUser } from '../types';

export const INITIAL_CATEGORIES: CategoryItem[] = [
  { id: 'cat-1', name: 'Mobiles', slug: 'mobiles', icon: 'Smartphone' },
  { id: 'cat-2', name: "Men's Fashion", slug: 'mens-fashion', icon: 'Shirt' },
  { id: 'cat-3', name: "Women's Fashion", slug: 'womens-fashion', icon: 'Sparkles' },
  { id: 'cat-4', name: 'Shoes', slug: 'shoes', icon: 'Footprints' },
  { id: 'cat-5', name: 'Watches', slug: 'watches', icon: 'Watch' },
  { id: 'cat-6', name: 'Electronics', slug: 'electronics', icon: 'Cpu' },
  { id: 'cat-7', name: 'Home & Kitchen', slug: 'home-kitchen', icon: 'Home' },
  { id: 'cat-8', name: 'Beauty', slug: 'beauty', icon: 'Heart' },
];

export const PAKISTAN_CITIES = [
  'Lahore',
  'Karachi',
  'Islamabad',
  'Rawalpindi',
  'Faisalabad',
  'Multan',
  'Peshawar',
  'Quetta',
  'Sialkot',
  'Gujranwala',
  'Hyderabad',
  'Other City',
];

export const INITIAL_BANNERS: HeroBanner[] = [
  {
    id: 'ban-1',
    title: 'PakStore Mega Online Deals',
    subtitle: 'Pay Cash at Doorstep Across Pakistan. 100% Inspected & Sealed Parcels.',
    image: 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1400&auto=format&fit=crop&q=80',
    category: 'Electronics',
    badge: 'Cash on Delivery',
    link: '#catalog-section',
  },
  {
    id: 'ban-2',
    title: 'Smart Tech & Titanium Wearables',
    subtitle: 'AMOLED Always-On Displays, Bluetooth Calling & Long Battery Life.',
    image: 'https://images.unsplash.com/photo-1508615039623-a25605d2b022?w=1400&auto=format&fit=crop&q=80',
    category: 'Watches',
    badge: 'Up to 35% Off',
    link: '#catalog-section',
  },
  {
    id: 'ban-3',
    title: 'Latest Fashion & Arabian Oud',
    subtitle: 'Hand-crafted Pakistani cowhide leather goods and concentrated attar.',
    image: 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1400&auto=format&fit=crop&q=80',
    category: "Men's Fashion",
    badge: 'New Season 2026',
    link: '#catalog-section',
  },
];

// Sirf 7-8 Demo Products (Exact 8 items with PKR, Unsplash images, discounts & ratings)
export const INITIAL_PRODUCTS: Product[] = [
  {
    id: 'prd-101',
    title: 'Apex Chrono Ultra Titanium Smartwatch (Calling & AMOLED)',
    category: 'Watches',
    price: 6499,
    originalPrice: 9999,
    stock: 25,
    image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80',
    badge: 'Bestseller',
    description: 'Precision aerospace titanium casing with crystal-clear AMOLED always-on display, 7-day battery life, heart-rate & SpO2 monitoring, and Bluetooth calling.',
    features: [
      'Aerospace grade titanium bezel',
      'AMOLED 1.43-inch HD Display',
      'IP68 Water & Dust Resistant',
      'Call Receiving & WhatsApp Notifications',
      '1 Year Official Pakstore Replacement Warranty'
    ],
    sku: 'PAK-WCH-01',
    inStock: true,
    rating: 4.9,
    reviewsCount: 142,
    createdAt: '2026-10-15T10:00:00Z',
  },
  {
    id: 'prd-102',
    title: 'TrueBass Pro Active Noise Cancelling Earbuds (32dB ANC)',
    category: 'Electronics',
    price: 4250,
    originalPrice: 5999,
    stock: 32,
    image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80',
    badge: 'Trending',
    description: 'Deep punchy bass tuned for high dynamic range audio. Features 32dB active noise cancellation, dual beamforming ENC microphones for clear calls, and 36-hour playback.',
    features: [
      '32dB Hybrid Active Noise Cancellation',
      '36 Hours Total Battery with USB-C Fast Charge',
      'Quad ENC Microphones for crystal clear Pakistani road calls',
      'Low Latency Gaming & Video Mode'
    ],
    sku: 'PAK-AUD-02',
    inStock: true,
    rating: 4.8,
    reviewsCount: 96,
    createdAt: '2026-10-18T12:00:00Z',
  },
  {
    id: 'prd-103',
    title: 'Royal Hunter Full-Grain Cowhide Leather Wallet (RFID Shield)',
    category: "Men's Fashion",
    price: 2899,
    originalPrice: 3800,
    stock: 20,
    image: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=800&auto=format&fit=crop&q=80',
    badge: 'Top Rated',
    description: 'Handcrafted from 100% genuine Pakistani full-grain cowhide leather. Develops a rich organic patina over time. Equipped with RFID blocking shield and 8 card slots.',
    features: [
      '100% Genuine Full-Grain Pakistani Leather',
      'RFID Anti-theft Blocking Protection',
      'Dual Cash Currency Compartments (fits Rs. 5000 notes easily)',
      'Packed in luxury matte gift presentation box'
    ],
    sku: 'PAK-LTH-03',
    inStock: true,
    rating: 4.9,
    reviewsCount: 184,
    createdAt: '2026-10-20T09:30:00Z',
  },
  {
    id: 'prd-104',
    title: 'Oud Al-Malik Luxury Arabian Eau De Parfum (100ml Concentrated)',
    category: 'Beauty',
    price: 5499,
    originalPrice: 7500,
    stock: 14,
    image: 'https://images.unsplash.com/photo-1594035910387-fea47794261f?w=800&auto=format&fit=crop&q=80',
    badge: 'Sale',
    description: 'An opulent Oriental fragrance opening with spicy saffron and royal amber, sinking into deep Cambodian agarwood (oud) and velvet rose. 14+ hours projection.',
    features: [
      'Concentrated Eau De Parfum (25% Oil Concentration)',
      '14+ Hours Projection in Pakistani Weather',
      'Notes: Royal Saffron, Ambergris, Cambodian Oud, Taif Rose',
      '100% Non-Alcoholic Organic Base Option'
    ],
    sku: 'PAK-FRG-04',
    inStock: true,
    rating: 5.0,
    reviewsCount: 78,
    createdAt: '2026-10-22T14:15:00Z',
  },
  {
    id: 'prd-105',
    title: 'AirFlex Ultra Breathable Running Sneakers (Lightweight Sports)',
    category: 'Shoes',
    price: 3850,
    originalPrice: 5500,
    stock: 28,
    image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800&auto=format&fit=crop&q=80',
    badge: 'New',
    description: 'Ultra-lightweight mesh upper with cushioned shock-absorbing air sole. Engineered for everyday walking, gym, and Pakistani road conditions.',
    features: [
      'Breathable engineered micro-mesh fabric',
      'Anti-slip high-grip rubber outsole',
      'Memory foam pressure relief insole',
      'Available in EU Sizes 40 to 45'
    ],
    sku: 'PAK-SHS-05',
    inStock: true,
    rating: 4.7,
    reviewsCount: 65,
    createdAt: '2026-10-24T11:00:00Z',
  },
  {
    id: 'prd-106',
    title: 'Galaxy Ultra 5G Dual SIM Mobile Phone (256GB / 12GB RAM)',
    category: 'Mobiles',
    price: 89999,
    originalPrice: 105000,
    stock: 12,
    image: 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=800&auto=format&fit=crop&q=80',
    badge: 'Bestseller',
    description: 'Flagship 120Hz Dynamic AMOLED display, 200MP camera sensor, Snapdragon high-speed octa-core processor with official PTA approval.',
    features: [
      'Official PTA Approved (Dual SIM)',
      '120Hz Quad HD+ Dynamic Curved Screen',
      '5000mAh Battery with 45W Fast Charging',
      '1 Year Brand Official Warranty'
    ],
    sku: 'PAK-MOB-06',
    inStock: true,
    rating: 4.9,
    reviewsCount: 112,
    createdAt: '2026-10-26T16:00:00Z',
  },
  {
    id: 'prd-107',
    title: 'Heritage Pure Lawn Stitched 3-Piece Designer Suit (Embroidered)',
    category: "Women's Fashion",
    price: 4999,
    originalPrice: 7200,
    stock: 22,
    image: 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=800&auto=format&fit=crop&q=80',
    badge: 'Trending',
    description: 'Fine Pakistani lawn cotton with intricate chikankari neck embroidery, digital printed pure chiffon dupatta, and dyed cambric trousers.',
    features: [
      '100% Breathable Fine Lawn Cotton',
      'Heavy Organza Thread Embroidery',
      'Chiffon Digital Print Dupatta',
      'Stitched Ready-to-Wear (Small, Medium, Large)'
    ],
    sku: 'PAK-WMN-07',
    inStock: true,
    rating: 4.8,
    reviewsCount: 89,
    createdAt: '2026-10-28T14:00:00Z',
  },
  {
    id: 'prd-108',
    title: 'Smart Touch Electric Rapid Kettle & Thermos (1.8L Stainless Steel)',
    category: 'Home & Kitchen',
    price: 3199,
    originalPrice: 4500,
    stock: 35,
    image: 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=800&auto=format&fit=crop&q=80',
    badge: 'Sale',
    description: 'Boil water for tea or coffee in under 90 seconds. Double-wall insulated food grade 304 stainless steel keeps hot for 6 hours with automatic dry-boil safety shutoff.',
    features: [
      '1.8 Litre Large Family Capacity',
      '1500W Fast Boil Concealed Heating Element',
      'Auto Cut-Off & Boil-Dry Protection',
      'Cool-touch outer body preventing burns'
    ],
    sku: 'PAK-KTN-08',
    inStock: true,
    rating: 4.8,
    reviewsCount: 54,
    createdAt: '2026-10-30T09:00:00Z',
  }
];

export const INITIAL_SETTINGS: StoreSettings = {
  storeName: 'PakStore',
  topBarText: 'Cash on Delivery All Over Pakistan',
  logoUrl: '', // If empty, header renders Pak (Green #00A651) + Store (Orange #FF6B00)
  phone: '0300-1234567',
  whatsapp: '923001234567',
  email: 'support@pakstore.com',
  deliveryCharges: 199, // Admin controlled delivery charges
  announcement: 'Cash on Delivery All Over Pakistan',
};

export const INITIAL_ORDERS: Order[] = [
  {
    id: 'PK-7821',
    customerName: 'Muhammad Bilal Khan',
    phone: '0300-4567891',
    email: 'bilal.khan@gmail.com',
    city: 'Lahore',
    address: 'House #42-B, Sector C, Commercial Area, Bahria Town',
    orderNotes: 'Call before arriving please',
    paymentMethod: 'Cash on Delivery',
    items: [
      {
        productId: 'prd-101',
        title: 'Apex Chrono Ultra Titanium Smartwatch (Calling & AMOLED)',
        price: 6499,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80'
      }
    ],
    subtotal: 6499,
    shippingFee: 199,
    totalAmount: 6698,
    status: 'Pending',
    createdAt: '2026-11-04T08:15:00Z',
    trackingCourier: 'TCS',
  },
  {
    id: 'PK-7818',
    customerName: 'Ayesha Siddiqui',
    phone: '0321-9876543',
    city: 'Karachi',
    address: 'Flat 402, Al-Razi Heights, Block 13-D, Gulshan-e-Iqbal',
    paymentMethod: 'Cash on Delivery',
    items: [
      {
        productId: 'prd-104',
        title: 'Oud Al-Malik Luxury Arabian Eau De Parfum (100ml Concentrated)',
        price: 5499,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1594035910387-fea47794261f?w=800&auto=format&fit=crop&q=80'
      }
    ],
    subtotal: 5499,
    shippingFee: 199,
    totalAmount: 5698,
    status: 'Processing',
    createdAt: '2026-11-03T18:40:00Z',
    trackingCourier: 'Leopards',
  },
  {
    id: 'PK-7790',
    customerName: 'Usman Tariq',
    phone: '0333-5551234',
    city: 'Islamabad',
    address: 'Street 14, House #29, Sector F-10/2',
    paymentMethod: 'Cash on Delivery',
    items: [
      {
        productId: 'prd-102',
        title: 'TrueBass Pro Active Noise Cancelling Earbuds (32dB ANC)',
        price: 4250,
        quantity: 1,
        image: 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80'
      }
    ],
    subtotal: 4250,
    shippingFee: 199,
    totalAmount: 4449,
    status: 'Shipped',
    createdAt: '2026-11-02T11:20:00Z',
    trackingCourier: 'Trax',
    trackingNumber: 'TRX-948102'
  }
];

export const INITIAL_USERS: CustomerUser[] = [
  {
    id: 'usr-1',
    fullName: 'Muhammad Bilal Khan',
    phone: '0300-4567891',
    email: 'bilal@pakstore.com',
    city: 'Lahore',
    address: 'House #42-B, Sector C, Commercial Area, Bahria Town',
    createdAt: '2026-10-10T12:00:00Z',
  },
  {
    id: 'usr-2',
    fullName: 'Ayesha Siddiqui',
    phone: '0321-9876543',
    email: 'ayesha@pakstore.com',
    city: 'Karachi',
    address: 'Flat 402, Al-Razi Heights, Block 13-D, Gulshan-e-Iqbal',
    createdAt: '2026-10-15T15:30:00Z',
  },
  {
    id: 'usr-3',
    fullName: 'Usman Tariq',
    phone: '0333-5551234',
    email: 'usman@pakstore.com',
    city: 'Islamabad',
    address: 'Street 14, House #29, Sector F-10/2',
    createdAt: '2026-10-22T09:15:00Z',
  }
];

export const INITIAL_REVIEWS: ProductReview[] = [
  {
    id: 'REV-101',
    productId: 'prd-101',
    author: 'Muhammad Bilal Khan',
    city: 'Lahore',
    rating: 5,
    date: '2026-11-01',
    comment: 'Bohot zabardast watch hai! Amoled screen bohot bright hai dhoop me bi clearly nazar aati hai. TCS rider ne delivery se pehle call ki aur meiny parcel check kar k cash diya. 100% recommended!',
    verifiedBuyer: true
  },
  {
    id: 'REV-102',
    productId: 'prd-101',
    author: 'Engr. Zeeshan Haider',
    city: 'Islamabad',
    rating: 5,
    date: '2026-10-28',
    comment: 'Battery timing 6-7 din chal rahi hai. Calling speaker aur mic ka sound clear hai. Titanium bezel looks premium.',
    verifiedBuyer: true
  },
  {
    id: 'REV-201',
    productId: 'prd-102',
    author: 'Farhan Ali',
    city: 'Rawalpindi',
    rating: 5,
    date: '2026-11-02',
    comment: 'ANC noise cancellation feature road noise ko completely block kar deta hai. Bass heavy hai aur ear fit comfortable hai.',
    verifiedBuyer: true
  },
  {
    id: 'REV-301',
    productId: 'prd-103',
    author: 'Chaudhry Kamran',
    city: 'Faisalabad',
    rating: 5,
    date: '2026-11-03',
    comment: 'Asli chamray (Genuine cowhide leather) ki khushbu aati hai. Stitching perfect hai aur Rs. 5,000 k notes bina moray aaram se aatay hain.',
    verifiedBuyer: true
  },
  {
    id: 'REV-401',
    productId: 'prd-104',
    author: 'Shahmir Shah',
    city: 'Peshawar',
    rating: 5,
    date: '2026-11-02',
    comment: 'Royal Oud fragrance! Kapron par aglay din tak mehak rehti hai. Office aur juma namaz k liye best attar fragrance.',
    verifiedBuyer: true
  }
];
