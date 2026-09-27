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
    initSidebarDrawer();
    initDropdowns();
    initModals();
    initConfirms();
    initTabs();
    initPasswordToggles();
    initPhotoThumbs();
    initPhotoUploader();
    initChat();
    initDiscoverFilters();
    initFilterDrawer();
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

  /* ---------------------------- sidebar drawer ----------------------------- */
  function initSidebarDrawer() {
    const toggle = $('[data-sidebar-toggle]');
    const sidebar = $('#memberSidebar');
    if (!toggle || !sidebar) return;

    const setOpen = (open) => {
      document.body.classList.toggle('sidebar-open', open);
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.classList.toggle('active', open);
    };

    toggle.addEventListener('click', () => {
      setOpen(!document.body.classList.contains('sidebar-open'));
    });

    $$('[data-sidebar-close]', sidebar).forEach((btn) => {
      btn.addEventListener('click', () => setOpen(false));
    });

    /* Following a link loads a new page, so close before the navigation. */
    $$('.side-item', sidebar).forEach((link) => {
      link.addEventListener('click', () => setOpen(false));
    });

    /* The backdrop is a body::after pseudo-element, so a click on it lands on
       the body itself. */
    document.addEventListener('click', (e) => {
      if (e.target === document.body) setOpen(false);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 991) setOpen(false);
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

  /* ------------------------- photo uploader preview ------------------------- */
  /* A photo is the one upload a member gets wrong most often, and a wrong one
     only comes back as a validation message after a full page reload. The rules
     the server checks are mirrored here so the thumbnail, the file's size and
     any problem show up before the form is ever submitted. */
  const PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
  const PHOTO_MAX_KB = 4096;
  const PHOTO_MIN_EDGE = 200;
  const PHOTO_MAX_EDGE = 6000;

  function initPhotoUploader() {
    const drop = $('[data-photo-drop]');
    if (!drop) return;

    const input = $('input[type="file"]', drop.form || drop);
    const preview = $('[data-photo-preview]', drop.form);
    if (!input || !preview) return;

    const thumb = $('[data-photo-img]', preview);
    const name = $('[data-photo-name]', preview);
    const note = $('[data-photo-note]', preview);
    const clear = $('[data-photo-clear]', preview);
    const submit = $('[data-photo-submit]', drop.form);
    let objectUrl = null;

    const showBytes = (bytes) => (bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB');

    const fail = (message) => {
      note.textContent = message;
      note.classList.remove('is-warn');
      note.classList.add('is-bad');
      if (submit) submit.disabled = true;
    };

    const reset = () => {
      input.value = '';
      preview.hidden = true;
      note.classList.remove('is-warn', 'is-bad');
      if (submit) submit.disabled = false;
      if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
      }
      thumb.removeAttribute('src');
    };

    const show = (file) => {
      if (!file) return reset();

      preview.hidden = false;
      if (name) name.textContent = file.name;
      if (submit) submit.disabled = false;

      if (window.URL && URL.createObjectURL) {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        thumb.src = objectUrl;
      }

      if (PHOTO_TYPES.indexOf(file.type) === -1) {
        return fail('That file is not a JPG, PNG or WebP image.');
      }
      if (file.size > PHOTO_MAX_KB * 1024) {
        return fail('That photo is ' + showBytes(file.size) + '. It has to be under 4 MB.');
      }

      note.classList.remove('is-bad');
      note.classList.add('is-warn');
      note.textContent = showBytes(file.size) + ' · checking dimensions…';

      // The pixel rules need the image itself, so they land after it decodes.
      const probe = new Image();
      probe.onload = () => {
        const w = probe.naturalWidth;
        const h = probe.naturalHeight;
        if (Math.min(w, h) < PHOTO_MIN_EDGE) {
          return fail('That photo is only ' + w + '×' + h + '. It needs to be at least 200×200.');
        }
        if (Math.max(w, h) > PHOTO_MAX_EDGE) {
          return fail('That photo is ' + w + '×' + h + '. The largest we accept is 6000×6000.');
        }
        note.classList.remove('is-warn');
        note.textContent = showBytes(file.size) + ' · ' + w + ' × ' + h + ' · ready to upload';
      };
      probe.onerror = () => {
        note.classList.remove('is-warn');
        note.textContent = showBytes(file.size) + ' · ready to upload';
      };
      probe.src = objectUrl || probe.src;
    };

    input.addEventListener('change', () => show(input.files[0]));
    if (clear) clear.addEventListener('click', reset);

    // A drop on the label has to reach the input, which a browser will not do
    // on its own once the input is visually hidden.
    ['dragenter', 'dragover'].forEach((type) => {
      drop.addEventListener(type, (e) => {
        e.preventDefault();
        drop.classList.add('is-over');
      });
    });
    ['dragleave', 'dragend'].forEach((type) => {
      drop.addEventListener(type, () => drop.classList.remove('is-over'));
    });
    drop.addEventListener('drop', (e) => {
      e.preventDefault();
      drop.classList.remove('is-over');
      const file = e.dataTransfer && e.dataTransfer.files[0];
      if (!file) return;
      try {
        input.files = e.dataTransfer.files;
      } catch (err) {
        // Older Safari refuses a dropped file, so the picker is the way in.
        reset();
        note.textContent = 'Your browser will not accept a dropped file. Choose one instead.';
        note.classList.add('is-warn');
        return;
      }
      show(file);
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
        // A screen reader hears the button's name, so it has to change with the state.
        const nextLabel = show ? 'Hide password' : 'Show password';
        btn.setAttribute('aria-label', nextLabel);
        btn.setAttribute('aria-pressed', show ? 'true' : 'false');
        if (show) input.focus();
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

  /* ---------------------------- filter sheet (mobile) ---------------------------- */
  function initFilterDrawer() {
    const toggle = $('[data-filter-toggle]');
    const closers = $$('[data-filter-close]');
    if (!toggle && !closers.length) return;

    const setOpen = (open) => document.body.classList.toggle('filters-open', open);

    if (toggle) {
      toggle.setAttribute('aria-expanded', 'false');
      toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const open = !document.body.classList.contains('filters-open');
        setOpen(open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    closers.forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        setOpen(false);
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
      });
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') setOpen(false);
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