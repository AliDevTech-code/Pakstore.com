<?php
/** Settings tab. */

$pdo = db();
$staff = $pdo->query('SELECT * FROM admin_users ORDER BY id ASC')->fetchAll();
$logo = setting('logo_url', '');
?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Store Settings</h1>
        <p class="text-xs text-slate-500 mt-1">Branding, contact details, delivery charges and admin access.</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
    <div class="lg:col-span-2 space-y-4">
        <!-- Store -->
        <form action="<?= e(url('admin/settings.php')) ?>" method="post" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_store">

            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-1.5">
                <i data-lucide="store" class="w-4 h-4 text-[#00A651]"></i> Store Profile
            </h2>

            <div class="flex flex-col sm:flex-row gap-4 items-start">
                <div class="w-24 h-24 rounded-xl border border-slate-200 bg-slate-50 overflow-hidden shrink-0 flex items-center justify-center">
                    <?php if ($logo !== ''): ?>
                        <img src="<?= e(image_url($logo)) ?>" alt="Store logo" class="w-full h-full object-contain">
                    <?php else: ?>
                        <span class="text-[10px] text-slate-400 text-center px-2">No logo<br>(text logo used)</span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 space-y-2">
                    <div>
                        <label for="logo_file" class="block text-xs font-semibold text-slate-700 mb-1">Upload Logo (JPG / PNG / WEBP, max 3 MB)</label>
                        <input id="logo_file" name="logo_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="w-full text-xs border border-slate-300 rounded-lg p-1.5 bg-white file:mr-2 file:px-2.5 file:py-1 file:rounded file:border-0 file:bg-slate-900 file:text-white file:text-[11px] file:font-bold">
                    </div>
                    <?php if ($logo !== ''): ?>
                        <label class="flex items-center gap-2 text-[11px] text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300"> Remove current logo (use text logo)
                        </label>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="store_name" class="block text-xs font-semibold text-slate-700 mb-1">Store Name *</label>
                    <input id="store_name" name="store_name" type="text" required maxlength="120" value="<?= e(setting('store_name', 'PakStore')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="delivery_charges" class="block text-xs font-semibold text-slate-700 mb-1">Delivery Charges (Rs.)</label>
                    <input id="delivery_charges" name="delivery_charges" type="number" min="0" step="0.01" value="<?= e(setting('delivery_charges', '0')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                    <span class="text-[10px] text-slate-500">Set 0 to show FREE delivery.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">Helpline Phone</label>
                    <input id="phone" name="phone" type="text" maxlength="30" value="<?= e(setting('phone')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="whatsapp" class="block text-xs font-semibold text-slate-700 mb-1">WhatsApp Number</label>
                    <input id="whatsapp" name="whatsapp" type="text" maxlength="30" value="<?= e(setting('whatsapp')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg font-mono focus:outline-[#00A651]">
                </div>
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Support Email</label>
                    <input id="email" name="email" type="email" maxlength="190" value="<?= e(setting('email')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                </div>
            </div>

            <div>
                <label for="top_bar_text" class="block text-xs font-semibold text-slate-700 mb-1">Top Bar Text</label>
                <input id="top_bar_text" name="top_bar_text" type="text" maxlength="190" value="<?= e(setting('top_bar_text')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>

            <div>
                <label for="announcement" class="block text-xs font-semibold text-slate-700 mb-1">Announcement</label>
                <input id="announcement" name="announcement" type="text" maxlength="190" value="<?= e(setting('announcement')) ?>" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>

            <div class="flex justify-end pt-1">
                <button type="submit" class="px-5 py-2.5 bg-[#00A651] hover:bg-[#008f45] text-white rounded-lg text-xs font-bold flex items-center gap-1.5">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Settings
                </button>
            </div>
        </form>

        <!-- Staff -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5">
            <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3 flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-[#00A651]"></i> Admin Accounts
            </h2>

            <div class="overflow-x-auto mb-4">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase tracking-wider">
                        <tr><th class="text-left px-3 py-2 font-bold">Email</th><th class="text-left px-3 py-2 font-bold">Role</th><th class="text-left px-3 py-2 font-bold">Last Login</th><th class="text-right px-3 py-2 font-bold">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($staff as $member): ?>
                            <tr>
                                <td class="px-3 py-2.5 font-semibold text-slate-800"><?= e($member['email']) ?><?= (int) $member['id'] === (int) $adminUser['id'] ? ' <span class="text-[9px] text-[#00A651] font-bold">(you)</span>' : '' ?></td>
                                <td class="px-3 py-2.5 text-slate-600"><?= e($member['role']) ?></td>
                                <td class="px-3 py-2.5 text-[11px] text-slate-500"><?= e(format_date($member['last_login'])) ?></td>
                                <td class="px-3 py-2.5 text-right">
                                    <?php if ((int) $member['id'] !== (int) $adminUser['id']): ?>
                                        <form action="<?= e(url('admin/settings.php')) ?>" method="post" data-confirm="Delete admin account <?= e($member['email']) ?>?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete_admin">
                                            <input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded" title="Delete admin"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-400">Current</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <details class="border-t border-slate-100 pt-3">
                <summary class="text-[11px] font-bold text-[#00A651] cursor-pointer flex items-center gap-1"><i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Add another admin account</summary>
                <form action="<?= e(url('admin/settings.php')) ?>" method="post" class="mt-3 grid grid-cols-1 sm:grid-cols-4 gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add_admin">
                    <input type="email" name="email" required placeholder="staff@example.com" class="px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651] sm:col-span-2">
                    <input type="password" name="password" required minlength="8" placeholder="Strong password (8+)" class="px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
                    <div class="flex gap-2">
                        <select name="role" class="flex-1 px-2 py-2 text-xs border border-slate-300 rounded-lg bg-white">
                            <option value="StoreManager">StoreManager</option>
                            <option value="SuperAdmin">SuperAdmin</option>
                        </select>
                        <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold">Add</button>
                    </div>
                </form>
            </details>
        </div>
    </div>

    <!-- Password -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:sticky lg:top-36">
        <h2 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 mb-3 flex items-center gap-1.5">
            <i data-lucide="lock" class="w-4 h-4 text-[#00A651]"></i> Change My Password
        </h2>

        <form action="<?= e(url('admin/settings.php')) ?>" method="post" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="change_password">

            <div>
                <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1">Current Password</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>
            <div>
                <label for="new_password" class="block text-xs font-semibold text-slate-700 mb-1">New Password (8+ chars)</label>
                <input id="new_password" name="new_password" type="password" required minlength="8" autocomplete="new-password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>
            <div>
                <label for="confirm_password" class="block text-xs font-semibold text-slate-700 mb-1">Confirm New Password</label>
                <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password" class="w-full px-3 py-2 text-xs border border-slate-300 rounded-lg focus:outline-[#00A651]">
            </div>

            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold flex items-center justify-center gap-1.5">
                <i data-lucide="key-round" class="w-3.5 h-3.5"></i> Update Password
            </button>

            <p class="text-[10px] text-slate-500 leading-relaxed pt-1">
                Change the default <code class="font-mono">Admin@12345</code> password immediately. Passwords are stored as secure bcrypt hashes.
            </p>
        </form>
    </div>
</div>
