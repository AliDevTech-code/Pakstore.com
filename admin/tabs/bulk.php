<?php
/** Bulk import / export tab. */

require_once __DIR__ . '/../../includes/bulk-products.php';

$pdo = db();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$import = $_SESSION[BULK_IMPORT_SESSION_KEY] ?? null;
$preview = $import['products'] ?? [];

$readyCount = 0;
$errorCount = 0;
$multiImageCount = 0;
foreach ($preview as $item) {
    if ($item['errors']) {
        $errorCount++;
    } else {
        $readyCount++;
    }
    if (count($item['images']) > 1) {
        $multiImageCount++;
    }
}
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Bulk Import / Export</h1>
        <p class="text-xs text-slate-500 mt-1">Products ko Excel/CSV mein export karein ya Markaz jaise app se sheet import karein.</p>
    </div>
    <a href="<?= e(url('admin/index.php?tab=products')) ?>" class="px-3.5 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-bold flex items-center gap-1.5">
        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Manage Products
    </a>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <div class="bg-white border border-slate-200 rounded-xl p-3.5">
        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Catalog products</span>
        <span class="block text-xl font-black text-slate-900 mt-1"><?= number_format($productCount) ?></span>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-3.5">
        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Categories</span>
        <span class="block text-xl font-black text-slate-900 mt-1"><?= number_format($categoryCount) ?></span>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-3.5">
        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Columns exported</span>
        <span class="block text-xl font-black text-slate-900 mt-1"><?= count(bulk_export_columns()) ?></span>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-3.5">
        <span class="block text-[10px] font-black uppercase tracking-wider text-slate-400">Max rows / import</span>
        <span class="block text-xl font-black text-slate-900 mt-1"><?= number_format(BULK_IMPORT_MAX_ROWS) ?></span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <!-- EXPORT -->
    <section class="bg-white border border-slate-200 rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#00A651] flex items-center justify-center">
                <i data-lucide="download" class="w-4 h-4"></i>
            </span>
            <div>
                <h2 class="text-sm font-black text-slate-900">Export Products</h2>
                <p class="text-[11px] text-slate-500">Poore catalog ko sheet mein download karein.</p>
            </div>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed mt-3">
            CSV file mein name, description, price, category, SKU, stock aur images aayengi.
            Agar ek product ke 2 ya ziyada images hain to wo <strong>images</strong> column mein comma se alag alag URLs ki form mein export hoti hain (pehli URL main image).
        </p>

        <div class="flex flex-wrap gap-2 mt-4">
            <a href="<?= e(url('admin/products-bulk.php?action=export')) ?>" class="px-4 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-xs rounded-lg flex items-center gap-1.5">
                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> Download Products CSV
            </a>
            <a href="<?= e(url('admin/products-bulk.php?action=sample')) ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg flex items-center gap-1.5">
                <i data-lucide="file-down" class="w-3.5 h-3.5"></i> Sample Format
            </a>
        </div>

        <details class="mt-4 border-t border-slate-100 pt-3">
            <summary class="text-[11px] font-bold text-slate-600 cursor-pointer">Export columns dekhein</summary>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1">
                <?php foreach (bulk_column_labels() as $key => $label): ?>
                    <span class="text-[11px] text-slate-500"><code class="text-[10px] bg-slate-100 px-1 py-0.5 rounded text-slate-700"><?= e($key) ?></code> &mdash; <?= e($label) ?></span>
                <?php endforeach; ?>
            </div>
        </details>
    </section>

    <!-- IMPORT -->
    <section class="bg-white border border-slate-200 rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-2">
            <span class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                <i data-lucide="upload" class="w-4 h-4"></i>
            </span>
            <div>
                <h2 class="text-sm font-black text-slate-900">Import Products</h2>
                <p class="text-[11px] text-slate-500">CSV / TXT / XLSX sheet upload karein.</p>
            </div>
        </div>

        <?php if ($preview): ?>
            <?php if (!empty($import['grouped'])): ?>
                <div class="mt-3 p-3 rounded-lg bg-sky-50 border border-sky-200 text-[11px] text-sky-800">
                    <i data-lucide="layers" class="w-3.5 h-3.5 inline-block align-text-bottom"></i>
                    Markaz / Shopify variant sheet detect ho gayi: <strong><?= (int) $import['sheet_rows'] ?></strong> sheet rows ko group kar ke <strong><?= count($preview) ?></strong> products banaye gaye (same Handle wale variants + extra image rows ek hi product).
                </div>
            <?php endif; ?>
            <div class="mt-3 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-800">
                <strong><?= count($preview) ?></strong> product(s) preview ke liye taiyar hain &middot;
                <strong><?= $readyCount ?></strong> import ho sakte hain &middot;
                <strong><?= $multiImageCount ?></strong> mein 1 se ziyada images hain
                <?php if ($errorCount): ?> &middot; <strong class="text-rose-700"><?= $errorCount ?></strong> row(s) skip honge<?php endif; ?>
            </div>

            <?php if (!empty($import['map']['images']) && count($import['map']['images']) > 1): ?>
                <p class="text-[10px] text-slate-500 mt-2">
                    <i data-lucide="images" class="w-3 h-3 inline-block align-text-bottom"></i>
                    <?= count($import['map']['images']) ?> image columns detect ki gayi hain — sab ko combine kar ke main + gallery banayenge.
                </p>
            <?php endif; ?>

            <div class="flex flex-wrap gap-2 mt-4">
                <form action="<?= e(url('admin/products-bulk.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="import">
                    <button type="submit" class="px-4 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold text-xs rounded-lg flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Import <?= $readyCount ?> Product(s)
                    </button>
                </form>
                <form action="<?= e(url('admin/products-bulk.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="cancel">
                    <button type="submit" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg">
                        Cancel
                    </button>
                </form>
            </div>
        <?php else: ?>
            <form action="<?= e(url('admin/products-bulk.php')) ?>" method="post" enctype="multipart/form-data" class="mt-4 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="preview">
                <div>
                    <label for="bulk-file" class="block text-xs font-semibold text-slate-700 mb-1">Sheet file choose karein</label>
                    <input id="bulk-file" name="file" type="file" accept=".csv,.txt,.xlsx" required class="w-full text-xs border border-slate-300 rounded-lg p-2 bg-white file:mr-2 file:px-2.5 file:py-1 file:rounded file:border-0 file:bg-slate-900 file:text-white file:text-[11px] file:font-bold">
                    <p class="text-[10px] text-slate-500 mt-1">Maximum 12 MB aur 2000 rows. Pehle preview dikhega, phir confirm karna hoga.</p>
                </div>
                <div>
                    <label for="default-stock" class="block text-xs font-semibold text-slate-700 mb-1">Default stock (0 = random 20-80)</label>
                    <input id="default-stock" name="default_stock" type="number" min="0" step="1" value="0" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                    <p class="text-[10px] text-slate-500 mt-1">Markaz export mein qty empty hoti hai — 0 rakhein to har product ko random stock milega.</p>
                </div>
                <label class="flex items-center gap-2 text-[11px] text-slate-600 cursor-pointer">
                    <input type="checkbox" name="create_categories" value="1" checked class="w-3.5 h-3.5 accent-[#00A651]">
                    Naye categories apne aap banayein (agar sheet mein category name maujood na ho)
                </label>
                <button type="submit" class="w-full px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg flex items-center justify-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Preview Import
                </button>
            </form>
        <?php endif; ?>

        <details class="mt-4 border-t border-slate-100 pt-3">
            <summary class="text-[11px] font-bold text-slate-600 cursor-pointer">Sheet format / tips</summary>
            <div class="text-[11px] text-slate-600 leading-relaxed mt-2 space-y-1.5">
                <p>Pehli row <strong>header</strong> honi chahiye, e.g. <code class="bg-slate-100 px-1 rounded">name, description, price, images</code>.</p>
                <p>Column names flexible hain: <em>name / product name / title</em>, <em>description / details</em>, <em>price / sale price</em>, <em>images / image / gallery / photo</em>.</p>
                <p>Multiple images ek hi cell mein <code class="bg-slate-100 px-1 rounded">url1, url2, url3</code> ya alag alag <em>image 1, image 2</em> columns mein ho sakti hain. Pehli image main banegi, baqi gallery mein.</p>
                <p>SKU match hone par product update hota hai, naye SKU par naya product banega. ID di gayi to usi product ko update karega.</p>
            </div>
        </details>
    </section>
