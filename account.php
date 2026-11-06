<?php
/**
 * Customer account: login, signup, logout, profile update and order history.
 * Passwords use PHP password_hash() / password_verify().
 */

require_once __DIR__ . '/includes/functions.php';

$mode   = get_string('mode', 'login');
$errors = [];
$notice = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    csrf_verify();

    $action = post_string('action');

    /* ---------------- Logout ---------------- */
    if ($action === 'logout') {
        customer_logout();
        flash('info', 'You have been signed out.');
        redirect('account.php');
    }

    /* ---------------- Login ---------------- */
    if ($action === 'login') {
        $identifier = post_string('identifier');
        $password   = (string) ($_POST['password'] ?? '');

        if ($identifier === '' || $password === '') {
            $errors[] = 'Please enter your phone/email and password.';
        } else {
            $digits = preg_replace('/[^0-9]/', '', $identifier);
            $stmt = db()->prepare(
                "SELECT * FROM users
                 WHERE email = ? OR phone = ? OR REPLACE(REPLACE(phone, '-', ''), ' ', '') = ?
                 LIMIT 1"
            );
            $stmt->execute([strtolower($identifier), $identifier, $digits]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = 'Incorrect phone/email or password.';
            } else {
                $_SESSION['customer_id'] = (int) $user['id'];
                session_regenerate_id(true);
                flash('success', 'Welcome back, ' . explode(' ', $user['full_name'])[0] . '!');
                redirect(local_redirect_target('account.php'));
            }
        }
        $mode = 'login';
    }

    /* ---------------- Signup ---------------- */
    if ($action === 'signup') {
        $fullName = post_string('full_name');
        $phone    = post_string('phone');
        $email    = strtolower(post_string('email'));
        $city     = post_string('city');
        $address  = post_string('address');
        $password = (string) ($_POST['password'] ?? '');

        if (mb_strlen($fullName) < 3) {
            $errors[] = 'Please enter your full name.';
        }
        if (strlen(preg_replace('/[^0-9]/', '', $phone)) < 10) {
            $errors[] = 'Please enter a valid Pakistani mobile number.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address or leave it blank.';
        }
        if (mb_strlen($city) < 2) {
            $errors[] = 'Please enter your city.';
        }
        if (mb_strlen($address) < 8) {
            $errors[] = 'Please enter your full delivery address.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if (!$errors) {
            if ($email === '') {
                $email = 'customer-' . preg_replace('/[^0-9]/', '', $phone) . '-' . random_int(100, 999) . '@pakstore.local';
            }

            $check = db()->prepare('SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1');
            $check->execute([$email, $phone]);
            if ($check->fetchColumn()) {
                $errors[] = 'An account with this phone or email already exists. Please sign in.';
                $mode = 'signup';
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO users (full_name, phone, email, password_hash, city, address)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$fullName, $phone, $email, password_hash($password, PASSWORD_DEFAULT), $city, $address]);
                $_SESSION['customer_id'] = (int) db()->lastInsertId();
                session_regenerate_id(true);
                flash('success', 'Account created. Your delivery details are saved for 1-click COD orders.');
                redirect('account.php');
            }
        } else {
            $mode = 'signup';
        }
    }

    /* ---------------- Profile update ---------------- */
    if ($action === 'update_profile') {
        $customer = current_customer();
        if (!$customer) {
            redirect('account.php');
        }
        $fullName = post_string('full_name');
        $phone    = post_string('phone');
        $email    = strtolower(post_string('email'));
        $city     = post_string('city');
        $address  = post_string('address');

        if (mb_strlen($fullName) < 3) {
            $errors[] = 'Please enter your full name.';
        }
        if (strlen(preg_replace('/[^0-9]/', '', $phone)) < 10) {
            $errors[] = 'Please enter a valid mobile number.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (mb_strlen($city) < 2) {
            $errors[] = 'Please enter your city.';
        }
        if (mb_strlen($address) < 8) {
            $errors[] = 'Please enter your full delivery address.';
        }

        if (!$errors) {
            $stmt = db()->prepare(
                'UPDATE users SET full_name = ?, phone = ?, email = ?, city = ?, address = ? WHERE id = ?'
            );
            $stmt->execute([
                $fullName,
                $phone,
                $email !== '' ? $email : $customer['email'],
                $city,
                $address,
                (int) $customer['id'],
            ]);
            flash('success', 'Your delivery details were updated.');
            redirect('account.php');
        }
    }

    /* ---------------- Password change ---------------- */
    if ($action === 'change_password') {
        $customer = current_customer();
        if (!$customer) {
            redirect('account.php');
        }
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!password_verify($current, $customer['password_hash'])) {
            $errors[] = 'Your current password is not correct.';
        } elseif (strlen($new) < 6) {
            $errors[] = 'The new password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'The new password and confirmation do not match.';
        } else {
            $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), (int) $customer['id']]);
            flash('success', 'Password changed successfully.');
            redirect('account.php');
        }
    }
}

