import React, { useState, useMemo } from 'react';
import { useStore } from '../../context/StoreContext';
import { ProductCard } from './ProductCard';
import { CategoryFilter } from './CategoryFilter';
import { Product } from '../../types';
import { ArrowUpDown, SearchX } from 'lucide-react';

interface ProductGridProps {
  onQuickOrder: (product: Product) => void;
}

export const ProductGrid: React.FC<ProductGridProps> = ({ onQuickOrder }) => {
  const { products, activeCategory, searchQuery, setSearchQuery, setActiveCategory } = useStore();
  const [sortBy, setSortBy] = useState<'featured' | 'price-low' | 'price-high' | 'newest'>('featured');

  const filteredProducts = useMemo(() => {
    let list = [...products];

    // Category filter
    if (activeCategory !== 'All Products') {
      list = list.filter((p) => p.category.toLowerCase() === activeCategory.toLowerCase());
    }

    // Search query filter
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase().trim();
      list = list.filter(
        (p) =>
          p.title.toLowerCase().includes(q) ||
          p.description.toLowerCase().includes(q) ||
          p.category.toLowerCase().includes(q) ||
          p.sku.toLowerCase().includes(q)
      );
    }

    // Sorting
    switch (sortBy) {
      case 'price-low':
        list.sort((a, b) => a.price - b.price);
        break;
      case 'price-high':
        list.sort((a, b) => b.price - a.price);
        break;
      case 'newest':
        list.sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime());
        break;
      case 'featured':
      default:
        list.sort((a, b) => (b.badge === 'Bestseller' ? 1 : 0) - (a.badge === 'Bestseller' ? 1 : 0));
        break;
    }

    return list;
  }, [products, activeCategory, searchQuery, sortBy]);

  return (
    <section id="catalog-section" className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 overflow-hidden">
      {/* Category Tabs & Controls Bar */}
      <div className="space-y-4 pb-5 border-b border-slate-200 w-full min-w-0 max-w-full">
        {/* Header Row: Title & Item Count on Left, Sort Selector on Right */}
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 className="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
              {activeCategory === 'All Products' ? 'Curated Collection' : activeCategory}
            </h2>
            <p className="text-xs text-slate-500 mt-0.5">
              {filteredProducts.length} items available · Guaranteed Cash on Delivery across Pakistan
            </p>
          </div>

          {/* Sort Selector */}
          <div className="relative shrink-0 flex items-center border border-slate-200 rounded-lg px-2.5 py-1.5 bg-white text-xs shadow-2xs self-start sm:self-auto">
            <ArrowUpDown className="w-3.5 h-3.5 text-slate-400 mr-1.5" />
            <select
              value={sortBy}
              onChange={(e) => setSortBy(e.target.value as any)}
              className="bg-transparent text-slate-700 font-medium focus:outline-none cursor-pointer"
            >
              <option value="featured">Featured First</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="newest">Newest Arrivals</option>
            </select>
          </div>
        </div>

        {/* Category Pills Strip (Constrained so it never pushes page width) */}
        <div className="w-full min-w-0 max-w-full overflow-hidden">
          <CategoryFilter />
        </div>
      </div>

      {/* Search status banner if active */}
      {searchQuery && (
        <div className="mt-4 p-3 bg-slate-100 rounded-lg flex items-center justify-between text-xs text-slate-700">
          <span>
            Search results for <strong className="text-slate-900">"{searchQuery}"</strong> ({filteredProducts.length} items)
          </span>
          <button
            onClick={() => setSearchQuery('')}
            className="text-emerald-700 font-semibold hover:underline cursor-pointer"
          >
            Clear Search
          </button>
        </div>
      )}

      {/* 2-Column Grid on Mobile, 3 on Tablet, 4 on Desktop (Balanced symmetric layout) */}
      {filteredProducts.length > 0 ? (
        <div className="mt-6 sm:mt-8 grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5 lg:gap-6">
          {filteredProducts.map((product) => (
            <ProductCard
              key={product.id}
              product={product}
              onQuickOrder={onQuickOrder}
            />
          ))}
        </div>
      ) : (
        <div className="py-16 text-center bg-white rounded-2xl border border-slate-200 mt-8">
          <SearchX className="w-10 h-10 text-slate-400 mx-auto mb-3" />
          <h3 className="text-base font-bold text-slate-800">No products found</h3>
          <p className="text-xs text-slate-500 max-w-sm mx-auto mt-1">
            Try adjusting your search terms or category selection to discover our items.
          </p>
          <button
            onClick={() => {
              setSearchQuery('');
              setActiveCategory('All Products');
            }}
            className="mt-4 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold cursor-pointer"
          >
            Reset Filters
          </button>
        </div>
      )}
    </section>
  );
};
