<?php
/** Secure admin sign-in. */

require_once __DIR__ . '/../includes/functions.php';

if (current_admin()) {
    redirect('admin/index.php');
}

$errors = [];
$email = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    csrf_verify();

    $email = strtolower(post_string('email'));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter your email and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admin_users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_matches($password, $admin['password_hash'])) {
            // Small delay to slow brute-force attempts.
            usleep(400000);
            $errors[] = 'Invalid admin email or password.';
        } else {
            // Upgrade the seeded SHA-256 hash to a modern bcrypt hash.
            if (strlen($admin['password_hash']) === 64 && ctype_xdigit($admin['password_hash'])) {
                $upgrade = db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
                $upgrade->execute([password_hash($password, PASSWORD_DEFAULT), (int) $admin['id']]);
            }

            $touch = db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
            $touch->execute([(int) $admin['id']]);

            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            flash('success', 'Welcome back, admin.');
            redirect('admin/index.php');
        }
    }
}

$pageTitle = 'Admin Sign In - ' . setting('store_name', 'PakStore');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="https://unpkg.com/lucide@0.546.0/dist/umd/lucide.min.js"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-900 flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-6">
        <a href="<?= e(url('index.php')) ?>" class="inline-flex items-end justify-center">
            <span class="text-3xl font-black tracking-tight text-[#00A651]">Pak</span>
            <span class="text-3xl font-black tracking-tight text-[#FF6B00]">Store</span>
        </a>
        <p class="text-[11px] uppercase tracking-[0.3em] text-slate-500 font-bold mt-1">Admin Control Panel</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center gap-2">
            <i data-lucide="lock" class="w-4 h-4 text-[#00A651]"></i>
            <h1 class="text-sm font-bold">Staff Authorization</h1>
        </div>

        <div class="p-6">
            <?php foreach (take_flashes() as $item): ?>
                <div class="mb-4 p-3 rounded-lg text-xs border <?= ($item['type'] ?? '') === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-sky-50 text-sky-800 border-sky-200' ?>">
                    <?= e($item['message']) ?>
                </div>
            <?php endforeach; ?>

            <?php if ($errors): ?>
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg space-y-1">
                    <?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('admin/login.php')) ?>" class="space-y-3.5">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Admin Email</label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                        <input id="email" name="email" type="email" required autocomplete="username" value="<?= e($email) ?>" placeholder="admin@pakstore.com" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                    <div class="relative">
                        <i data-lucide="key-round" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Your admin password" class="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] focus:border-[#00A651]">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white font-bold rounded-lg text-xs flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                    <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Sign In Securely
                </button>
            </form>

            <p class="mt-4 pt-3 border-t border-slate-100 text-[10px] text-slate-400 text-center leading-relaxed">
                Default first-time login: <code class="font-mono text-slate-600">admin@pakstore.com</code> / <code class="font-mono text-slate-600">Admin@12345</code><br>
                Change this password immediately after signing in.
            </p>
        </div>
    </div>

    <a href="<?= e(url('index.php')) ?>" class="mt-4 flex items-center justify-center gap-1.5 text-[11px] text-slate-500 hover:text-slate-300">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to storefront
    </a>
</div>

<script>
    if (window.lucide) { window.lucide.createIcons(); }
</script>
</body>
</html>
