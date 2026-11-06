<?php
/** Admin layout footer. */
?>
</main>

<footer class="border-t border-slate-200 bg-white print-hide">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
        <span>&copy; <?= date('Y') ?> <?= e(setting('store_name', 'PakStore')) ?> Admin Panel</span>
        <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#00A651]"></i> Secure session &middot; Prepared statements &middot; CSRF protected</span>
    </div>
</footer>

<script src="<?= e(url('assets/js/main.js')) ?>"></script>
</body>
</html>
