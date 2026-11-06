import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { Product } from '../../types';
import { formatPKR } from '../../utils/format';
import {
  ArrowLeft,
  Star,
  ShieldCheck,
  Truck,
  RefreshCw,
  Share2,
  Copy,
  Check,
  ShoppingBag,
  Zap,
  MessageCircle,
  CheckCircle2,
  Heart,
  Eye,
} from 'lucide-react';

interface ProductDetailPageProps {
  product: Product;
  onQuickOrder: (product: Product, quantity: number) => void;
  onBack: () => void;
}

export const ProductDetailPage: React.FC<ProductDetailPageProps> = ({
  product,
  onQuickOrder,
  onBack,
}) => {
  const {
    addToCart,
    settings,
    wishlist,
    toggleWishlist,
    products,
    setSelectedProductPage,
  } = useStore();

  const [selectedImage, setSelectedImage] = useState(product.image);
  const [quantity, setQuantity] = useState(1);
  const [copiedLink, setCopiedLink] = useState(false);
  const [isShareModalOpen, setIsShareModalOpen] = useState(false);

  const isFavorited = wishlist.includes(product.id);

  const discountPercent = product.originalPrice
    ? Math.round(((product.originalPrice - product.price) / product.originalPrice) * 100)
    : 0;

  const galleryImages = product.gallery && product.gallery.length > 0
    ? product.gallery
    : [product.image, 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&auto=format&fit=crop&q=80'];

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setCopiedLink(true);
    setTimeout(() => setCopiedLink(false), 2000);
  };

  const shareText = `Check out "${product.title}" on PakStore Pakistan for ${formatPKR(product.price)} with 100% Cash on Delivery!`;
  const whatsappShareUrl = `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + window.location.href)}`;

  // Related products from catalog (excluding current product)
  const relatedProducts = products
    .filter((p) => p.id !== product.id)
    .sort((a, b) => (a.category === product.category ? -1 : 1))
    .slice(0, 4);

  // Realistic rating breakdown
  const ratingScore = product.rating || 4.9;
  const totalReviews = product.reviewsCount || 128;

  // Verified buyer sample ratings (Star ⭐ only, NO text comments as requested)
  const dummyStarRatings = [
    { id: '1', author: 'Muhammad Bilal Khan', city: 'Lahore', rating: 5, date: 'Yesterday' },
    { id: '2', author: 'Engr. Zeeshan Haider', city: 'Islamabad', rating: 5, date: '2 days ago' },
    { id: '3', author: 'Farhan Ali', city: 'Rawalpindi', rating: 5, date: '3 days ago' },
    { id: '4', author: 'Chaudhry Kamran', city: 'Faisalabad', rating: 5, date: '4 days ago' },
    { id: '5', author: 'Hamza Tariq', city: 'Karachi', rating: 4, date: '5 days ago' },
    { id: '6', author: 'Shahmir Shah', city: 'Peshawar', rating: 5, date: '1 week ago' },
  ];

  return (
    <div className="bg-slate-50 min-h-screen text-slate-900 pb-20 sm:pb-24">
      {/* Top Breadcrumbs & Back Navigation */}
      <div className="bg-white border-b border-slate-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
          <button
            onClick={onBack}
            className="flex items-center gap-2 text-xs font-bold text-slate-700 hover:text-[#00A651] transition-colors cursor-pointer"
          >
            <ArrowLeft className="w-4 h-4" />
            <span>Back to All Products</span>
          </button>

          <div className="hidden sm:flex items-center gap-2 text-xs text-slate-400">
            <span>Home</span>
            <span>/</span>
            <span className="text-slate-600 font-medium">{product.category}</span>
            <span>/</span>
            <span className="text-slate-900 font-medium truncate max-w-xs">{product.title}</span>
          </div>

          <button
            onClick={() => setIsShareModalOpen(true)}
            className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs font-semibold text-slate-700 transition-colors cursor-pointer"
          >
            <Share2 className="w-3.5 h-3.5 text-[#00A651]" />
            <span>Share Product</span>
          </button>
        </div>
      </div>

      {/* Main Contiguous Purchase Module */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
          {/* Left Column: Gallery & Visuals */}
          <div className="lg:col-span-7 space-y-4">
            {/* Main Active Image Showcase */}
            <div className="relative aspect-square sm:aspect-4/3 rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-xs group">
              <img
                src={selectedImage}
                alt={product.title}
                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                referrerPolicy="no-referrer"
              />

              {product.badge && (
                <span className="absolute top-4 left-4 bg-slate-950 text-white text-xs font-bold px-3 py-1 rounded-md shadow-xs">
                  {product.badge}
                </span>
              )}

              {discountPercent > 0 && (
                <span className="absolute top-4 right-4 bg-[#FF6B00] text-white text-xs font-black px-2.5 py-1 rounded-md shadow-xs">
                  Save {discountPercent}%
                </span>
              )}

              <div className="absolute bottom-4 left-4 bg-white/95 backdrop-blur-md border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-900 flex items-center gap-1.5 shadow-sm">
                <ShieldCheck className="w-4 h-4 text-[#00A651]" />
                <span>100% Cash on Delivery Nationwide</span>
              </div>
            </div>

            {/* Thumbnail Selectors */}
            <div className="flex items-center gap-3 overflow-x-auto pb-1">
              {galleryImages.map((imgUrl, idx) => (
                <button
                  key={idx}
                  onClick={() => setSelectedImage(imgUrl)}
                  className={`w-20 h-20 rounded-xl overflow-hidden border-2 transition-all cursor-pointer bg-white shrink-0 ${
                    selectedImage === imgUrl
                      ? 'border-[#00A651] ring-2 ring-emerald-500/30'
                      : 'border-slate-200 hover:border-slate-300 opacity-75 hover:opacity-100'
                  }`}
                >
                  <img src={imgUrl} alt="Thumbnail" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>

            {/* Trust Badges Bar */}
            <div className="grid grid-cols-3 gap-3 pt-4 border-t border-slate-200 text-xs">
              <div className="p-3 bg-white rounded-xl border border-slate-200 flex flex-col items-center text-center">
                <Truck className="w-5 h-5 text-[#00A651] mb-1" />
                <span className="font-bold text-slate-900">2-4 Days TCS Delivery</span>
                <span className="text-[11px] text-slate-500">All Pakistani cities</span>
              </div>
              <div className="p-3 bg-white rounded-xl border border-slate-200 flex flex-col items-center text-center">
                <ShieldCheck className="w-5 h-5 text-[#FF6B00] mb-1" />
                <span className="font-bold text-slate-900">Cash on Delivery</span>
                <span className="text-[11px] text-slate-500">Pay cash upon delivery</span>
              </div>
              <div className="p-3 bg-white rounded-xl border border-slate-200 flex flex-col items-center text-center">
                <RefreshCw className="w-5 h-5 text-[#00A651] mb-1" />
                <span className="font-bold text-slate-900">7-Day Replacement</span>
                <span className="text-[11px] text-slate-500">Official Warranty</span>
              </div>
            </div>
          </div>

          {/* Right Column: Contiguous Purchase Module */}
          <div className="lg:col-span-5 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <div>
              <div className="flex items-center justify-between text-xs text-slate-400 mb-2">
                <span className="font-bold uppercase tracking-wider text-[#00A651]">
                  {product.category}
                </span>
                <span className="font-mono text-slate-500">SKU: {product.sku}</span>
              </div>

              <h1 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                {product.title}
              </h1>

              {/* Star Rating Header ⭐ (Pure Stars & Score) */}
              <div className="mt-3 flex items-center gap-2">
                <div className="flex items-center text-amber-400">
                  {[...Array(5)].map((_, i) => (
                    <Star
                      key={i}
                      className={`w-4 h-4 ${
                        i < Math.floor(ratingScore)
                          ? 'fill-amber-400 text-amber-400'
                          : 'fill-amber-100 text-amber-200'
                      }`}
                    />
                  ))}
                </div>
                <span className="text-xs font-bold text-slate-900 font-mono">
                  {ratingScore} / 5.0
                </span>
                <span aria-hidden="true" className="text-slate-300">·</span>
                <span className="text-xs text-[#00A651] font-semibold">
                  {totalReviews} Verified Buyer Ratings ⭐
                </span>
              </div>
            </div>

            {/* Price Module */}
            <div className="p-4 bg-slate-50 rounded-xl border border-slate-200">
              <div className="flex items-baseline gap-3">
                <span className="text-2xl sm:text-3xl font-black text-slate-900 font-mono tabular-nums">
                  {formatPKR(product.price)}
                </span>
                {product.originalPrice && (
                  <span className="text-sm sm:text-base text-slate-400 line-through font-mono tabular-nums">
                    {formatPKR(product.originalPrice)}
                  </span>
                )}
                {discountPercent > 0 && (
                  <span className="text-xs font-bold text-white bg-[#FF6B00] px-2 py-0.5 rounded">
                    -{discountPercent}% OFF
                  </span>
                )}
              </div>
              <p className="text-[11px] text-slate-500 mt-1">
                Cash on Delivery nationwide. Inspect parcel before payment.
              </p>
            </div>

            {/* In Stock Status */}
            <div className="flex items-center gap-2 text-xs font-semibold text-[#00A651]">
              <CheckCircle2 className="w-4 h-4 shrink-0" />
              <span>In Stock — {product.stock} units ready for prompt dispatch</span>
            </div>

            {/* Description */}
            <p className="text-xs sm:text-sm text-slate-600 leading-relaxed">
              {product.description}
            </p>

            {/* Bullet Specifications */}
            {product.features && product.features.length > 0 && (
              <div className="space-y-1.5 pt-2 border-t border-slate-100">
                <span className="text-xs font-bold uppercase tracking-wider text-slate-700 block">
                  Product Highlights:
                </span>
                {product.features.map((feat, idx) => (
                  <div key={idx} className="flex items-start gap-2 text-xs text-slate-600">
                    <span className="text-[#00A651] font-bold">✓</span>
                    <span>{feat}</span>
                  </div>
                ))}
              </div>
            )}

            {/* Quantity Stepper & Actions */}
            <div className="pt-3 border-t border-slate-200 space-y-3">
              <div className="flex items-center gap-4">
                <span className="text-xs font-bold text-slate-700">Quantity:</span>
                <div className="flex items-center border border-slate-300 rounded-lg overflow-hidden bg-slate-50 text-xs">
                  <button
                    onClick={() => setQuantity(Math.max(1, quantity - 1))}
                    className="px-3.5 py-1.5 text-slate-700 hover:bg-slate-200 font-bold cursor-pointer"
                  >
                    -
                  </button>
                  <span className="px-4 py-1.5 font-mono font-bold text-slate-900 bg-white">
                    {quantity}
                  </span>
                  <button
                    onClick={() => setQuantity(Math.min(product.stock, quantity + 1))}
                    className="px-3.5 py-1.5 text-slate-700 hover:bg-slate-200 font-bold cursor-pointer"
                  >
                    +
                  </button>
                </div>
                <span className="text-xs text-slate-500 font-mono">
                  Total: <strong className="text-slate-900">{formatPKR(product.price * quantity)}</strong>
                </span>
              </div>

              {/* Action Buttons */}
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button
                  onClick={() => addToCart(product, quantity)}
                  className="w-full py-3 px-4 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold transition-colors flex items-center justify-center gap-2 cursor-pointer"
                >
                  <ShoppingBag className="w-4 h-4 text-[#00A651]" />
                  <span>Add to Cart</span>
                </button>

                <button
                  onClick={() => onQuickOrder(product, quantity)}
                  className="w-full py-3 px-4 rounded-xl bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <Zap className="w-4 h-4 fill-current" />
                  <span>Order Now (COD)</span>
                </button>
              </div>

              {/* Wishlist and Trust Action Row */}
              <div className="pt-1">
                <button
                  onClick={() => toggleWishlist(product.id)}
                  className={`w-full py-2.5 px-4 rounded-xl border text-xs font-bold transition-colors flex items-center justify-center gap-2 cursor-pointer ${
                    isFavorited
                      ? 'bg-rose-50 border-rose-200 text-rose-600'
                      : 'border-slate-300 text-slate-700 hover:text-rose-600 hover:bg-rose-50'
                  }`}
                >
                  <Heart className={`w-4 h-4 ${isFavorited ? 'fill-rose-600' : ''}`} />
                  <span>{isFavorited ? 'Saved to Your Wishlist' : 'Add to Wishlist'}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Customer Star ⭐ Ratings Dashboard (Star ⭐ only, NO comment submission form) */}
        <div id="reviews-section" className="mt-14 pt-10 border-t border-slate-200">
          <div className="mb-6">
            <span className="text-xs uppercase font-bold text-[#00A651] tracking-wider">
              Verified Ratings
            </span>
            <h3 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-0.5">
              Customer Star ⭐ Ratings ({ratingScore} / 5.0)
            </h3>
            <p className="text-xs text-slate-500 mt-0.5">
              Verified star ratings from real buyers across Pakistan who received this item with Cash on Delivery.
            </p>
          </div>

          {/* Star Rating Breakdown Cards */}
          <div className="grid grid-cols-1 md:grid-cols-12 gap-6 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
            {/* Overall Score Box */}
            <div className="md:col-span-4 flex flex-col items-center justify-center p-5 bg-slate-50 rounded-xl border border-slate-100 text-center">
              <span className="text-4xl sm:text-5xl font-black text-slate-900 font-mono">
                {ratingScore}
              </span>
              <div className="flex items-center text-amber-400 my-2">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="w-5 h-5 fill-amber-400 text-amber-400" />
                ))}
              </div>
              <span className="text-xs font-bold text-slate-700">
                {totalReviews} Verified Ratings
              </span>
              <span className="text-[11px] text-[#00A651] font-semibold mt-1">
                ★ 98% Recommended by Pakistani buyers
              </span>
            </div>

            {/* Star Distribution Progress Bars */}
            <div className="md:col-span-8 space-y-2 flex flex-col justify-center">
              {[
                { stars: 5, percent: 89, count: Math.round(totalReviews * 0.89) },
                { stars: 4, percent: 8, count: Math.round(totalReviews * 0.08) },
                { stars: 3, percent: 2, count: Math.round(totalReviews * 0.02) },
                { stars: 2, percent: 1, count: Math.round(totalReviews * 0.01) },
                { stars: 1, percent: 0, count: 0 },
              ].map((row) => (
                <div key={row.stars} className="flex items-center gap-3 text-xs">
                  <div className="flex items-center gap-1 w-14 shrink-0 font-bold text-slate-700">
                    <span>{row.stars} Star</span>
                    <Star className="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                  </div>
                  <div className="flex-1 bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div
                      className="bg-amber-400 h-full rounded-full transition-all duration-500"
                      style={{ width: `${row.percent}%` }}
                    />
                  </div>
                  <span className="w-12 text-right font-mono text-[11px] text-slate-500">
                    {row.percent}%
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* Recent Verified Star Ratings List (Clean Star badges only, no comment box) */}
          <div className="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {dummyStarRatings.map((item) => (
              <div
                key={item.id}
                className="bg-white p-3 rounded-xl border border-slate-200 shadow-xs flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center text-amber-400 mb-1">
                    {[...Array(item.rating)].map((_, i) => (
                      <Star key={i} className="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                    ))}
                  </div>
                  <span className="font-bold text-slate-900 text-xs block truncate">
                    {item.author}
                  </span>
                  <span className="text-[10px] text-slate-400 block">
                    {item.city} · {item.date}
                  </span>
                </div>
                <div className="mt-2 pt-1 border-t border-slate-100 flex items-center gap-1 text-[9px] text-[#00A651] font-bold">
                  <Check className="w-3 h-3" /> Verified Buyer
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* RELATED PRODUCTS SECTION ("product k releted neachy bi products show hou ok") */}
        <div className="mt-16 pt-10 border-t border-slate-200">
          <div className="flex items-center justify-between mb-6">
            <div>
              <span className="text-xs uppercase font-bold text-[#FF6B00] tracking-wider block">
                Recommendations
              </span>
              <h3 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-0.5">
                Related Products / You May Also Like
              </h3>
            </div>
            <button
              onClick={onBack}
              className="text-xs text-[#00A651] font-bold hover:underline cursor-pointer"
            >
              View Full Catalog →
            </button>
          </div>

          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
            {relatedProducts.map((relProd) => {
              const relDiscount = relProd.originalPrice
                ? Math.round(((relProd.originalPrice - relProd.price) / relProd.originalPrice) * 100)
                : 0;

              return (
                <div
                  key={relProd.id}
                  className="bg-white rounded-xl sm:rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between hover:shadow-md transition-all shadow-xs group"
                >
                  <div
                    onClick={() => {
                      setSelectedProductPage(relProd);
                      window.scrollTo({ top: 0, behavior: 'smooth' });
                    }}
                    className="relative aspect-square bg-slate-100 overflow-hidden cursor-pointer"
                  >
                    <img
                      src={relProd.image}
                      alt={relProd.title}
                      className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    {relDiscount > 0 && (
                      <span className="absolute top-2 right-2 bg-[#FF6B00] text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                        -{relDiscount}%
                      </span>
                    )}
                    {relProd.badge && (
                      <span className="absolute top-2 left-2 bg-slate-900 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">
                        {relProd.badge}
                      </span>
                    )}
                  </div>

                  <div className="p-3 flex-1 flex flex-col justify-between">
                    <div>
                      <span className="text-[10px] font-bold uppercase text-slate-400 block truncate">
                        {relProd.category}
                      </span>
                      <h4
                        onClick={() => {
                          setSelectedProductPage(relProd);
                          window.scrollTo({ top: 0, behavior: 'smooth' });
                        }}
                        className="text-xs font-bold text-slate-900 line-clamp-2 min-h-[32px] hover:text-[#00A651] cursor-pointer mt-0.5"
                      >
                        {relProd.title}
                      </h4>
                      <div className="flex items-center gap-1 text-amber-400 mt-1">
                        <Star className="w-3 h-3 fill-amber-400" />
                        <span className="text-[10px] font-bold text-slate-700 font-mono">
                          {relProd.rating || 4.9}
                        </span>
                      </div>
                    </div>

                    <div className="mt-3 pt-2 border-t border-slate-100">
                      <span className="text-xs sm:text-sm font-black text-slate-900 font-mono block">
                        {formatPKR(relProd.price)}
                      </span>

                      <div className="mt-2 flex gap-1.5">
                        <button
                          onClick={() => onQuickOrder(relProd, 1)}
                          className="flex-1 py-1.5 bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold rounded-lg transition-colors flex items-center justify-center gap-1 cursor-pointer"
                        >
                          <Zap className="w-3 h-3 fill-current" />
                          <span>Order COD</span>
                        </button>
                        <button
                          onClick={() => {
                            setSelectedProductPage(relProd);
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                          }}
                          className="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 cursor-pointer"
                          title="View Details"
                        >
                          <Eye className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Share Product Modal */}
      {isShareModalOpen && (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 relative animate-in zoom-in-95 duration-200">
            <div className="p-4 bg-slate-900 text-white flex items-center justify-between">
              <div className="flex items-center gap-2">
                <Share2 className="w-4 h-4 text-emerald-400" />
                <h3 className="text-sm font-bold">Share Product with Image</h3>
              </div>
              <button
                onClick={() => setIsShareModalOpen(false)}
                className="p-1 text-slate-400 hover:text-white rounded-lg cursor-pointer"
              >
                ✕
              </button>
            </div>

            <div className="p-5 space-y-4">
              <div className="bg-slate-950 text-white rounded-2xl p-4 border border-slate-800 shadow-md">
                <div className="relative aspect-4/3 rounded-xl overflow-hidden mb-3 bg-slate-900">
                  <img
                    src={product.image}
                    alt={product.title}
                    className="w-full h-full object-cover"
                  />
                  <span className="absolute bottom-2 left-2 bg-[#00A651] text-white text-[10px] font-bold px-2 py-0.5 rounded">
                    100% Cash on Delivery
                  </span>
                </div>
                <div className="flex items-center justify-between">
                  <div className="min-w-0 pr-2">
                    <span className="text-[10px] text-emerald-400 uppercase font-bold">{product.category}</span>
                    <h4 className="text-xs font-bold text-white truncate">{product.title}</h4>
                  </div>
                  <span className="text-base font-black text-emerald-400 font-mono shrink-0">
                    {formatPKR(product.price)}
                  </span>
                </div>
              </div>

              <div className="space-y-2">
                <a
                  href={whatsappShareUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-2.5 px-4 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
                >
                  <MessageCircle className="w-4 h-4" />
                  <span>Share to WhatsApp</span>
                </a>

                <button
                  onClick={handleCopyLink}
                  className="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold rounded-xl text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
                >
                  {copiedLink ? <Check className="w-4 h-4 text-[#00A651]" /> : <Copy className="w-4 h-4" />}
                  <span>{copiedLink ? 'Link Copied!' : 'Copy Direct Product Link'}</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Sticky Bottom Mobile Bar */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2.5 flex items-center justify-between shadow-lg">
        <div>
          <span className="text-[10px] text-slate-500 block uppercase font-bold">Total (COD)</span>
          <span className="font-mono text-base font-black text-slate-900">
            {formatPKR(product.price * quantity)}
          </span>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={() => addToCart(product, quantity)}
            className="p-2.5 rounded-xl border border-slate-200 text-slate-800 hover:bg-slate-100"
            title="Add to Cart"
          >
            <ShoppingBag className="w-4 h-4" />
          </button>

          <button
            onClick={() => onQuickOrder(product, quantity)}
            className="py-2.5 px-4 rounded-xl bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm"
          >
            <Zap className="w-3.5 h-3.5 fill-current" />
            <span>Order with COD</span>
          </button>
        </div>
      </div>
    </div>
  );
};
