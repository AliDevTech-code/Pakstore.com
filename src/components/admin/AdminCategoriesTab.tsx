import React, { useState } from 'react';
import { useStore } from '../../context/StoreContext';
import { Plus, Trash2, Edit2, Check, X, Tag } from 'lucide-react';
import { CategoryItem } from '../../types';

export const AdminCategoriesTab: React.FC = () => {
  const { categories, addCategory, updateCategory, deleteCategory } = useStore();

  const [newCatName, setNewCatName] = useState('');
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editingName, setEditingName] = useState('');

  const handleAdd = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newCatName.trim()) return;
    addCategory(newCatName.trim());
    setNewCatName('');
  };

  const startEdit = (cat: CategoryItem) => {
    setEditingId(cat.id);
    setEditingName(cat.name);
  };

  const handleSaveEdit = (id: string) => {
    if (!editingName.trim()) return;
    updateCategory(id, editingName.trim());
    setEditingId(null);
  };

  return (
    <div className="space-y-6 max-w-4xl">
      <div className="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-2">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-[#00A651] flex items-center gap-1.5">
              <Tag className="w-4 h-4" /> Category Management
            </span>
            <h2 className="text-xl font-bold text-slate-900 mt-1">Manage Product Categories</h2>
            <p className="text-xs text-slate-500 mt-0.5">
              Categories add, edit ya delete karein. Website k navigation aur filters par foran update hoga.
            </p>
          </div>
          <span className="text-xs font-mono font-bold bg-emerald-50 text-[#00A651] px-2.5 py-1 rounded-lg border border-emerald-200 self-start sm:self-auto">
            {categories.length} Categories
          </span>
        </div>

        {/* Add Category Form */}
        <form onSubmit={handleAdd} className="pt-5 flex gap-2">
          <input
            type="text"
            required
            placeholder="New Category Name (e.g. Mobiles, Watches, Shoes...)"
            value={newCatName}
            onChange={(e) => setNewCatName(e.target.value)}
            className="flex-1 px-3.5 py-2 text-xs border border-slate-300 rounded-xl focus:outline-[#00A651] bg-white text-slate-900"
          />
          <button
            type="submit"
            className="px-4 py-2 bg-[#00A651] hover:bg-[#008f45] text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-xs transition-colors"
          >
            <Plus className="w-4 h-4" />
            <span>Add Category</span>
          </button>
        </form>

        {/* Categories Table / List */}
        <div className="mt-6 border border-slate-200 rounded-xl overflow-hidden">
          <table className="w-full text-left text-xs">
            <thead className="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
              <tr>
                <th className="py-3 px-4">Category Name</th>
                <th className="py-3 px-4">Slug</th>
                <th className="py-3 px-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {categories.map((cat) => (
                <tr key={cat.id} className="hover:bg-slate-50/80 transition-colors">
                  <td className="py-3 px-4">
                    {editingId === cat.id ? (
                      <input
                        type="text"
                        value={editingName}
                        onChange={(e) => setEditingName(e.target.value)}
                        className="px-2 py-1 text-xs border border-slate-300 rounded-md focus:outline-[#00A651] font-bold"
                        autoFocus
                      />
                    ) : (
                      <span className="font-bold text-slate-900">{cat.name}</span>
                    )}
                  </td>
                  <td className="py-3 px-4 font-mono text-slate-500">
                    {cat.slug}
                  </td>
                  <td className="py-3 px-4 text-right">
                    <div className="flex items-center justify-end gap-1.5">
                      {editingId === cat.id ? (
                        <>
                          <button
                            onClick={() => handleSaveEdit(cat.id)}
                            className="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-lg cursor-pointer"
                            title="Save"
                          >
                            <Check className="w-4 h-4" />
                          </button>
                          <button
                            onClick={() => setEditingId(null)}
                            className="p-1.5 text-slate-400 hover:bg-slate-100 rounded-lg cursor-pointer"
                            title="Cancel"
                          >
                            <X className="w-4 h-4" />
                          </button>
                        </>
                      ) : (
                        <>
                          <button
                            onClick={() => startEdit(cat)}
                            className="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg cursor-pointer"
                            title="Edit Category Name"
                          >
                            <Edit2 className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => {
                              if (confirm(`Are you sure you want to delete category "${cat.name}"?`)) {
                                deleteCategory(cat.id);
                              }
                            }}
                            className="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg cursor-pointer"
                            title="Delete Category"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};
