<?php
/**
 * Cart page + cart actions (session cart).
 *  POST action=add|update|remove|clear
 *  GET  -> cart page
 */

require_once __DIR__ . '/includes/functions.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    csrf_verify();

    $action = post_string('action');
    $productId = post_int('product_id');

    if ($action === 'add') {
        $quantity = max(1, post_int('quantity', 1));
        $stmt = db()->prepare('SELECT id, title, stock, in_stock FROM products WHERE id = ? LIMIT 1');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            flash('error', 'That product no longer exists.');
        } elseif (!(int) $product['in_stock'] || (int) $product['stock'] <= 0) {
            flash('error', 'Sorry, this product is currently out of stock.');
        } else {
            $existing = (int) (cart_raw()[$productId] ?? 0);
            $available = max(0, (int) $product['stock'] - $existing);
            if ($available <= 0) {
                flash('error', 'You already have the maximum available stock of this product in your bag.');
            } else {
                $quantity = max(1, min($quantity, $available));
                cart_add($productId, $quantity);
                flash('success', 'Added to your shopping bag: ' . $product['title']);
            }
        }
        redirect(local_redirect_target('index.php'));
    }

    if ($action === 'update') {
        cart_update($productId, post_int('quantity', 1));
        redirect(local_redirect_target('cart.php'));
    }

    if ($action === 'remove') {
        cart_remove($productId);
        flash('info', 'Item removed from your bag.');
        redirect(local_redirect_target('cart.php'));
    }

    if ($action === 'clear') {
        cart_clear();
        flash('info', 'Your shopping bag is now empty.');
        redirect('cart.php');
    }

    flash('error', 'Unknown cart action.');
    redirect('cart.php');
}

$items = cart_detailed();
$totals = cart_totals();

$pageTitle = 'Shopping Bag - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="text-xs text-slate-500 mb-5 flex items-center gap-2" aria-label="Breadcrumb">
            <a href="<?= e(url('index.php')) ?>" class="hover:text-[#00A651]">Home</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Shopping Bag</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Shopping Bag</h1>
                <p class="text-xs text-slate-500 mt-1"><?= count($items) ?> product<?= count($items) === 1 ? '' : 's' ?> in your bag &middot; Cash on Delivery available</p>
            </div>
            <a href="<?= e(url('index.php#catalog-section')) ?>" class="text-xs font-bold text-[#00A651] hover:underline flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Continue shopping
            </a>
        </div>

        <?php if (!$items): ?>
            <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shopping-bag" class="w-8 h-8"></i>
                </div>
                <h2 class="text-base font-bold text-slate-800">Your bag is empty</h2>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Explore the catalog and add your favourite items. You only pay cash when the courier arrives.</p>
                <a href="<?= e(url('index.php#catalog-section')) ?>" class="inline-block mt-5 px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-xl text-xs font-bold">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Items -->
                <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <?php foreach ($items as $index => $item): ?>
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row gap-4 <?= $index > 0 ? 'border-t border-slate-100' : '' ?>">
                            <a href="<?= e(url('product.php?id=' . (int) $item['id'])) ?>" class="shrink-0">
                                <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-24 h-24 rounded-xl object-cover border border-slate-200 bg-slate-50">
                            </a>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?= e($item['category_name'] ?: 'Uncategorized') ?></span>
                                        <a href="<?= e(url('product.php?id=' . (int) $item['id'])) ?>" class="block text-sm font-bold text-slate-900 hover:text-[#00A651] truncate"><?= e($item['title']) ?></a>
                                        <span class="text-[11px] text-slate-500 font-mono"><?= e(money($item['price'])) ?> each</span>
                                    </div>
                                    <form action="<?= e(url('cart.php')) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="redirect" value="cart.php">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Remove item">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                    <form action="<?= e(url('cart.php')) ?>" method="post" class="flex items-center border border-slate-300 rounded-lg bg-slate-50 text-xs" data-qty>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                        <input type="hidden" name="redirect" value="cart.php">
                                        <button type="submit" name="quantity" value="<?= max(0, (int) $item['qty'] - 1) ?>" class="px-3 py-1.5 text-slate-700 hover:bg-slate-200 font-bold" aria-label="Decrease quantity">-</button>
                                        <span class="px-4 py-1.5 font-mono font-bold text-slate-900 bg-white border-x border-slate-200"><?= (int) $item['qty'] ?></span>
                                        <button type="submit" name="quantity" value="<?= (int) $item['qty'] + 1 ?>" class="px-3 py-1.5 text-slate-700 hover:bg-slate-200 font-bold" aria-label="Increase quantity">+</button>
                                    </form>

                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Line total</span>
                                        <span class="text-sm font-black text-slate-900 font-mono"><?= e(money($item['line_total'])) ?></span>
                                    </div>
                                </div>

                                <?php if ((int) $item['stock'] <= 5): ?>
                                    <p class="text-[10px] text-amber-600 font-semibold mt-2">Only <?= (int) $item['stock'] ?> left in stock</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="px-4 sm:px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                        <form action="<?= e(url('cart.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-rose-600">Clear bag</button>
                        </form>
                        <a href="<?= e(url('index.php#catalog-section')) ?>" class="text-[11px] font-bold text-[#00A651] hover:underline">Add more items</a>
                    </div>
                </div>

                <!-- Summary -->
                <aside class="lg:col-span-4 bg-white border border-slate-200 rounded-2xl p-5 space-y-4 lg:sticky lg:top-28">
                    <h2 class="font-bold text-slate-900 text-sm pb-3 border-b border-slate-100">Order Summary</h2>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-mono font-bold text-slate-900"><?= e(money($totals['subtotal'])) ?></span>
                        </div>
                        <div class="flex justify-between text-slate-600">
                            <span>Delivery charges</span>
                            <span class="font-mono font-bold">
                                <?php if ($totals['shipping'] <= 0): ?>
                                    <span class="text-emerald-600 uppercase text-[11px] font-bold">Free</span>
                                <?php else: ?>
                                    <?= e(money($totals['shipping'])) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-slate-900 pt-3 border-t border-slate-200">
                            <span>Total payable (COD)</span>
                            <span class="font-mono text-emerald-700"><?= e(money($totals['total'])) ?></span>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-slate-700 flex items-start gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#00A651] shrink-0 mt-0.5"></i>
                        <span>100% Cash on Delivery. Inspect the parcel, then pay the rider in cash.</span>
                    </div>

                    <a href="<?= e(url('checkout.php')) ?>" class="w-full py-3 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-sm transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span>Proceed to Checkout</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>

                    <div class="grid grid-cols-3 gap-2 text-[10px] text-slate-500 text-center pt-2 border-t border-slate-100">
                        <div><i data-lucide="truck" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>2-4 Days</div>
                        <div><i data-lucide="banknote" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>Cash COD</div>
                        <div><i data-lucide="refresh-cw" class="w-4 h-4 text-[#00A651] mx-auto mb-1"></i>7-Day Return</div>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
