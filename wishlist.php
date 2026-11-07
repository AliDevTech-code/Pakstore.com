<?php
/** Session wishlist: toggle endpoint + wishlist page. */

require_once __DIR__ . '/includes/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    csrf_verify();

    $action = post_string('action');
    $productId = post_int('product_id');

    if ($action === 'toggle' && $productId > 0) {
        $stmt = db()->prepare('SELECT id FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$productId]);
        if ($stmt->fetchColumn()) {
            wishlist_toggle($productId);
            flash('success', wishlist_has($productId) ? 'Added to your wishlist.' : 'Removed from your wishlist.');
        } else {
            flash('error', 'That product no longer exists.');
        }
    } elseif ($action === 'clear') {
        $_SESSION['wishlist'] = [];
        flash('info', 'Wishlist cleared.');
        redirect('wishlist.php');
    }

    redirect(local_redirect_target('wishlist.php'));
}

$ids = wishlist_ids();
$products = [];
if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.id IN ($placeholders)
         ORDER BY p.created_at DESC"
    );
    $stmt->execute($ids);
    $products = $stmt->fetchAll();
}

$pageTitle = 'My Wishlist - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">My Wishlist</h1>
                <p class="text-xs text-slate-500 mt-1"><?= count($products) ?> saved item<?= count($products) === 1 ? '' : 's' ?></p>
            </div>
            <?php if ($products): ?>
                <form action="<?= e(url('wishlist.php')) ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1.5" data-confirm="Clear your entire wishlist?">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Clear wishlist
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (!$products): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-rose-50 text-rose-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="heart" class="w-8 h-8"></i>
                </div>
                <h2 class="text-base font-bold text-slate-800">Your wishlist is empty</h2>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Tap the heart icon on any product to save it here for later.</p>
                <a href="<?= e(url('index.php#catalog-section')) ?>" class="inline-block mt-5 px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-xl text-xs font-bold">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
                <?php foreach ($products as $p): ?>
                    <?php
                    $price = (float) $p['price'];
                    $original = $p['original_price'] !== null && (float) $p['original_price'] > $price ? (float) $p['original_price'] : 0.0;
                    $inStock = (int) $p['in_stock'] === 1 && (int) $p['stock'] > 0;
                    ?>
                    <div class="group bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col hover:shadow-lg transition-all">
                        <div class="relative aspect-square bg-slate-50 overflow-hidden">
                            <a href="<?= e(url('product.php?id=' . (int) $p['id'])) ?>" class="block w-full h-full">
                                <img src="<?= e(image_url($p['image'])) ?>" alt="<?= e($p['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                            <form action="<?= e(url('wishlist.php')) ?>" method="post" class="absolute top-2 right-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="p-1.5 rounded-full bg-rose-500 text-white shadow-sm" title="Remove from wishlist">
                                    <i data-lucide="heart" class="w-3.5 h-3.5 fill-white"></i>
                                </button>
                            </form>
                        </div>
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400"><?= e($p['category_name'] ?: 'Uncategorized') ?></span>
                                <a href="<?= e(url('product.php?id=' . (int) $p['id'])) ?>" class="block text-xs font-bold text-slate-900 clamp-2 min-h-[32px] hover:text-[#00A651] mt-0.5"><?= e($p['title']) ?></a>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="flex items-baseline gap-1.5 mb-2">
                                    <span class="text-xs font-black text-slate-900 font-mono"><?= e(money($price)) ?></span>
                                    <?php if ($original > 0): ?><span class="text-[10px] text-slate-400 line-through font-mono"><?= e(money($original)) ?></span><?php endif; ?>
                                </div>
                                <a href="<?= e(url('checkout.php?product=' . (int) $p['id'] . '&qty=1')) ?>" class="w-full py-2 text-[11px] font-bold rounded-lg bg-[#00A651] hover:bg-[#008f45] text-white transition-colors flex items-center justify-center gap-1 <?= $inStock ? '' : 'opacity-50 pointer-events-none' ?>">
                                    <i data-lucide="zap" class="w-3 h-3 fill-current"></i> Order Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