</div>

<?php if ($preview): ?>
    <section class="bg-white border border-slate-200 rounded-2xl overflow-hidden mt-5">
        <div class="px-5 py-3.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-black text-slate-900">Import Preview</h2>
                <p class="text-[11px] text-slate-500">Sirf pehli 40 rows dikhai gayi hain. Import sab rows par chalega.</p>
            </div>
            <span class="text-[10px] font-mono text-slate-400">Sheet rows: <?= count($preview) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-bold">Row</th>
                        <th class="text-left px-4 py-2.5 font-bold">Product</th>
                        <th class="text-left px-4 py-2.5 font-bold">Category</th>
                        <th class="text-right px-4 py-2.5 font-bold">Price</th>
                        <th class="text-center px-4 py-2.5 font-bold">Stock</th>
                        <th class="text-left px-4 py-2.5 font-bold">Images</th>
                        <th class="text-left px-4 py-2.5 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach (array_slice($preview, 0, 40) as $item): ?>
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-2.5 font-mono text-slate-400">#<?= (int) $item['source_row'] ?></td>
                            <td class="px-4 py-2.5 max-w-[260px]">
                                <span class="block font-bold text-slate-900 truncate" title="<?= e($item['name']) ?>"><?= e($item['name'] ?: '—') ?></span>
                                <?php if ($item['sku']): ?><span class="text-[10px] font-mono text-slate-400"><?= e($item['sku']) ?></span><?php endif; ?>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600"><?= e($item['category'] ?: '—') ?></td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900"><?= e(number_format((float) $item['price'], 0)) ?></td>
                            <td class="px-4 py-2.5 text-center font-mono text-slate-600"><?= (int) $item['stock'] ?></td>
                            <td class="px-4 py-2.5 max-w-[260px]">
                                <span class="text-[11px] font-bold <?= count($item['images']) > 1 ? 'text-emerald-700' : 'text-slate-700' ?>"><?= count($item['images']) ?> image(s)</span>
                                <?php if ($item['images']): ?>
                                    <span class="block text-[10px] text-slate-400 truncate" title="<?= e(implode("\n", $item['images'])) ?>"><?= e($item['images'][0]) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2.5">
                                <?php if ($item['errors']): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700" title="<?= e(implode(', ', $item['errors'])) ?>">Skip: <?= e(implode(', ', $item['errors'])) ?></span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Ready</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>
