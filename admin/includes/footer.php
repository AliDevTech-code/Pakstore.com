<?php
/** Admin layout footer. */
?>
    </main>

    <footer class="border-t border-slate-200 bg-white print-hide">
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
            <span>&copy; <?= date('Y') ?> <?= e(setting('store_name', 'PakStore')) ?> Admin Panel</span>
            <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#00A651]"></i> Secure session &middot; Prepared statements &middot; CSRF protected</span>
        </div>
    </footer>
</div>

<script>
(function () {
    var sidebar = document.getElementById('admin-sidebar');
    var overlay = document.getElementById('admin-sidebar-overlay');
    if (!sidebar) return;

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
        document.documentElement.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
        document.documentElement.style.overflow = '';
    }

    document.querySelectorAll('[data-admin-open]').forEach(function (btn) {
        btn.addEventListener('click', openSidebar);
    });
    document.querySelectorAll('[data-admin-close]').forEach(function (btn) {
        btn.addEventListener('click', closeSidebar);
    });
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) closeSidebar();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });
})();
</script>

<script src="<?= e(url('assets/js/main.js') . '?v=2') ?>"></script>
</body>
</html>
