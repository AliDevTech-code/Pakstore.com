import React, { useState, useMemo } from 'react';
import { useStore } from '../../context/StoreContext';
import { Product } from '../../types';
import { formatPKR } from '../../utils/format';
import { Plus, Search, Edit2, Trash2, CheckCircle2, XCircle, Package } from 'lucide-react';
import { AddProductModal } from './AddProductModal';

export const AdminProductsTab: React.FC = () => {
  const { products, deleteProduct, toggleProductStock, categories } = useStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('All');
  const [isAddModalOpen, setIsAddModalOpen] = useState(false);
  const [productToEdit, setProductToEdit] = useState<Product | null>(null);

  const filteredProducts = useMemo(() => {
    return products.filter((p) => {
      if (categoryFilter !== 'All' && p.category !== categoryFilter) {
        return false;
      }
      if (searchTerm.trim()) {
        const q = searchTerm.toLowerCase().trim();
        return (
          p.title.toLowerCase().includes(q) ||
          p.sku.toLowerCase().includes(q) ||
          p.category.toLowerCase().includes(q)
        );
      }
      return true;
    });
  }, [products, categoryFilter, searchTerm]);

  const handleEdit = (product: Product) => {
    setProductToEdit(product);
    setIsAddModalOpen(true);
  };

  const handleOpenAdd = () => {
    setProductToEdit(null);
    setIsAddModalOpen(true);
  };

  return (
    <div className="space-y-6">
      {/* Control Bar */}
      <div className="bg-white p-4 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div className="flex flex-1 items-center gap-3 max-w-lg">
          <div className="relative flex-1">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            <input
              type="text"
              placeholder="Search products by title, SKU, or category..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 focus:border-emerald-600"
            />
          </div>

          <select
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
            className="text-xs px-3 py-2 border border-slate-300 rounded-lg bg-white text-slate-800"
          >
            <option value="All">All Categories</option>
            {categories.map((c) => (
              <option key={c.id} value={c.name}>
                {c.name}
              </option>
            ))}
          </select>
        </div>

        {/* Add Product Button */}
        <button
          onClick={handleOpenAdd}
          className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
          <Plus className="w-4 h-4" />
          <span>Add New Product</span>
        </button>
      </div>

      {/* Products Table */}
      <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
              <tr>
                <th className="py-3 px-4">Product</th>
                <th className="py-3 px-4">Category / SKU</th>
                <th className="py-3 px-4">Price (PKR)</th>
                <th className="py-3 px-4">Stock</th>
                <th className="py-3 px-4">Status</th>
                <th className="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {filteredProducts.map((product) => (
                <tr key={product.id} className="hover:bg-slate-50/70 transition-colors">
                  {/* Product Title & Image */}
                  <td className="py-3 px-4">
                    <div className="flex items-center gap-3">
                      <img
                        src={product.image}
                        alt={product.title}
                        className="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0 bg-slate-100"
                      />
                      <div className="min-w-0 max-w-xs">
                        <span className="font-bold text-slate-900 block truncate">
                          {product.title}
                        </span>
                        {product.badge && (
                          <span className="inline-block mt-0.5 text-[10px] font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.2 rounded">
                            {product.badge}
                          </span>
                        )}
                      </div>
                    </div>
                  </td>

                  {/* Category & SKU */}
                  <td className="py-3 px-4">
                    <span className="font-medium text-slate-700 block">{product.category}</span>
                    <span className="font-mono text-[11px] text-slate-400">{product.sku}</span>
                  </td>

                  {/* Price */}
                  <td className="py-3 px-4">
                    <span className="font-mono font-bold text-slate-900 text-sm">
                      {formatPKR(product.price)}
                    </span>
                    {product.originalPrice && (
                      <span className="block text-[11px] text-slate-400 line-through font-mono">
                        {formatPKR(product.originalPrice)}
                      </span>
                    )}
                  </td>

                  {/* Stock */}
                  <td className="py-3 px-4">
                    <span className={`font-mono font-bold ${
                      product.stock <= 5 ? 'text-amber-600' : 'text-slate-800'
                    }`}>
                      {product.stock} units
                    </span>
                    {product.stock <= 5 && (
                      <span className="block text-[10px] text-amber-600 font-semibold">Low Stock</span>
                    )}
                  </td>

                  {/* Status Toggle */}
                  <td className="py-3 px-4">
                    <button
                      onClick={() => toggleProductStock(product.id)}
                      className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold cursor-pointer transition-colors ${
                        product.inStock
                          ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'
                          : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100'
                      }`}
                      title="Click to toggle stock status"
                    >
                      {product.inStock ? (
                        <>
                          <CheckCircle2 className="w-3.5 h-3.5" /> In Stock
                        </>
                      ) : (
                        <>
                          <XCircle className="w-3.5 h-3.5" /> Out of Stock
                        </>
                      )}
                    </button>
                  </td>

                  {/* Actions */}
                  <td className="py-3 px-4 text-right space-x-2">
                    <button
                      onClick={() => handleEdit(product)}
                      className="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                      title="Edit Product"
                    >
                      <Edit2 className="w-4 h-4" />
                    </button>
                    <button
                      onClick={() => {
                        if (confirm(`Delete "${product.title}" from store?`)) {
                          deleteProduct(product.id);
                        }
                      }}
                      className="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                      title="Delete Product"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Add / Edit Product Modal */}
      {isAddModalOpen && (
        <AddProductModal
          productToEdit={productToEdit}
          onClose={() => {
            setIsAddModalOpen(false);
            setProductToEdit(null);
          }}
        />
      )}
    </div>
  );
};
