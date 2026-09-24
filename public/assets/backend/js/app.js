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

  function initConfirms() {
    $$('form[data-confirm], [data-confirm-submit]').forEach((el) => {
      const form = el.tagName === 'FORM' ? el : el.closest('form');
      if (!form) return;
      form.addEventListener('submit', (e) => {
        if (!confirm(el.getAttribute('data-confirm') || 'Are you sure?')) e.preventDefault();
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