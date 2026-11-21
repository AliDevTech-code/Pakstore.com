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
        $redir = local_redirect_target('index.php');
        if (str_contains($redir, 'checkout.php?product=') && !str_contains($redir, 'qty=')) { $redir .= '&qty=' . $quantity; }
        redirect($redir);
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
<main class="flex-1 bg-[#f3f4f6]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-6">
      <div><h1 class="pk-title">Shopping Cart</h1><p class="pk-sub"><?= count($items) ?> item<?= count($items)===1?'':'s' ?> in your cart • Cash on Delivery available</p></div>
      <a href="<?= e(url('shop.php')) ?>" class="text-sm font-bold text-[#166534] hover:text-[#f97316]">← Continue Shopping</a>
    </div>

    <?php if (!$items): ?>
      <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
        <i data-lucide="shopping-bag" class="w-14 h-14 mx-auto text-gray-300 mb-4"></i>
        <h2 class="text-xl font-bold text-gray-900">Your cart is empty</h2>
        <p class="text-gray-500 mt-2">Apni pasand ki products add karein aur Cash on Delivery ka faida uthayein.</p>
        <a href="<?= e(url('shop.php')) ?>" class="pk-btn pk-btn-orange mt-6">Start Shopping</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-8 bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
          <?php foreach ($items as $index => $item): ?>
            <div class="p-4 sm:p-5 flex flex-col sm:flex-row gap-4 <?= $index>0?'border-t border-gray-100':'' ?>">
              <a href="<?= e(url('product.php?id='.(int)$item['id'])) ?>" class="shrink-0"><img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-24 h-24 rounded-xl object-cover border border-gray-200 bg-gray-50"></a>
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400"><?= e($item['category_name'] ?: 'Product') ?></div>
                    <a href="<?= e(url('product.php?id='.(int)$item['id'])) ?>" class="block font-bold text-gray-900 hover:text-[#166534] truncate"><?= e($item['title']) ?></a>
                    <div class="text-sm text-gray-500 mt-0.5"><?= e(money($item['price'])) ?> each</div>
                    <?php if (!empty($item['original_price']) && (float)$item['original_price'] > (float)$item['price']): ?><div class="text-xs text-gray-400 line-through"><?= e(money($item['original_price'])) ?></div><?php endif; ?>
                  </div>
                  <form action="<?= e(url('cart.php')) ?>" method="post">
                    <?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="redirect" value="cart.php">
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Remove"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                  </form>
                </div>
                <div class="mt-3 flex items-center justify-between gap-3">
                  <form action="<?= e(url('cart.php')) ?>" method="post" class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                    <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="redirect" value="cart.php">
                    <button type="submit" name="quantity" value="<?= max(1,(int)$item['qty']-1) ?>" class="px-3 py-2 hover:bg-gray-100">-</button>
                    <span class="px-4 py-2 font-bold text-sm border-x border-gray-200"><?= (int)$item['qty'] ?></span>
                    <button type="submit" name="quantity" value="<?= (int)$item['qty']+1 ?>" class="px-3 py-2 hover:bg-gray-100">+</button>
                  </form>
                  <div class="font-black text-[#166534] text-lg font-heading"><?= e(money($item['line_total'])) ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="lg:col-span-4 bg-white border border-gray-200 rounded-2xl p-5 shadow-sm sticky top-24">
          <h2 class="font-extrabold text-lg text-[#166534] mb-4">Order Summary</h2>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-600">Subtotal</span><span class="font-bold"><?= e(money($totals['subtotal'])) ?></span></div>
            <div class="flex justify-between"><span class="text-gray-600">Delivery</span><span class="font-bold"><?= $totals['shipping'] <= 0 ? '<span class="text-[#166534]">FREE</span>' : e(money($totals['shipping'])) ?></span></div>
            <div class="flex justify-between pt-3 border-t border-gray-200 text-base"><span class="font-extrabold text-gray-900">Total</span><span class="font-black text-[#166534] text-xl font-heading"><?= e(money($totals['total'])) ?></span></div>
          </div>
          <div class="mt-5 space-y-3">
            <a href="<?= e(url('checkout.php')) ?>" class="pk-btn pk-btn-orange w-full">Proceed to Checkout</a>
            <div class="text-center text-xs text-gray-500 flex items-center justify-center gap-1"><i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#166534]"></i> Cash on Delivery • Secure Order</div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>