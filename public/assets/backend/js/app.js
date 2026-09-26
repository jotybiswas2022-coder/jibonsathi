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
    initFormKit($('[data-form-kit]'));
    initSettings();
    initLiveSearch();
    initListFilter();
    initThreadLog();
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

  /* ================================ form kit ================================ */
  /* Character counters, file-picker previews, clearable fields and the
     unsaved-changes bar, shared by every long admin form. A form opts in by
     carrying the matching data attributes, and anything a form does not have is
     skipped, so this is safe to run on any form. `onReset` lets a form re-paint
     its own previews after the visitor discards their edits. */
  function initFormKit(form, onReset) {
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

    /* A form can word its own untouched state ("No unsaved changes" on an edit
       form reads better than "All changes saved", which is not true yet). */
    const cleanLabel = barText?.dataset.sbClean || 'All changes saved';

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

    /* ------------------------------ file pickers ------------------------------ */
    $$('[data-preview]', form).forEach((input) => {
      const thumb = document.getElementById(input.dataset.preview);
      const label = $('[data-uploader="' + input.id + '"]', form);
      const name = $('[data-file-name="' + input.id + '"]', form);
      const removeBtn = $('[data-remove="' + input.id + '"]', form);
      const removeFlag = $('[data-remove-field="' + input.name + '"]', form);

      /* Removing an existing image is a pending change, not an instant delete:
         the file is only unlinked when the form is saved, so a mis-click can be
         undone and nothing is lost if the visitor simply navigates away. */
      const idleName = name ? (name.dataset.idleName || name.textContent) : '';

      const paintRemoving = (on) => {
        if (label) label.classList.toggle('is-removing', on);
        if (name) name.textContent = on ? 'Will be removed when you save' : idleName;
        if (removeBtn) {
          removeBtn.classList.toggle('is-armed', on);
          const text = $('[data-remove-label]', removeBtn);
          if (text) text.textContent = on ? 'Keep it' : 'Remove';
        }
      };

      const setRemoving = (on) => {
        if (! removeFlag) return;
        removeFlag.value = on ? '1' : '';
        paintRemoving(on);
      };

      input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (! file) return;

        if (name) name.textContent = file.name + ' · ' + Math.max(1, Math.round(file.size / 1024)) + ' KB';
        if (label) {
          label.classList.add('has-file');
          label.classList.remove('is-removing');
        }

        /* A newly chosen file always wins over a pending removal. */
        if (removeFlag) removeFlag.value = '';
        if (removeBtn) {
          removeBtn.classList.remove('is-armed');
          const text = $('[data-remove-label]', removeBtn);
          if (text) text.textContent = 'Remove';
        }

        if (thumb && file.type.indexOf('image') === 0) {
          const reader = new FileReader();
          reader.onload = (e) => { thumb.innerHTML = '<img alt="" src="' + e.target.result + '">'; };
          reader.readAsDataURL(file);
        }
      });

      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          setRemoving(! (removeFlag && removeFlag.value === '1'));
        });
      }
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
      if (barText) barText.textContent = now !== initialState ? 'You have unsaved changes' : cleanLabel;
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
      counters.forEach((pair) => updateCounter(pair.counter, pair.field));
      $$('[data-clearable]', form).forEach(paintClear);
      $$('[data-uploader]', form).forEach((label) => label.classList.remove('has-file', 'is-removing'));
      $$('[data-file-name]', form).forEach((el) => { el.textContent = el.dataset.idleName || ''; });
      /* A pending image removal is an edit like any other, so discarding has to
         drop it too, or the next save would delete the file anyway. */
      $$('[data-remove-field]', form).forEach((el) => { el.value = ''; });
      $$('[data-remove]', form).forEach((btn) => {
        btn.classList.remove('is-armed');
        const text = $('[data-remove-label]', btn);
        if (text) text.textContent = 'Remove';
      });
      if (typeof onReset === 'function') onReset();
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
  }

  /* ============================== settings page ============================== */
  function initSettings() {
    const form = $('#settingsForm');
    if (!form) return;

    initFormKit(form, () => paintPreviews());

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

  /* ------------------------------ live search ----------------------------- */
  /* Filters the rows already on the page as you type, and marks the hits. The
     surrounding form is a normal GET search, so Enter still runs the full
     query across every page and the page works with this script disabled.

     The status tiles are live too: clicking one filters the rows in place and
     rewrites the query string, rather than reloading the page. Their hrefs are
     real filter links, so without JS they still work as plain navigation. */
  function initLiveSearch() {
    const form = $('[data-live-search]');
    if (!form) return;

    const input = $('[data-live-search-input]', form);
    const clearBtn = $('[data-live-search-clear]', form);
    const counter = $('[data-live-search-count]');
    const hint = $('[data-live-search-hint]', form);
    const blank = $('[data-live-search-empty]');
    const blankTitle = $('[data-live-empty-title]');
    const blankText = $('[data-live-empty-text]');
    const blankIcon = $('[data-live-empty-icon]');
    const blankLink = $('[data-live-empty-link]');
    const statusInput = $('[data-status-input]', form);
    if (!input) return;

    const rows = $$('[data-story-row]');
    if (!rows.length) return;

    /* One entry per tile, holding the key plus the count the server printed.
       The count is the total for the whole library, not just this page, which
       is what the empty state needs in order to stay honest. */
    const tiles = $$('[data-status-filter]').map((el) => ({
      el: el,
      key: el.dataset.statusFilter,
      href: el.href,
      count: parseInt($('[data-st-count]', el)?.textContent ?? '', 10) || 0,
    }));

    const LABELS = { all: 'all', published: 'published', draft: 'draft', featured: 'featured' };
    const SINGULAR = { all: 'story', published: 'published story', draft: 'draft', featured: 'featured story' };
    const PLURAL = { all: 'stories', published: 'published stories', draft: 'drafts', featured: 'featured stories' };

    /* "1 draft" but "3 drafts", so the empty-state sentences read properly. */
    const counted = (n, key) => `${n} ${n === 1 ? SINGULAR[key] : PLURAL[key]}`;

    const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

    /* Cache each highlightable cell's markup once, so repainting a term is a
       plain string swap rather than a re-render of the row. */
    const cells = rows.flatMap((row) => $$('[data-hl]', row).map((el) => ({ el, html: el.innerHTML })));

    const paint = (el, html, re) => { el.innerHTML = re ? html.replace(re, '<mark>$1</mark>') : html; };

    /* Seed from the field so a ?q= load is highlighted straight away, and so
       init() recognises the list as already filtered by the server. */
    let term = input.value.trim().toLowerCase();
    let re = term.length > 1 ? new RegExp(`(${escapeRe(term)})`, 'gi') : null;

    /* The active status is whichever tile the server marked as current. */
    const activeTile = tiles.find((t) => t.el.classList.contains('is-active'));
    let status = activeTile ? activeTile.key : 'all';

    /* When the page was opened with ?q= or ?status= the server already did the
       filtering, so the row list is only the current page of an already
       filtered result. Track those two values so apply() does not overwrite the
       server's wording while the view still matches what the server returned. */
    const serverTerm = term;
    const serverStatus = status;
    const serverTotal = parseInt(counter?.dataset.liveSearchTotal ?? '', 10);

    /* Only write when there is a target, so pages that do not use the richer
       copy keep whatever the blade rendered. */
    const setEmptyText = (el, value) => { if (el) el.textContent = value; };

    const apply = () => {
      let shown = 0;

      rows.forEach((row) => {
        const byTerm = ! term || row.dataset.search.includes(term);
        const byStatus = status === 'all'
          || (status === 'featured' ? row.dataset.featured === '1' : row.dataset.status === status);
        const hit = byTerm && byStatus;
        row.hidden = ! hit;
        if (hit) shown++;
      });

      cells.forEach((c) => paint(c.el, c.html, re));

      /* Still showing exactly what the server sent? Then its count is the real
         total. Once the term or the status moves, only the local count is known. */
      const serverSynced = term === serverTerm && status === serverStatus;

      if (counter) {
        if (serverSynced) {
          const n = Number.isFinite(serverTotal) ? serverTotal : rows.length;
          counter.textContent = term ? `${n} ${n === 1 ? 'match' : 'matches'}` : `${n} ${n === 1 ? 'story' : 'stories'}`;
        } else {
          counter.textContent = `${shown} ${shown === 1 ? 'match' : 'matches'} on this page`;
        }
      }

      const label = LABELS[status];
      if (hint) {
        hint.textContent = serverSynced
          ? (term
            ? `Filtered on the server across all ${Number.isFinite(serverTotal) ? serverTotal : rows.length} ${serverTotal === 1 ? 'story' : 'stories'}.`
            : `Showing ${LABELS[status] === 'all' ? 'every story' : LABELS[status]}. Type to filter the rows below, or press Enter to search every page.`)
          : (shown
            ? `Filtering the ${rows.length} ${rows.length === 1 ? 'row' : 'rows'} on this page by ${term ? `“${term}”` : label}.`
            : 'Nothing on this page matches.');
      }

      if (blank) blank.hidden = shown !== 0;

      /* A live filter can only ever see this page, so when it empties out say
         what is really going on instead of implying the library is empty. */
      if (shown === 0) {
        const tile = tiles.find((t) => t.key === status);
        const total = tile ? tile.count : 0;
        const termPart = term ? ` matching “${term}”` : '';

        if (term) {
          setEmptyText(blankTitle, `No ${PLURAL[status]}${termPart} on this page`);
          setEmptyText(blankText, total > 0
            ? `There ${total === 1 ? 'is' : 'are'} ${counted(total, status)}${termPart} in total, on other pages. Press Enter to search every page.`
            : `Nothing in the library is ${label}${termPart}. Try a different word or clear the search.`);
        } else if (total > rows.length) {
          setEmptyText(blankTitle, `No ${label} on this page`);
          setEmptyText(blankText, `All ${counted(total, status)} are on other pages. Search every page to see them.`);
        } else {
          setEmptyText(blankTitle, `No ${label} to show`);
          setEmptyText(blankText, `There are no ${PLURAL[status]} in the library right now.`);
        }

        if (blankIcon) blankIcon.className = status === 'all' ? 'fas fa-magnifying-glass' : 'fas fa-filter';
        if (blankLink) {
          blankLink.hidden = total === 0;
          if (total > 0 && tile) blankLink.href = tile.href;
        }
      }

      if (clearBtn) clearBtn.hidden = term === '';
    };

    /* Keep the address bar describing what is on screen. replaceState is used
       rather than pushState so the back button is not flooded with one entry
       per keystroke, and so the tiles and the term can never disagree. */
    const syncUrl = () => {
      const tile = tiles.find((t) => t.key === status);
      if (! tile || ! window.history || typeof history.replaceState !== 'function') return;
      const url = new URL(tile.href);
      if (term) url.searchParams.set('q', term);
      else url.searchParams.delete('q');
      if (url.href !== location.href) history.replaceState(null, '', url);
    };

    /* Move the filter without a reload: repaint the tiles, keep the hidden
       status field and the address bar in step so the view stays shareable. */
    const setStatus = (key) => {
      if (! LABELS[key]) return;
      status = key;

      tiles.forEach((t) => {
        const on = t.key === key;
        t.el.classList.toggle('is-active', on);
        if (on) t.el.setAttribute('aria-current', 'true');
        else t.el.removeAttribute('aria-current');
      });

      if (statusInput) statusInput.value = key === 'all' ? '' : key;

      syncUrl();
      apply();
    };

    const onInput = debounce(() => {
      term = input.value.trim().toLowerCase();
      re = term.length > 1 ? new RegExp(`(${escapeRe(term)})`, 'gi') : null;
      syncUrl();
      apply();
    }, 130);

    input.addEventListener('input', onInput);

    input.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape' || ! input.value) return;
      e.preventDefault();
      input.value = '';
      onInput();
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        input.value = '';
        onInput();
        input.focus();
      });
    }

    /* Modified clicks and middle clicks still open the real href, so the tiles
       keep behaving like links for anyone who wants a new tab. */
    tiles.forEach((t) => {
      t.el.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault();
        setStatus(t.key);
      });
    });

    /* Reset drops the term and the status together, in place. */
    const reset = $('[data-live-search-reset]', form);
    if (reset) {
      reset.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        if (! input.value && status === 'all') return;
        e.preventDefault();
        input.value = '';
        term = '';
        re = null;
        setStatus('all');
        input.focus();
      });
    }

    /* "/" jumps to the box from anywhere that is not already a text field. */
    document.addEventListener('keydown', (e) => {
      if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey) return;
      const el = document.activeElement;
      if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT' || el.isContentEditable)) return;
      e.preventDefault();
      input.focus();
      input.select();
    });

    apply();
  }

  /* ============================== message thread ============================== */
  /* The log scrolls on its own, so a long thread has to open at the newest
     message rather than at the top of the oldest page. */
  function initThreadLog() {
    const log = $('[data-ms-log]');
    if (! log) return;

    const jump = () => { log.scrollTop = log.scrollHeight; };

    if (log.scrollHeight > log.clientHeight) {
      jump();
      /* Late loading images can change the height after the first paint, which
         would leave the view parked above the newest message. */
      window.addEventListener('load', jump);
      $$('img', log).forEach((img) => {
        if (! img.complete) img.addEventListener('load', jump, { once: true });
      });
    }
  }

  /* ============================== messages list ============================== */
  /* A smaller sibling of initLiveSearch for pages that filter a plain list by
     one term and one scope. It is deliberately separate rather than a mode of
     the story search, which is tied to status tiles and featured flags. */
  function initListFilter() {
    const form = $('[data-ms-filter]');
    if (! form) return;

    const input = $('[data-ms-filter-input]', form);
    const clearBtn = $('[data-ms-filter-clear]', form);
    const counter = $('[data-ms-filter-count]');
    const hint = $('[data-ms-filter-hint]');
    const blank = $('[data-ms-filter-empty]');
    const blankLink = $('[data-ms-empty-link]');
    const scopeInput = $('[data-ms-scope-input]', form);
    if (! input) return;

    const rows = $$('[data-ms-row]');
    if (! rows.length) return;

    const tiles = $$('[data-ms-scope]').map((el) => ({
      el: el,
      key: el.dataset.msScope,
      count: parseInt($('[data-ms-count]', el)?.textContent ?? '', 10) || 0,
    }));

    const escapeRe = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const cells = rows.flatMap((row) => $$('[data-hl]', row).map((el) => ({ el: el, html: el.innerHTML })));
    const paint = (el, html, re) => { el.innerHTML = re ? html.replace(re, '<mark>$1</mark>') : html; };

    const activeTile = tiles.find((t) => t.el.classList.contains('is-active'));
    let scope = activeTile ? activeTile.key : 'all';
    let term = input.value.trim().toLowerCase();
    let re = term.length > 1 ? new RegExp(`(${escapeRe(term)})`, 'gi') : null;

    const pageTotal = parseInt(counter?.dataset.msFilterTotal ?? '', 10) || 0;
    /* Values the server used, so a ?q=/?scope= load keeps the server's wording
       until the visitor actually changes something. */
    const serverTerm = term;
    const serverScope = scope;

    /* A row matches the scope when it is a flagged thread, or when the active
       scope is a property it simply has or has not got. */
    const inScope = (row) => {
      if (scope === 'all') return true;
      if (scope === 'flagged') return row.dataset.scope === 'flagged';
      if (scope === 'empty') return row.dataset.empty === '1';
      if (scope === 'week') return row.dataset.week === '1';
      return true;
    };

    const apply = () => {
      let shown = 0;

      rows.forEach((row) => {
        const hit = (! term || row.dataset.search.includes(term)) && inScope(row);
        row.hidden = ! hit;
        if (hit) shown++;
      });

      cells.forEach((c) => paint(c.el, c.html, re));

      /* Still showing exactly what the server returned? Then its count is the
         real total and the wording stays. Otherwise count the visible rows and
         say so, rather than claiming a page holds every match. */
      const untouched = term === serverTerm && scope === serverScope;
      if (counter) {
        if (untouched) {
          counter.textContent = pageTotal + ' ' + (pageTotal === 1 ? 'thread' : 'threads');
        } else {
          counter.textContent = shown + ' on this page'
            + (shown === 1 ? ' match' : ' matches');
        }
      }

      if (hint) {
        if (untouched && ! term) {
          hint.textContent = 'Type to filter the rows below, or press Enter to search every page.';
        } else if (term) {
          hint.textContent = 'Filtering the ' + shown + ' ' + (shown === 1 ? 'thread' : 'threads')
            + ' on this page. Press Enter to search every page.';
        } else {
          hint.textContent = 'Showing ' + shown + ' ' + (shown === 1 ? 'thread' : 'threads') + ' on this page.';
        }
      }

      if (blank) {
        blank.hidden = shown > 0;
        if (blankLink) blankLink.hidden = shown > 0;
      }
    };

    const setScope = (key) => {
      scope = key;
      tiles.forEach((t) => t.el.classList.toggle('is-active', t.key === key));
      if (scopeInput) scopeInput.value = key === 'all' ? '' : key;
    };

    let timer;
    input.addEventListener('input', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        term = input.value.trim().toLowerCase();
        re = term.length > 1 ? new RegExp(`(${escapeRe(term)})`, 'gi') : null;
        if (clearBtn) clearBtn.hidden = input.value === '';
        apply();
      }, 140);
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        input.value = '';
        term = '';
        re = null;
        clearBtn.hidden = true;
        apply();
        input.focus();
      });
    }

    tiles.forEach((t) => {
      t.el.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        e.preventDefault();
        setScope(t.key);
        apply();
      });
    });

    const reset = $('[data-ms-filter-reset]', form);
    if (reset) {
      reset.addEventListener('click', (e) => {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        if (! input.value && scope === 'all') return;
        e.preventDefault();
        input.value = '';
        term = '';
        re = null;
        if (clearBtn) clearBtn.hidden = true;
        setScope('all');
        apply();
        input.focus();
      });
    }

    document.addEventListener('keydown', (e) => {
      if (e.key !== '/' || e.metaKey || e.ctrlKey || e.altKey) return;
      const el = document.activeElement;
      if (el && (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT' || el.isContentEditable)) return;
      e.preventDefault();
      input.focus();
      input.select();
    });

    apply();
  }
})();