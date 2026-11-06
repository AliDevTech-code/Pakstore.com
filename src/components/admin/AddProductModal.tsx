import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { Product } from '../../types';
import { X, Plus, Sparkles, Image as ImageIcon } from 'lucide-react';

interface AddProductModalProps {
  productToEdit?: Product | null;
  onClose: () => void;
}

export const AddProductModal: React.FC<AddProductModalProps> = ({
  productToEdit,
  onClose,
}) => {
  const { addProduct, updateProduct, categories } = useStore();

  const [title, setTitle] = useState(productToEdit?.title || '');
  const [category, setCategory] = useState(productToEdit?.category || 'Electronics');
  const [price, setPrice] = useState(productToEdit?.price?.toString() || '');
  const [originalPrice, setOriginalPrice] = useState(
    productToEdit?.originalPrice?.toString() || ''
  );
  const [stock, setStock] = useState(productToEdit?.stock?.toString() || '25');
  const [sku, setSku] = useState(productToEdit?.sku || `PAK-ITM-${Math.floor(100 + Math.random() * 900)}`);
  const [image, setImage] = useState(
    productToEdit?.image || '/src/assets/images/product_smartwatch_ultra_1793814621511.jpg'
  );
  const [badge, setBadge] = useState<Product['badge']>(productToEdit?.badge || 'New');
  const [description, setDescription] = useState(productToEdit?.description || '');
  const [featureInput, setFeatureInput] = useState('');
  const [features, setFeatures] = useState<string[]>(
    productToEdit?.features || [
      'Authentic premium build quality',
      '1 Year Pakstore official replacement warranty',
      'Fast 2-4 days courier delivery nationwide'
    ]
  );
  const [error, setError] = useState('');

  const PRESET_IMAGES = [
    { name: 'Smartwatch', url: '/src/assets/images/product_smartwatch_ultra_1793814621511.jpg' },
    { name: 'Wireless Earbuds', url: '/src/assets/images/product_wireless_earbuds_1793814621939.jpg' },
    { name: 'Leather Wallet', url: '/src/assets/images/product_leather_wallet_1793814623557.jpg' },
    { name: 'Oud Fragrance', url: '/src/assets/images/product_fragrance_oud_1793814622540.jpg' },
    { name: 'Lifestyle Gadgets', url: '/src/assets/images/hero_pakstore_banner_1793814622604.jpg' },
  ];

  const handleAddFeature = () => {
    if (featureInput.trim()) {
      setFeatures([...features, featureInput.trim()]);
      setFeatureInput('');
    }
  };

  const handleRemoveFeature = (index: number) => {
    setFeatures(features.filter((_, i) => i !== index));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    const parsedPrice = parseInt(price, 10);
    const parsedOriginalPrice = originalPrice ? parseInt(originalPrice, 10) : undefined;
    const parsedStock = parseInt(stock, 10) || 0;

    if (!title.trim()) {
      setError('Product title is required.');
      return;
    }

    if (isNaN(parsedPrice) || parsedPrice <= 0) {
      setError('Please enter a valid price in PKR.');
      return;
    }

    if (!image.trim()) {
      setError('Product image is required.');
      return;
    }

    if (productToEdit) {
      updateProduct(productToEdit.id, {
        title: title.trim(),
        category,
        price: parsedPrice,
        originalPrice: parsedOriginalPrice,
        stock: parsedStock,
        sku: sku.trim(),
        image: image.trim(),
        badge: badge || undefined,
        description: description.trim(),
        features,
        inStock: parsedStock > 0,
      });
    } else {
      addProduct({
        title: title.trim(),
        category,
        price: parsedPrice,
        originalPrice: parsedOriginalPrice,
        stock: parsedStock,
        sku: sku.trim(),
        image: image.trim(),
        badge: badge || undefined,
        description: description.trim() || 'High quality lifestyle product backed by Pakstore guarantee.',
        features,
        inStock: parsedStock > 0,
        rating: 5.0,
        reviewsCount: 1,
      });
    }

    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 relative my-8 animate-in fade-in duration-200">
        {/* Header */}
        <div className="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Sparkles className="w-4 h-4 text-emerald-400" />
            <h2 className="text-base font-bold tracking-tight">
              {productToEdit ? 'Edit Product' : 'Add New Product to Pakstore'}
            </h2>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-white rounded-lg transition-colors cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
          {error && (
            <div className="p-3 text-xs bg-rose-50 text-rose-700 border border-rose-200 rounded-lg">
              {error}
            </div>
          )}

          {/* Title */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Product Title *
            </label>
            <input
              type="text"
              required
              placeholder="e.g. Wireless Pro ANC Earbuds"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 focus:border-emerald-600 font-medium text-slate-900"
            />
          </div>

          {/* Category & SKU */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Category *
              </label>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] bg-white text-slate-900 font-medium"
              >
                {categories.map((c) => (
                  <option key={c.id} value={c.name}>
                    {c.name}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                SKU / Item Code
              </label>
              <input
                type="text"
                placeholder="e.g. PAK-AUD-09"
                value={sku}
                onChange={(e) => setSku(e.target.value)}
                className="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-emerald-600"
              />
            </div>
          </div>

          {/* Price, Original Price, Stock */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Selling Price (PKR) *
              </label>
              <div className="relative">
                <span className="absolute left-3 top-2 text-xs text-slate-400 font-mono">Rs.</span>
                <input
                  type="number"
                  required
                  placeholder="3499"
                  value={price}
                  onChange={(e) => setPrice(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg font-mono font-bold text-slate-900 focus:outline-emerald-600"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Original Cut Price (Optional)
              </label>
              <div className="relative">
                <span className="absolute left-3 top-2 text-xs text-slate-400 font-mono">Rs.</span>
                <input
                  type="number"
                  placeholder="4999"
                  value={originalPrice}
                  onChange={(e) => setOriginalPrice(e.target.value)}
                  className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg font-mono text-slate-500 focus:outline-emerald-600"
                />
              </div>
            </div>

            <div>
              <label className="block text-xs font-semibold text-slate-700 mb-1">
                Initial Stock Units *
              </label>
              <input
                type="number"
                required
                placeholder="30"
                value={stock}
                onChange={(e) => setStock(e.target.value)}
                className="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-emerald-600"
              />
            </div>
          </div>

          {/* Badge & Image Selector */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Badge Label
            </label>
            <select
              value={badge || ''}
              onChange={(e) => setBadge((e.target.value as any) || undefined)}
              className="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 bg-white text-slate-900"
            >
              <option value="">No Badge</option>
              <option value="Bestseller">Bestseller</option>
              <option value="Sale">Sale</option>
              <option value="New">New</option>
              <option value="Trending">Trending</option>
              <option value="Top Rated">Top Rated</option>
            </select>
          </div>

          {/* Product Image URL with quick preset buttons */}
          <div>
            <div className="flex items-center justify-between mb-1">
              <label className="text-xs font-semibold text-slate-700">
                Product Image URL *
              </label>
              <span className="text-[11px] text-slate-400">Choose preset or paste URL</span>
            </div>

            <div className="flex gap-2 mb-2">
              <input
                type="text"
                required
                placeholder="Enter image URL..."
                value={image}
                onChange={(e) => setImage(e.target.value)}
                className="flex-1 px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 font-mono text-slate-600"
              />
            </div>

            {/* Quick Presets */}
            <div className="flex flex-wrap gap-2 items-center text-xs">
              <span className="text-[11px] text-slate-500 font-medium">Quick Pick:</span>
              {PRESET_IMAGES.map((preset) => (
                <button
                  key={preset.name}
                  type="button"
                  onClick={() => setImage(preset.url)}
                  className={`text-[11px] px-2.5 py-1 rounded-md border cursor-pointer transition-colors ${
                    image === preset.url
                      ? 'bg-emerald-600 text-white border-emerald-600'
                      : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100'
                  }`}
                >
                  {preset.name}
                </button>
              ))}
            </div>
          </div>

          {/* Description */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Product Description
            </label>
            <textarea
              rows={3}
              placeholder="Explain product specifications, craftsmanship, and benefits..."
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="w-full px-3.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600 text-slate-800"
            />
          </div>

          {/* Bullet Features */}
          <div>
            <label className="block text-xs font-semibold text-slate-700 mb-1">
              Feature Bullet Points
            </label>
            <div className="flex gap-2 mb-2">
              <input
                type="text"
                placeholder="e.g. 1 Year Official Pakstore Replacement Warranty"
                value={featureInput}
                onChange={(e) => setFeatureInput(e.target.value)}
                onKeyDown={(e) => {
                  if (e.key === 'Enter') {
                    e.preventDefault();
                    handleAddFeature();
                  }
                }}
                className="flex-1 px-3 py-1.5 text-xs border border-slate-300 rounded-lg focus:outline-emerald-600"
              />
              <button
                type="button"
                onClick={handleAddFeature}
                className="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold hover:bg-slate-900 cursor-pointer"
              >
                + Add
              </button>
            </div>

            <div className="space-y-1">
              {features.map((feat, index) => (
                <div
                  key={index}
                  className="flex items-center justify-between text-xs bg-slate-50 px-3 py-1.5 rounded-md border border-slate-200 text-slate-700"
                >
                  <span>✓ {feat}</span>
                  <button
                    type="button"
                    onClick={() => handleRemoveFeature(index)}
                    className="text-slate-400 hover:text-rose-600 text-xs font-bold"
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Submit */}
          <div className="pt-3 border-t border-slate-200 flex justify-end gap-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-colors shadow-xs cursor-pointer"
            >
              {productToEdit ? 'Save Changes' : 'Publish Product to Store'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
