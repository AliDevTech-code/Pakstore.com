/* PakStore - progressive enhancement only. The store works without JS. */
(function () {
  'use strict';

  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

  /* ---------------- Drawers ---------------- */
  window.openDrawer = function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.hidden = false;
    document.documentElement.style.overflow = 'hidden';
  };

  window.closeDrawer = function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    el.hidden = true;
    document.documentElement.style.overflow = '';
  };

  document.addEventListener('click', function (event) {
    var opener = event.target.closest('[data-open-drawer]');
    if (opener) {
      event.preventDefault();
      openDrawer(opener.getAttribute('data-open-drawer'));
      return;
    }
    var closer = event.target.closest('[data-close-drawer]');
    if (closer) {
      event.preventDefault();
      closeDrawer(closer.getAttribute('data-close-drawer'));
      return;
    }
    var backdrop = event.target.closest('[data-drawer-backdrop]');
    if (backdrop) {
      var drawer = backdrop.parentElement;
      if (drawer && drawer.hasAttribute('data-drawer')) {
        closeDrawer(drawer.id);
      }
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      qsa('[data-drawer]:not([hidden])').forEach(function (el) { closeDrawer(el.id); });
    }
  });

  /* ---------------- Hero slider ---------------- */
  function initSlider() {
    var root = qs('[data-hero-slider]');
    if (!root) return;
    var slides = qsa('.hero-slide', root);
    var dots = qsa('[data-hero-dot]', root);
    if (slides.length <= 1) return;

    var index = 0;
    var timer = null;

    function show(next) {
      index = (next + slides.length) % slides.length;
      slides.forEach(function (slide, i) { slide.classList.toggle('is-active', i === index); });
      dots.forEach(function (dot, i) {
        dot.classList.toggle('bg-[#FF6B00]', i === index);
        dot.classList.toggle('w-7', i === index);
        dot.classList.toggle('bg-white/40', i !== index);
        dot.classList.toggle('w-2', i !== index);
      });
    }

    function start() {
      stop();
      timer = setInterval(function () { show(index + 1); }, 4500);
    }
    function stop() { if (timer) clearInterval(timer); timer = null; }

    qsa('[data-hero-prev]', root).forEach(function (btn) {
      btn.addEventListener('click', function () { show(index - 1); start(); });
    });
    qsa('[data-hero-next]', root).forEach(function (btn) {
      btn.addEventListener('click', function () { show(index + 1); start(); });
    });
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { show(i); start(); });
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    show(0);
    start();
  }

  /* ---------------- Quantity steppers ---------------- */
  function initQuantitySteppers() {
    qsa('[data-qty]').forEach(function (wrap) {
      var input = qs('input[type="number"]', wrap);
      if (!input) return;
      qsa('[data-qty-step]', wrap).forEach(function (btn) {
        btn.addEventListener('click', function () {
          var step = parseInt(btn.getAttribute('data-qty-step'), 10) || 1;
          var min = parseInt(input.getAttribute('min') || '1', 10);
          var max = parseInt(input.getAttribute('max') || '9999', 10);
          var value = parseInt(input.value || '1', 10) + step;
          if (isNaN(value)) value = 1;
          value = Math.max(min, Math.min(max, value));
          input.value = value;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    });
  }

  /* ---------------- Auto submit quantity forms ---------------- */
  function initAutoSubmit() {
    qsa('form[data-auto-submit]').forEach(function (form) {
      var timer = null;
      qsa('input[type="number"]', form).forEach(function (input) {
        input.addEventListener('change', function () {
          clearTimeout(timer);
          timer = setTimeout(function () { form.submit(); }, 350);
        });
      });
      qsa('select[data-autosubmit]', form).forEach(function (select) {
        select.addEventListener('change', function () { form.submit(); });
      });
    });
  }

  /* ---------------- Product gallery ---------------- */
  function initGallery() {
    var main = qs('[data-gallery-main]');
    if (!main) return;
    qsa('[data-gallery-thumb]').forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        var src = thumb.getAttribute('data-gallery-thumb');
        main.src = src;
        qsa('[data-gallery-thumb]').forEach(function (t) {
          t.classList.remove('border-[#00A651]', 'ring-2', 'ring-emerald-500/30');
          t.classList.add('border-slate-200', 'opacity-75');
        });
        thumb.classList.add('border-[#00A651]', 'ring-2', 'ring-emerald-500/30');
        thumb.classList.remove('border-slate-200', 'opacity-75');
      });
    });
  }

  /* ---------------- Confetti-free order success ----------------
     A tiny CSS burst is used instead of a third-party library. */
  function initSuccessBurst() {
    var target = qs('[data-success-burst]');
    if (!target) return;
    for (var i = 0; i < 28; i++) {
      var dot = document.createElement('span');
      dot.style.cssText = 'position:absolute;width:7px;height:7px;border-radius:99px;left:50%;top:50%;pointer-events:none;background:' +
        (i % 2 ? '#00A651' : '#FF6B00') + ';opacity:.9;transition:transform .9s ease-out,opacity .9s ease-out;';
      target.appendChild(dot);
      (function (node, angle) {
        requestAnimationFrame(function () {
          var distance = 90 + Math.random() * 120;
          node.style.transform = 'translate(' + Math.cos(angle) * distance + 'px,' + Math.sin(angle) * distance + 'px)';
          node.style.opacity = '0';
        });
      })(dot, (Math.PI * 2 * i) / 28);
    }
  }

  /* ---------------- Copy link ---------------- */
  function initCopyButtons() {
    qsa('[data-copy-link]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var text = btn.getAttribute('data-copy-link') || window.location.href;
        var done = function () {
          var label = btn.querySelector('[data-copy-label]');
          if (label) {
            var old = label.textContent;
            label.textContent = 'Link Copied!';
            setTimeout(function () { label.textContent = old; }, 1800);
          }
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done).catch(done);
        } else {
          var area = document.createElement('textarea');
          area.value = text;
          document.body.appendChild(area);
          area.select();
          try { document.execCommand('copy'); } catch (e) {}
          document.body.removeChild(area);
          done();
        }
      });
    });
  }

  /* ---------------- Confirm dangerous actions ---------------- */
  function initConfirms() {
    qsa('[data-confirm]').forEach(function (el) {
      el.addEventListener('click', function (event) {
        if (!window.confirm(el.getAttribute('data-confirm'))) {
          event.preventDefault();
          event.stopPropagation();
        }
      });
    });
  }

  /* ---------------- Product description read more ---------------- */
  function initDescriptionToggle() {
    qsa('[data-desc-toggle]').forEach(function (btn) {
      var target = document.getElementById(btn.getAttribute('data-desc-toggle'));
      if (!target) return;
      var label = btn.querySelector('[data-desc-label]');
      var icon = btn.querySelector('i');

      btn.addEventListener('click', function () {
        var isOpen = target.style.maxHeight === 'none';
        if (isOpen) {
          target.style.maxHeight = '6.5rem';
          target.classList.add('overflow-hidden');
          if (label) label.textContent = 'Read more';
          if (icon) icon.classList.remove('rotate-180');
        } else {
          target.style.maxHeight = 'none';
          target.classList.remove('overflow-hidden');
          if (label) label.textContent = 'Read less';
          if (icon) icon.classList.add('rotate-180');
        }
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initSlider();
    initQuantitySteppers();
    initAutoSubmit();
    initGallery();
    initSuccessBurst();
    initCopyButtons();
    initConfirms();
    initDescriptionToggle();

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  });
})();
