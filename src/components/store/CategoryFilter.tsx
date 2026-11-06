import React from 'react';
import { useStore } from '../../context/StoreContext';

export const CategoryFilter: React.FC = () => {
  const { categories, activeCategory, setActiveCategory } = useStore();

  return (
    <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none w-full max-w-full min-w-0">
      <button
        onClick={() => setActiveCategory('All Products')}
        className={`px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
          activeCategory === 'All Products'
            ? 'bg-[#00A651] text-white shadow-xs'
            : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300'
        }`}
      >
        All Products
      </button>

      {categories.map((cat) => {
        const isActive = activeCategory === cat.name;
        return (
          <button
            key={cat.id}
            onClick={() => setActiveCategory(cat.name)}
            className={`px-3.5 py-1.5 text-xs font-semibold rounded-lg transition-colors whitespace-nowrap shrink-0 cursor-pointer ${
              isActive
                ? 'bg-[#00A651] text-white shadow-xs'
                : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-300'
            }`}
          >
            {cat.name}
          </button>
        );
      })}
    </div>
  );
};
