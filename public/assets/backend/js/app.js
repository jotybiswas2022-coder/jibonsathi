/* ==========================================================================
   Jibon Sathi Admin — backend interactions
   ========================================================================== */
(function () {
  'use strict';

  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initTopbarHeight();
    initSidebar();
    initDropdowns();
    initConfirms();
    initAutoClose();
    initCharts();
    initTabs();
    initSettings();
  });

  function initToasts() {
    $$('[data-toast]').forEach((el) => {
      setTimeout(() => { el.classList.add('leaving'); setTimeout(() => el.remove(), 320); }, parseInt(el.dataset.toast, 10) || 4200);
    });
  }

  /* Sticky offsets elsewhere in the CSS are derived from the topbar height, which
     is only final once the webfont has loaded. */
  function initTopbarHeight() {
    const bar = $('.admin-topbar');
    const layout = $('.admin-layout');
    if (!bar || !layout) return;
    const publish = () => layout.style.setProperty('--topbar-h', bar.offsetHeight + 'px');
    publish();
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(publish).catch(() => {});
    window.addEventListener('resize', debounce(publish, 150));
  }

  function debounce(fn, wait) {
    let t;
    return function () {
      const args = arguments;
      clearTimeout(t);
      t = setTimeout(() => fn.apply(null, args), wait);
    };
  }

  function initSidebar() {
    const btn = $('[data-sidebar-toggle]');
    const layout = $('.admin-layout');
    if (!btn || !layout) return;

    const setOpen = (open) => {
      layout.classList.toggle('sidebar-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    btn.setAttribute('aria-expanded', 'false');
    btn.addEventListener('click', () => setOpen(!layout.classList.contains('sidebar-open')));
    document.addEventListener('click', (e) => {
      if (layout.classList.contains('sidebar-open') && !e.target.closest('.admin-sidebar, [data-sidebar-toggle]')) {
        setOpen(false);
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && layout.classList.contains('sidebar-open')) setOpen(false);
    });
  }

  function initDropdowns() { /* reserved */ }

  /* ------------------------- confirms (SweetAlert2) ------------------------ */
  /* One delegated listener for the whole panel. A single form can carry several
     confirm buttons, so the message has to come from the button that was used
     (event.submitter) rather than from every button on the form. */
  function initConfirms() {
    let reSubmitting = false;

    /* Any data-confirm* attribute turns an element into a confirm trigger, so a
       button may carry just a title, or a title plus a longer explanation. */
    const hasConfirm = (el) => !!el && Array.from(el.attributes).some((a) => a.name.startsWith('data-confirm'));

    const optionsFor = (el) => {
      const title = el.getAttribute('data-confirm-title') || 'Are you sure?';
      const text = el.getAttribute('data-confirm') || '';
      const confirmText = el.getAttribute('data-confirm-ok') || 'Yes, continue';
      const cancelText = el.getAttribute('data-confirm-cancel') || 'Cancel';
      const icon = el.getAttribute('data-confirm-icon') || 'warning';
      return {
        title: title,
        text: text || undefined,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        confirmButtonColor: el.getAttribute('data-confirm-color') || undefined,
        focusCancel: el.getAttribute('data-confirm-focus-cancel') !== null,
        reverseButtons: true,
      };
    };

    const nativeConfirm = (el) => window.confirm(
      (el.getAttribute('data-confirm-title') || 'Are you sure?') +
      (el.getAttribute('data-confirm') ? '\n\n' + el.getAttribute('data-confirm') : '')
    );

    const send = (form, submitter) => {
      reSubmitting = true;
      if (submitter && typeof form.requestSubmit === 'function') {
        form.requestSubmit(submitter);
      } else {
        /* Older browsers drop the submitter, so carry its name/value over. */
        if (submitter && submitter.name) {
          const hidden = document.createElement('input');
          hidden.type = 'hidden';
          hidden.name = submitter.name;
          hidden.value = submitter.value;
          form.appendChild(hidden);
        }
        form.submit();
      }
      setTimeout(() => { reSubmitting = false; }, 0);
    };

    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;

      const submitter = e.submitter || form.querySelector('[data-confirm-submit]:focus');
      const el = hasConfirm(submitter) ? submitter
        : (hasConfirm(form) ? form : null);
      if (!el) return;
      if (reSubmitting) return;

      e.preventDefault();
      if (typeof window.Swal === 'undefined') {
        if (nativeConfirm(el)) send(form, submitter);
        return;
      }
      window.Swal.fire(optionsFor(el)).then((result) => {
        if (result.isConfirmed) send(form, submitter);
      });
    });

    /* Submit buttons outside a form (rare) still get a guard. */
    let reClicking = false;
    $$('[data-confirm-submit], [data-confirm-title]').forEach((btn) => {
      if (btn.closest('form')) return;
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (reClicking) return;
        if (typeof window.Swal === 'undefined') {
          if (nativeConfirm(btn)) { reClicking = true; btn.click(); setTimeout(() => { reClicking = false; }, 0); }
          return;
        }
        window.Swal.fire(optionsFor(btn)).then((result) => {
          if (!result.isConfirmed) return;
          reClicking = true;
          btn.click();
          setTimeout(() => { reClicking = false; }, 0);
        });
      });
    });
  }

  function initAutoClose() {
    $$('.alert[data-auto-close]').forEach((el) => setTimeout(() => el.remove(), 6000));
  }

  /* ------------------------------ mini charts ------------------------------ */
  function initCharts() {
    $$('canvas[data-chart]').forEach((canvas) => {
      const conf = JSON.parse(canvas.dataset.chart);
      drawChart(canvas, conf);
    });
  }

  function drawChart(canvas, conf) {
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();
    const w = Math.max(rect.width, 160);
    const h = conf.height || 200 || rect.height;
    canvas.width = w * dpr;
    canvas.height = h * dpr;
    canvas.style.width = w + 'px';
    canvas.style.height = h + 'px';
    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);

    const labels = conf.labels || [];
    const datasets = conf.datasets || [];
    const padding = { top: 24, right: 12, bottom: 28, left: 40 };
    const iw = w - padding.left - padding.right;
    const ih = h - padding.top - padding.bottom;

    let maxVal = 0;
    datasets.forEach((ds) => (ds.data || []).forEach((v) => { if (v > maxVal) maxVal = v; }));
    if (maxVal === 0) maxVal = 1;

    // Grid lines
    ctx.strokeStyle = '#EFE7E1';
    ctx.fillStyle = '#6B7280';
    ctx.font = '11px Inter, sans-serif';
    ctx.textAlign = 'right';
    const steps = 4;
    for (let i = 0; i <= steps; i++) {
      const y = padding.top + ih - (ih * i) / steps;
      ctx.beginPath();
      ctx.moveTo(padding.left, y);
      ctx.lineTo(w - padding.right, y);
      ctx.stroke();
      const val = Math.round((maxVal * i) / steps);
      ctx.fillText(String(val), padding.left - 7, y + 4);
    }

    const series = datasets.map((ds) => {
      const colors = ds.color || '#8B1E3F';
      const values = ds.data || [];
      if (conf.type === 'bar' || !conf.type) {
        const bw = Math.min(iw / (values.length || 1) * 0.62, 46);
        values.forEach((value, idx) => {
          const cx = padding.left + (idx + 0.5) * (iw / Math.max(values.length, 1));
          const bh = (value / maxVal) * ih;
          const y = padding.top + ih - bh;
          ctx.fillStyle = ds.color || '#8B1E3F';
          roundRect(ctx, cx - bw / 2, y, bw, bh, 6);
          ctx.fill();
          if (ds.color2) {
            ctx.fillStyle = ds.color2;
            roundRect(ctx, cx - bw / 2, padding.top + ih - Math.min(bh, 56), bw, 6, 3);
            ctx.fill();
          }
        });
      } else {
        ctx.strokeStyle = colors;
        ctx.lineWidth = 2.5;
        ctx.lineJoin = 'round';
        ctx.beginPath();
        values.forEach((value, idx) => {
          const cx = padding.left + (idx / Math.max(values.length - 1, 1)) * iw;
          const cy = padding.top + ih - (value / maxVal) * ih;
          if (idx === 0) ctx.moveTo(cx, cy); else ctx.lineTo(cx, cy);
        });
        ctx.stroke();
        values.forEach((value, idx) => {
          const cx = padding.left + (idx / Math.max(values.length - 1, 1)) * iw;
          const cy = padding.top + ih - (value / maxVal) * ih;
          ctx.beginPath();
          ctx.arc(cx, cy, 3.4, 0, Math.PI * 2);
          ctx.fillStyle = ds.color || '#8B1E3F';
          ctx.fill();
        });
      }
      return values;
    });

    if (labels.length) {
      ctx.fillStyle = '#9AA1AB';
      ctx.font = '10.5px Inter, sans-serif';
      ctx.textAlign = 'center';
      const every = Math.ceil(labels.length / 6);
      labels.forEach((label, idx) => {
        if (idx % every === 0 || idx === labels.length - 1) {
          const cx = padding.left + (idx + (conf.type === 'bar' ? 0.5 : 0)) * (iw / Math.max(labels.length, 1));
          ctx.fillText(clip(label, 12), cx, h - 10);
        }
      });
    }
  }

  function roundRect(ctx, x, y, w, h, r) {
    if (h < 1) return;
    const rr = Math.min(r, h / 2);
    ctx.beginPath();
    ctx.moveTo(x + rr, y);
    ctx.arcTo(x + w, y, x + w, y + h, rr);
    ctx.arcTo(x + w, y + h, x, y + h, rr);
    ctx.arcTo(x, y + h, x, y, rr);
    ctx.arcTo(x, y, x + w, y, rr);
    ctx.closePath();
  }

  function clip(str, n) { str = String(str || ''); return str.length > n ? str.slice(0, n - 1) + '…' : str; }

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

  /* ============================== settings page ============================== */
  function initSettings() {
    const form = $('#settingsForm');
    if (!form) return;

    /* A validation bounce re-renders the form; bring the summary into view so the
       reason for the bounce is not left above the fold. */
    const summary = $('.alert-danger', form);
    if (summary) {
      $$('input[name], textarea[name], select[name]', form).forEach((input) => {
        if (input.classList && !input.classList.contains('sr-only') && input.type !== 'hidden') {
          input.classList.remove('error');
        }
      });
      setTimeout(() => summary.scrollIntoView({ behavior: 'smooth', block: 'start' }), 80);
    }

    const bar = $('[data-save-bar]', form);
    const barText = $('[data-sb-text]', form);
    const barIcon = $('[data-sb-icon]', form);
    const resetBtn = $('[data-reset-form]', form);

    /* ---------------------------- character counters --------------------------- */
    /* `field` is the input, `counter` the label-adjacent readout. Colour is
       driven by the ideal length rather than the hard maximum, because search
       engines truncate well before the validation limit. */
    const updateCounter = (counter, field) => {
      const max = parseInt(counter.dataset.max, 10) || 0;
      const ideal = parseInt(counter.dataset.ideal, 10) || 0;
      const used = field.value.length;
      counter.textContent = used + ' / ' + max;
      counter.classList.remove('good', 'warn', 'over');
      if (used > max) counter.classList.add('over');
      else if (ideal && used > ideal) counter.classList.add('warn');
      else if (ideal && used > 0) counter.classList.add('good');
    };

    const counters = $$('[data-counter]', form)
      .map((counter) => ({ counter: counter, field: form.elements[counter.getAttribute('for')] }))
      .filter((pair) => pair.field);

    counters.forEach((pair) => {
      updateCounter(pair.counter, pair.field);
      pair.field.addEventListener('input', () => updateCounter(pair.counter, pair.field));
    });

    /* ------------------------------ live previews ----------------------------- */
    const siteName = form.elements.site_name;
    const tagline = form.elements.tagline;
    const seoTitle = form.elements.seo_title;
    const seoDesc = form.elements.seo_description;
    const heroHeadline = form.elements.hero_headline;
    const heroSub = form.elements.hero_subheading;

    const setText = (selector, value, fallback) => {
      const el = $(selector);
      if (el) el.textContent = value.trim() || fallback;
    };

    const paintPreviews = () => {
      setText('[data-serp-title]', seoTitle.value, 'Your page title');
      setText('[data-serp-desc]', seoDesc.value, 'Add a meta description to control the grey snippet Google shows here.');
      setText('[data-hero-brand]', siteName.value, 'Jibon Sathi');
      setText('[data-hero-headline]', heroHeadline.value, 'Find the life you were meant for');
      setText('[data-hero-sub]', heroSub.value, 'Tell us about yourself and let verified matches come to you.');
      setText('[data-hero-tagline]', tagline.value, '');
      setText('[data-serp-url]', siteName.value, config_app_name());
    };

    [siteName, tagline, seoTitle, seoDesc, heroHeadline, heroSub]
      .filter(Boolean)
      .forEach((el) => el.addEventListener('input', paintPreviews));
    paintPreviews();

    /* ------------------------------ file pickers ------------------------------ */
    $$('[data-preview]', form).forEach((input) => {
      const thumb = document.getElementById(input.dataset.preview);
      const label = $('[data-uploader="' + input.id + '"]', form);
      const name = $('[data-file-name="' + input.id + '"]', form);

      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;

        if (name) name.textContent = file.name + ' · ' + Math.max(1, Math.round(file.size / 1024)) + ' KB';
        if (label) label.classList.add('has-file');

        if (thumb && file.type.indexOf('image') === 0) {
          const reader = new FileReader();
          reader.onload = (e) => { thumb.innerHTML = '<img alt="" src="' + e.target.result + '">'; };
          reader.readAsDataURL(file);
        }
      });
    });

    /* Clear buttons on the social URL fields, shown only while there is a value. */
    const paintClear = (field) => {
      const btn = $('[data-clear="' + field.id + '"]', form);
      if (btn) btn.hidden = field.value.trim() === '';
    };

    $$('[data-clearable]', form).forEach((field) => {
      paintClear(field);
      field.addEventListener('input', () => paintClear(field));
    });

    $$('[data-clear]', form).forEach((btn) => {
      btn.addEventListener('click', () => {
        const field = form.elements[btn.dataset.clear];
        if (!field) return;
        field.value = '';
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.focus();
      });
    });

    /* ----------------------- unsaved-changes + save bar ----------------------- */
    /* Compared against the values the page was rendered with, so typing and then
       undoing a change is correctly reported as clean again. */
    const pairs = $$('input[name], textarea[name], select[name]', form)
      .filter((input) => {
        if (input.name === '_method' || input.name === '_token') return false;
        /* A file input holds no value; compare against whatever it starts with. */
        if (input.type === 'file') return false;
        return true;
      })
      .map((input) => ({ input: input, initial: input.value }));

    const fileInputs = $$('input[type=file][name]', form);
    const fileState = () => fileInputs
      .map((f) => (f.files && f.files.length ? f.files[0].name + ':' + f.files[0].size : ''))
      .join('\u0000');
    const initialFiles = fileState();

    const snapshot = () => pairs.map((p) => p.input.value).join('\u0000') + '\u0001' + fileState();

    const initialState = snapshot();
    const paintBar = () => {
      const now = snapshot();
      if (!bar) return;
      bar.classList.toggle('is-dirty', now !== initialState);
      if (barText) barText.textContent = now !== initialState ? 'You have unsaved changes' : 'All changes saved';
      if (barIcon) barIcon.className = now !== initialState ? 'fas fa-triangle-exclamation' : 'fas fa-circle-check';
      if (resetBtn) resetBtn.hidden = now === initialState;
    };

    let dirty = false;
    const markDirty = () => { if (!dirty) { dirty = true; paintBar(); } };
    const markClean = () => { if (dirty) { dirty = false; paintBar(); } };

    form.addEventListener('input', (e) => {
      if (e.target === resetBtn) return;
      markDirty();
    });
    form.addEventListener('change', (e) => { if (e.target.type !== 'file') markDirty(); });

    /* Discarding edits uses the same SweetAlert2 dialog as every other confirm
       in the panel, rather than a native confirm() that would break the look. */
    const discardAll = () => {
      form.reset();
      dirty = false;
      paintBar();
      paintPreviews();
      counters.forEach((pair) => updateCounter(pair.counter, pair.field));
      $$('[data-clearable]', form).forEach(paintClear);
      $$('[data-uploader]', form).forEach((label) => label.classList.remove('has-file'));
    };

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        if (typeof window.Swal === 'undefined') {
          if (window.confirm('Discard every unsaved change on this page?')) discardAll();
          return;
        }
        window.Swal.fire({
          title: 'Discard your changes?',
          text: 'Every edit on this page goes back to the last saved version.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Discard changes',
          cancelButtonText: 'Keep editing',
          confirmButtonColor: '#DC2626',
          focusCancel: true,
          reverseButtons: true,
        }).then((result) => { if (result.isConfirmed) discardAll(); });
      });
    }

    /* The form is submitted through the confirm dialog, so the flag is cleared
       on submit rather than on a successful response. */
    form.addEventListener('submit', markClean);

    window.addEventListener('beforeunload', (e) => {
      if (!dirty) return;
      e.preventDefault();
      e.returnValue = '';
    });

    paintBar();

    /* ------------------------- rail: smooth scroll + spy ---------------------- */
    const links = $$('[data-set-link]', form);
    const cards = links
      .map((link) => document.getElementById(link.dataset.setLink))
      .filter(Boolean);

    links.forEach((link) => {
      link.addEventListener('click', (e) => {
        const card = document.getElementById(link.dataset.setLink);
        if (!card) return;
        e.preventDefault();
        card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, '', '#' + link.dataset.setLink);
        setActive(link.dataset.setLink);
      });
    });

    const setActive = (id) => {
      if (setActive.current === id) return;
      setActive.current = id;
      links.forEach((l) => l.classList.toggle('active', l.dataset.setLink === id));
    };

    /* The active rail item is derived from scroll position rather than from an
       IntersectionObserver: a narrow observer band disagreed with a programmatic
       jump, snapping the highlight back to the wrong section. */
    let ticking = false;
    const syncSpy = () => {
      ticking = false;
      if (!cards.length) return;

      const threshold = ($('.admin-topbar') ? $('.admin-topbar').offsetHeight : 76) + 130;
      let current = cards[0];
      cards.forEach((card) => {
        if (card.getBoundingClientRect().top <= threshold) current = card;
      });

      /* The final section can sit above the threshold line without ever crossing
         it, so pin it while the page is scrolled to the bottom. */
      const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
      setActive((atBottom ? cards[cards.length - 1] : current).id);
    };

    const onScroll = () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(syncSpy);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', debounce(syncSpy, 150));
    syncSpy();

    /* Land straight on a section when the page is opened with a hash. */
    if (window.location.hash) {
      const target = document.getElementById(window.location.hash.slice(1));
      if (target) setTimeout(() => target.scrollIntoView({ block: 'start' }), 60);
    }
  }

  function config_app_name() {
    return document.body.dataset.appName || 'Jibon Sathi';
  }
})();