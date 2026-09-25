/* ==========================================================================
   Jibon Sathi Admin — backend interactions
   ========================================================================== */
(function () {
  'use strict';

  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));

  document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initSidebar();
    initDropdowns();
    initConfirms();
    initAutoClose();
    initCharts();
    initTabs();
  });

  function initToasts() {
    $$('[data-toast]').forEach((el) => {
      setTimeout(() => { el.classList.add('leaving'); setTimeout(() => el.remove(), 320); }, parseInt(el.dataset.toast, 10) || 4200);
    });
  }

  function initSidebar() {
    const btn = $('[data-sidebar-toggle]');
    const layout = $('.admin-layout');
    if (!btn || !layout) return;
    btn.addEventListener('click', () => layout.classList.toggle('sidebar-open'));
    $(document).addEventListener('click', (e) => {
      if (layout.classList.contains('sidebar-open') && !e.target.closest('.admin-sidebar, [data-sidebar-toggle]')) {
        layout.classList.remove('sidebar-open');
      }
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
})();