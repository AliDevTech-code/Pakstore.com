import React, { useState } from 'react';
import {
  SQL_SCHEMA,
  PHP_CONFIG,
  PHP_INDEX,
  PHP_CHECKOUT,
  PHP_ADMIN_ORDERS,
  PHP_ADMIN_ADD_PRODUCT,
  PHP_ADMIN_LOGIN,
  INFINITYFREE_GUIDE,
} from '../../data/infinityfreeTemplates';
import { Download, Copy, Check, Server, Database, Code2, BookOpen, ExternalLink, Lock } from 'lucide-react';
import JSZip from 'jszip';

export const AdminInfinityFreeTab: React.FC = () => {
  const [activeTab, setActiveTab] = useState<
    'guide' | 'sql' | 'config' | 'index' | 'checkout' | 'orders' | 'add_product' | 'admin_login'
  >('guide');
  const [copied, setCopied] = useState(false);
  const [isZipping, setIsZipping] = useState(false);

  const getActiveCode = () => {
    switch (activeTab) {
      case 'sql':
        return { code: SQL_SCHEMA, filename: 'pakstore_db.sql' };
      case 'config':
        return { code: PHP_CONFIG, filename: 'config.php' };
      case 'index':
        return { code: PHP_INDEX, filename: 'index.php' };
      case 'checkout':
        return { code: PHP_CHECKOUT, filename: 'checkout.php' };
      case 'orders':
        return { code: PHP_ADMIN_ORDERS, filename: 'admin/orders.php' };
      case 'add_product':
        return { code: PHP_ADMIN_ADD_PRODUCT, filename: 'admin/add_product.php' };
      case 'admin_login':
        return { code: PHP_ADMIN_LOGIN, filename: 'admin/login.php' };
      default:
        return { code: INFINITYFREE_GUIDE, filename: 'InfinityFree_Deployment_Guide.txt' };
    }
  };

  const handleCopy = () => {
    const { code } = getActiveCode();
    navigator.clipboard.writeText(code);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const handleDownloadSingle = () => {
    const { code, filename } = getActiveCode();
    const blob = new Blob([code], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename.split('/').pop() || 'file.txt';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
  };

  const handleDownloadAllZip = async () => {
    setIsZipping(true);
    try {
      const zip = new JSZip();

      // Database
      zip.file('pakstore_db.sql', SQL_SCHEMA);
      // Main site
      zip.file('config.php', PHP_CONFIG);
      zip.file('index.php', PHP_INDEX);
      zip.file('checkout.php', PHP_CHECKOUT);
      zip.file('InfinityFree_Guide.txt', INFINITYFREE_GUIDE);

      // Admin subfolder
      const adminFolder = zip.folder('admin');
      if (adminFolder) {
        adminFolder.file('login.php', PHP_ADMIN_LOGIN);
        adminFolder.file('orders.php', PHP_ADMIN_ORDERS);
        adminFolder.file('add_product.php', PHP_ADMIN_ADD_PRODUCT);
      }

      const content = await zip.generateAsync({ type: 'blob' });
      const url = URL.createObjectURL(content);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'Pakstore_InfinityFree_PHP_MySQL.zip';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    } catch (err) {
      alert('Failed to generate ZIP package.');
    } finally {
      setIsZipping(false);
    }
  };

  return (
    <div className="space-y-6">
      {/* Banner */}
      <div className="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 text-white rounded-2xl p-6 sm:p-8 shadow-sm border border-emerald-800/40">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div className="space-y-2">
            <span className="text-emerald-400 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
              <Server className="w-4 h-4" /> InfinityFree PHP & MySQL Deployment Kit
            </span>
            <h3 className="text-xl sm:text-2xl font-bold tracking-tight">
              Host Pakstore on Your InfinityFree Domain
            </h3>
            <p className="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
              We have generated the complete production-ready PHP scripts and MySQL database schema for your InfinityFree hosting with full Cash on Delivery and customer address tracking.
            </p>
          </div>

          <button
            onClick={handleDownloadAllZip}
            disabled={isZipping}
            className="px-5 py-3 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs sm:text-sm flex items-center justify-center gap-2 transition-all shadow-md cursor-pointer shrink-0"
          >
            <Download className="w-4 h-4" />
            <span>{isZipping ? 'Creating ZIP...' : 'Download Full PHP + MySQL ZIP'}</span>
          </button>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <button
          onClick={() => setActiveTab('guide')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'guide'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <BookOpen className="w-3.5 h-3.5 text-emerald-400" />
          <span>Setup Guide (Urdu & English)</span>
        </button>

        <button
          onClick={() => setActiveTab('sql')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'sql'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Database className="w-3.5 h-3.5 text-amber-500" />
          <span>pakstore_db.sql</span>
        </button>

        <button
          onClick={() => setActiveTab('config')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'config'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Code2 className="w-3.5 h-3.5" />
          <span>config.php</span>
        </button>

        <button
          onClick={() => setActiveTab('index')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'index'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Code2 className="w-3.5 h-3.5" />
          <span>index.php</span>
        </button>

        <button
          onClick={() => setActiveTab('checkout')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'checkout'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Code2 className="w-3.5 h-3.5" />
          <span>checkout.php (COD)</span>
        </button>

        <button
          onClick={() => setActiveTab('orders')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'orders'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Code2 className="w-3.5 h-3.5 text-emerald-500" />
          <span>admin/orders.php</span>
        </button>

        <button
          onClick={() => setActiveTab('admin_login')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'admin_login'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Lock className="w-3.5 h-3.5 text-amber-500" />
          <span>admin/login.php</span>
        </button>

        <button
          onClick={() => setActiveTab('add_product')}
          className={`px-3.5 py-2 text-xs font-semibold rounded-lg flex items-center gap-1.5 cursor-pointer transition-colors ${
            activeTab === 'add_product'
              ? 'bg-slate-900 text-white shadow-xs'
              : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200'
          }`}
        >
          <Code2 className="w-3.5 h-3.5 text-blue-500" />
          <span>admin/add_product.php</span>
        </button>
      </div>

      {/* Code / Content Viewer */}
      <div className="bg-slate-900 text-slate-100 rounded-2xl border border-slate-800 overflow-hidden shadow-md">
        {/* Viewer Header */}
        <div className="px-5 py-3 bg-slate-950 border-b border-slate-800 flex items-center justify-between text-xs">
          <span className="font-mono font-bold text-slate-300">
            {getActiveCode().filename}
          </span>
          <div className="flex items-center gap-2">
            <button
              onClick={handleCopy}
              className="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-md font-medium flex items-center gap-1 transition-colors cursor-pointer"
            >
              {copied ? <Check className="w-3.5 h-3.5 text-emerald-400" /> : <Copy className="w-3.5 h-3.5" />}
              <span>{copied ? 'Copied!' : 'Copy Code'}</span>
            </button>
            <button
              onClick={handleDownloadSingle}
              className="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium flex items-center gap-1 transition-colors cursor-pointer"
            >
              <Download className="w-3.5 h-3.5" />
              <span>Download File</span>
            </button>
          </div>
        </div>

        {/* Code Content */}
        <pre className="p-5 text-xs font-mono overflow-x-auto max-h-[500px] leading-relaxed text-slate-300 bg-slate-900">
          <code>{getActiveCode().code}</code>
        </pre>
      </div>
    </div>
  );
};