$customer = current_customer();

/* ---------------- Order history ---------------- */
$orders = [];
if ($customer) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 50');
    $stmt->execute([(int) $customer['id']]);
    $orders = $stmt->fetchAll();
    foreach ($orders as &$row) {
        $row['items'] = order_items((int) $row['id']);
    }
    unset($row);
}

$pageTitle = ($customer ? 'My Account' : 'Sign In / Register') . ' - ' . setting('store_name', 'PakStore');
require __DIR__ . '/includes/header.php';
?>

<main class="flex-1 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <?php if (!$customer): ?>
            <div class="max-w-md mx-auto">
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-5 bg-slate-900 text-white text-center">
                        <div class="w-11 h-11 rounded-full bg-emerald-500/15 text-[#00A651] flex items-center justify-center mx-auto mb-2">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </div>
                        <h1 class="text-base font-bold tracking-tight"><?= $mode === 'signup' ? 'Create Your Account' : 'Sign In to PakStore' ?></h1>
                        <p class="text-[11px] text-slate-400 mt-0.5">Save your address for faster Cash on Delivery orders.</p>
                    </div>

                    <div class="p-6">
                        <?php if ($errors): ?>
                            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg space-y-1">
                                <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($mode === 'signup'): ?>
                            <form method="post" action="<?= e(url('account.php?mode=signup')) ?>" class="space-y-3">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="signup">
                                <input type="hidden" name="redirect" value="<?= e(get_string('redirect')) ?>">

                                <div>
                                    <label for="full_name" class="block text-xs font-semibold text-slate-700 mb-1">Full Name *</label>
                                    <input id="full_name" name="full_name" type="text" required maxlength="150" placeholder="e.g. Usman Tariq" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Mobile *</label>
                                        <input id="phone" name="phone" type="tel" required maxlength="30" placeholder="0300-1234567" class="w-full px-2.5 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                                    </div>
                                    <div>
                                        <label for="city" class="block text-xs font-semibold text-slate-700 mb-1">City *</label>
                                        <input id="city" name="city" type="text" required maxlength="100" placeholder="e.g. Lahore" class="w-full px-2.5 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email (optional)</label>
                                    <input id="email" name="email" type="email" maxlength="190" placeholder="you@example.com" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                                </div>

                                <div>
                                    <label for="address" class="block text-xs font-semibold text-slate-700 mb-1">Delivery Address *</label>
                                    <textarea id="address" name="address" rows="2" required maxlength="1000" placeholder="House #, Street #, Sector / Area, Landmark..." class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"></textarea>
                                </div>

                                <div>
                                    <label for="signup-password" class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                                    <input id="signup-password" name="password" type="password" required minlength="6" placeholder="Choose a password (min 6 chars)" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                                </div>

                                <button type="submit" class="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Create Account &amp; Save Address
                                </button>

                                <p class="text-center text-[11px] text-slate-500 pt-1">
                                    Already registered?
                                    <a href="<?= e(url('account.php')) ?>" class="text-[#00A651] font-bold hover:underline">Sign in</a>
                                </p>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= e(url('account.php')) ?>" class="space-y-3.5">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="login">
                                <input type="hidden" name="redirect" value="<?= e(get_string('redirect')) ?>">

                                <div>
                                    <label for="identifier" class="block text-xs font-semibold text-slate-700 mb-1">Phone or Email *</label>
                                    <input id="identifier" name="identifier" type="text" required placeholder="0300-1234567 or email@domain.com" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651] font-mono">
                                </div>

                                <div>
                                    <label for="login-password" class="block text-xs font-semibold text-slate-700 mb-1">Password *</label>
                                    <input id="login-password" name="password" type="password" required placeholder="Your password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                                </div>

                                <button type="submit" class="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors">
                                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Sign In to PakStore
                                </button>

                                <div class="flex items-center justify-between text-[11px]">
                                    <a href="<?= e(url('account.php?mode=signup')) ?>" class="text-[#00A651] font-bold hover:underline">Create an account</a>
                                    <a href="<?= e(url('track-order.php')) ?>" class="text-slate-500 hover:text-slate-800">Track without signing in</a>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if ($mode !== 'signup'): ?>
                            <p class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 text-center">
                                New here? <a href="<?= e(url('account.php?mode=signup')) ?>" class="text-[#00A651] font-bold hover:underline">Create a free account</a>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Logged in -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Assalam-o-Alaikum, <?= e(explode(' ', $customer['full_name'])[0]) ?>!</h1>
                    <p class="text-xs text-slate-500 mt-1"><?= e($customer['phone']) ?><?= $customer['email'] !== '' ? ' · ' . e($customer['email']) : '' ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= e(url('index.php#catalog-section')) ?>" class="px-3.5 py-2 text-xs font-bold text-[#00A651] bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 flex items-center gap-1.5">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Continue shopping
                    </a>
                    <form action="<?= e(url('account.php')) ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="px-3.5 py-2 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 flex items-center gap-1.5">
                            <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Sign out
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($errors): ?>
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg space-y-1">
                    <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Orders -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <i data-lucide="package" class="w-4 h-4 text-[#00A651]"></i> My Orders
                        </h2>
                        <span class="text-[11px] text-slate-500"><?= count($orders) ?> order<?= count($orders) === 1 ? '' : 's' ?></span>
                    </div>

                    <?php if (!$orders): ?>
                        <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
                            <i data-lucide="package-open" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-500">You have not placed any orders yet.</p>
                            <a href="<?= e(url('index.php#catalog-section')) ?>" class="inline-block mt-3 px-4 py-2 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold">Start shopping</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <span class="text-[10px] uppercase tracking-wider text-slate-400 font-bold block">Order ID</span>
                                        <span class="font-mono text-sm font-black text-slate-900">#<?= e($order['order_code']) ?></span>
                                        <span class="text-[10px] text-slate-500 ml-2"><?= e(format_date($order['created_at'], false)) ?></span>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= e(order_status_classes($order['status'])) ?>"><?= e($order['status']) ?></span>
                                </div>

                                <div class="p-4 space-y-2">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="flex items-center justify-between gap-3 text-xs">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <img src="<?= e(image_url($item['image'])) ?>" alt="<?= e($item['title']) ?>" class="w-8 h-8 rounded object-cover border border-slate-100 shrink-0">
                                                <span class="text-slate-700 truncate"><?= e($item['title']) ?> &times; <?= (int) $item['quantity'] ?></span>
                                            </div>
                                            <span class="font-mono font-bold text-slate-900 shrink-0"><?= e(money((float) $item['price'] * (int) $item['quantity'])) ?></span>
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="pt-2 mt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                        <span class="text-xs text-slate-500">Total: <strong class="text-slate-900 font-mono"><?= e(money($order['total_amount'])) ?></strong> (COD)</span>
                                        <a href="<?= e(url('track-order.php?order=' . urlencode($order['order_code']))) ?>" class="text-[11px] font-bold text-[#00A651] hover:underline flex items-center gap-1">
                                            Track order <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Profile -->
                <div class="lg:col-span-4 space-y-4 lg:sticky lg:top-28">
                    <div class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-4 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-[#00A651]"></i> Saved Delivery Details
                        </h2>

                        <form method="post" action="<?= e(url('account.php')) ?>" class="space-y-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_profile">

                            <div>
                                <label for="acc-name" class="block text-[11px] font-semibold text-slate-600 mb-1">Full Name</label>
                                <input id="acc-name" name="full_name" type="text" required maxlength="150" value="<?= e($customer['full_name']) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            </div>

                            <div>
                                <label for="acc-phone" class="block text-[11px] font-semibold text-slate-600 mb-1">Mobile Number</label>
                                <input id="acc-phone" name="phone" type="tel" required maxlength="30" value="<?= e($customer['phone']) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                            </div>

                            <div>
                                <label for="acc-email" class="block text-[11px] font-semibold text-slate-600 mb-1">Email (optional)</label>
                                <input id="acc-email" name="email" type="email" maxlength="190" value="<?= e($customer['email']) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            </div>

                            <div>
                                <label for="acc-city" class="block text-[11px] font-semibold text-slate-600 mb-1">City</label>
                                <input id="acc-city" name="city" type="text" required maxlength="100" value="<?= e($customer['city']) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            </div>

                            <div>
                                <label for="acc-address" class="block text-[11px] font-semibold text-slate-600 mb-1">Delivery Address</label>
                                <textarea id="acc-address" name="address" rows="3" required maxlength="1000" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]"><?= e($customer['address']) ?></textarea>
                            </div>

                            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold flex items-center justify-center gap-1.5">
                                <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Changes
                            </button>
                        </form>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-2xl p-5">
                        <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 mb-4 flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-4 h-4 text-[#00A651]"></i> Change Password
                        </h2>
                        <form method="post" action="<?= e(url('account.php')) ?>" class="space-y-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="change_password">
                            <input type="password" name="current_password" required placeholder="Current password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            <input type="password" name="new_password" required minlength="6" placeholder="New password (min 6)" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Confirm new password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                            <button type="submit" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-lg text-xs font-bold">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
