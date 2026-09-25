/* ==========================================================================
   Jibon Sathi — Frontend interactions
   ========================================================================== */
(function () {
  'use strict';

  const $ = (sel, root) => (root || document).querySelector(sel);
  const $$ = (sel, root) => Array.from((root || document).querySelectorAll(sel));

  document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initNav();
    initDropdowns();
    initModals();
    initConfirms();
    initTabs();
    initPasswordToggles();
    initPhotoThumbs();
    initChat();
    initDiscoverFilters();
    initInterestActions();
    initNotifBell();
    initAutoClose();
    initCharacterCounters();
    initScrollReveal();
    initNavbarScroll();
    initCounters();
  });

  /* ------------------------------ animated counters ------------------------------ */
  function initCounters() {
    const els = $$('[data-counter]');
    if (!els.length) return;

    const reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const run = (el) => {
      const target = parseInt(el.dataset.counter, 10);
      if (!Number.isFinite(target)) return;
      const suffix = el.dataset.suffix || '';

      if (reduced) { el.textContent = target.toLocaleString() + suffix; return; }

      const duration = 1400;
      const start = performance.now();
      const tick = (now) => {
        const p = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * eased).toLocaleString() + suffix;
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };

    if (!('IntersectionObserver' in window)) { els.forEach(run); return; }

    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          run(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    els.forEach((el) => io.observe(el));
  }

  /* ------------------------------ scroll reveal ------------------------------ */
  function initScrollReveal() {
    // Cancels the <head> failsafe so reveal-ready is not removed again.
    document.documentElement.setAttribute('data-reveal-init', '1');

    const els = $$('.reveal');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach((el) => el.classList.add('revealed'));
      return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach((el) => io.observe(el));
  }

  /* ----------------------------- navbar scroll ------------------------------ */
  function initNavbarScroll() {
    const nav = $('.navbar');
    if (!nav) return;
    const update = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  /* ---------------------------------- toasts --------------------------------- */
  function initToasts() {
    $$('[data-toast]').forEach((el) => {
      setTimeout(() => {
        el.classList.add('leaving');
        setTimeout(() => el.remove(), 320);
      }, parseInt(el.dataset.toast, 10) || 4200);
    });
  }

  function flashSuccess(msg) {
    const wrap = ensureToastWrap();
    const t = document.createElement('div');
    t.className = 'toast success';
    t.innerHTML = '<i class="fas fa-circle-check"></i><div><p>' + msg + '</p></div>';
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('leaving'); setTimeout(() => t.remove(), 320); }, 3600);
  }

  function flashError(msg) {
    const wrap = ensureToastWrap();
    const t = document.createElement('div');
    t.className = 'toast error';
    t.innerHTML = '<i class="fas fa-circle-exclamation"></i><div><p>' + msg + '</p></div>';
    wrap.appendChild(t);
    setTimeout(() => { t.classList.add('leaving'); setTimeout(() => t.remove(), 4200); }, 4200);
  }

  function ensureToastWrap() {
    let wrap = $('.toast-wrap');
    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'toast-wrap';
      document.body.appendChild(wrap);
    }
    return wrap;
  }

  /* ---------------------------------- nav ----------------------------------- */
  function initNav() {
    const toggle = $('[data-nav-toggle]');
    if (!toggle) return;
    toggle.addEventListener('click', () => {
      document.body.classList.toggle('nav-open');
      const open = document.body.classList.contains('nav-open');
      toggle.querySelector('i').className = open ? 'fas fa-xmark' : 'fas fa-bars';
    });
  }

  /* -------------------------------- dropdowns ------------------------------- */
  function initDropdowns() {
    $$('[data-dropdown]').forEach((dd) => {
      const trigger = $('[data-dropdown-toggle]', dd);
      const menu = $('.dropdown-menu', dd);
      if (!trigger || !menu) return;

      trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeDropdowns();
        dd.classList.toggle('open');
      });

      menu.querySelectorAll('a, button').forEach((el) => {
        el.addEventListener('click', (e) => e.stopPropagation());
      });
    });

    document.addEventListener('click', closeDropdowns);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeDropdowns();
    });
  }

  function closeDropdowns() {
    $$('.dropdown.open').forEach((d) => d.classList.remove('open'));
  }

  /* --------------------------------- modals --------------------------------- */
  function initModals() {
    $$('[data-modal-open]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const target = document.getElementById(btn.dataset.modalOpen);
        if (target) openModal(target);
      });
    });

    $$('.modal').forEach((m) => {
      const closeBtn = $('[data-modal-close]', m);
      if (closeBtn) closeBtn.addEventListener('click', () => closeModal(m));
      $('.modal-backdrop', m).addEventListener('click', () => closeModal(m));
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') $$('.modal.open').forEach(closeModal);
    });
  }

  function openModal(m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; m.setAttribute('aria-hidden', 'false'); }
  function closeModal(m) { m.classList.remove('open'); document.body.style.overflow = ''; m.setAttribute('aria-hidden', 'true'); }
  window.JibonSathi = window.JibonSathi || {};
  window.JibonSathi.openModal = openModal;
  window.JibonSathi.closeModal = closeModal;
  window.JibonSathi.flash = { success: flashSuccess, error: flashError };

  /* ------------------------------ confirm dialogs --------------------------- */
  function initConfirms() {
    $$('form[data-confirm]').forEach((form) => {
      form.addEventListener('submit', (e) => {
        if (!confirm(form.dataset.confirm || 'Are you sure you want to do this?')) {
          e.preventDefault();
        }
      });
    });
  }

  /* ---------------------------------- tabs ---------------------------------- */
  function initTabs() {
    $$('[data-tab]').forEach((tab) => {
      tab.addEventListener('click', (e) => {
        const group = tab.closest('[data-tabs]');
        if (!group) return;
        e.preventDefault();
        $$('[data-tab]', group).forEach((t) => t.classList.remove('active'));
        tab.classList.add('active');
        const url = new URL(tab.href);
        url.searchParams.set('tab', tab.dataset.tab);
        window.location.href = url.toString();
      });
    });
  }

  /* ---------------------------- password toggles ---------------------------- */
  function initPasswordToggles() {
    $$('[data-password-toggle]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.passwordToggle);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
      });
    });
  }

  /* ------------------------------- photo thumbs ------------------------------ */
  function initPhotoThumbs() {
    const main = $('[data-photo-main]');
    if (!main) return;
    $$('.ph-thumb').forEach((th) => {
      th.addEventListener('click', () => {
        main.src = th.dataset.full;
        $$('.ph-thumb').forEach((t) => t.classList.remove('active'));
        th.classList.add('active');
        if (th.dataset.caption) {
          const cap = $('[data-photo-caption]');
          if (cap) cap.textContent = th.dataset.caption;
        }
      });
    });
  }

  /* ---------------------------------- chat ---------------------------------- */
  const threadIds = [];
  function initChat() {
    const form = $('.chat-composer form');
    const list = $('.chat-messages');
    if (!form || !list || list.dataset.threadId === undefined) return;

    const threadId = list.dataset.threadId;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const textarea = $('textarea[name="body"]', form);
      const body = textarea.value.trim();
      if (!body) return;

      const submitBtn = $('button[type="submit"]', form);
      submitBtn.disabled = true;

      try {
        const resp = await fetch(form.action, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
          body: new URLSearchParams(new FormData(form)),
        });

        if (!resp.ok) {
          const data = await resp.json().catch(() => ({}));
          throw new Error(data.message || 'Could not send the message.');
        }

        const data = await resp.json();
        appendBubble(list, data.message.body, data.message.sent_at, true);
        textarea.value = '';
        list.scrollTop = list.scrollHeight;
      } catch (err) {
        flashError(err.message);
      } finally {
        submitBtn.disabled = false;
        textarea.focus();
      }
    });

    threadIds.push(threadId);
    setInterval(() => { if (threadIds.includes(list.dataset.threadId)) markRead(); }, 8000);
  }

  function appendBubble(list, body, time, mine) {
    const wrap = document.createElement('div');
    wrap.className = 'chat-day-block';
    const dayLbl = document.createElement('div');
    dayLbl.className = 'chat-day';
    dayLbl.textContent = 'Today';
    wrap.appendChild(dayLbl);
    const div = document.createElement('div');
    div.className = mine ? 'bubble mine' : 'bubble theirs';
    div.innerHTML = '<span>' + escapeHtml(body) + '</span><span class="b-time">' + escapeHtml(time) + '</span>';
    wrap.appendChild(div);
    list.appendChild(wrap);
  }

  function markRead() {
    // Passive: unread state is refreshed on full page loads.
  }

  /* ----------------------------- discover filters ---------------------------- */
  function initDiscoverFilters() {
    const form = $('[data-discover-filter]');
    const wrap = $('[data-results]');
    if (!form || !wrap) return;

    let timeout = null;
    const run = () => {
      form.classList.add('is-loading');
      const body = new URLSearchParams(new FormData(form));
      body.set('partial', '1');
      fetch(form.getAttribute('action') + '/?' + body.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then((r) => r.text())
        .then((html) => {
          wrap.innerHTML = html;
          const count = $('[data-result-count]');
          if (count) {
            const fresh = $('data-result-count');
            if (fresh) count.textContent = fresh.textContent;
          }
          window.history.replaceState({}, '', form.getAttribute('action') + '?' + body.toString());
          const qs = new URLSearchParams(body); qs.delete('partial');
          window.history.replaceState({}, '', form.getAttribute('action') + '?' + qs.toString());
        })
        .catch(() => flashError('Could not refresh results.'))
        .finally(() => form.classList.remove('is-loading'));
    };

    form.addEventListener('submit', (e) => { e.preventDefault(); run(); });
    form.addEventListener('change', (e) => {
      if (e.target.matches('select, input[type="checkbox"], input[type="radio"]')) {
        clearTimeout(timeout);
        timeout = setTimeout(run, 350);
      }
    });
    $$('input[type="text"], input[type="number"], input[type="date"]', form).forEach((el) => {
      el.addEventListener('input', () => { clearTimeout(timeout); timeout = setTimeout(run, 650); });
    });
  }

  /* ----------------------------- interest actions ---------------------------- */
  function initInterestActions() {
    $$('[data-interest-action]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const endpoint = btn.dataset.interestAction;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = endpoint;
        const token = $('meta[name="csrf-token"]');
        if (token) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = '_token';
          input.value = token.content;
          form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
      });
    });
  }

  /* ------------------------------ notification bell -------------------------- */
  function initNotifBell() {
    const dots = $$('.notif-dot[data-unread-url]');
    dots.forEach((dot) => {
      setInterval(() => {
        fetch(dot.dataset.unreadUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then((r) => r.json())
          .then((data) => {
            if (typeof data.unread === 'number') {
              if (data.unread > 0) { dot.textContent = data.unread > 99 ? '99+' : data.unread; dot.style.display = 'flex'; }
              else { dot.style.display = 'none'; }
            }
          })
          .catch(() => {});
      }, 45000);
    });
  }

  /* ------------------------------ auto close alerts -------------------------- */
  function initAutoClose() {
    $$('.alert[data-auto-close]').forEach((el) => {
      const btn = $('.alert-close', el);
      if (btn) btn.addEventListener('click', () => el.remove());
      setTimeout(() => el.remove(), 6000);
    });
  }

  /* --------------------------- character counters ---------------------------- */
  function initCharacterCounters() {
    $$('[data-count]').forEach((el) => {
      const target = $('[data-count-target]', el.closest('form') || el.parentElement);
      const max = parseInt(el.dataset.count, 10);
      const sync = () => { if (target) target.textContent = el.value.length + ' / ' + max; };
      el.addEventListener('input', sync);
      sync();
    });
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();