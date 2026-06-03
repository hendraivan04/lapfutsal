// ============================================================
// main.js – Global JavaScript for Futsal Booking System
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

  // ── Active Nav Link Highlight ──────────────────────────────
  const currentPage = window.location.pathname.split('/').pop();
  document.querySelectorAll('.navbar-links a, .sidebar-nav a').forEach(link => {
    if (link.getAttribute('href') === currentPage) {
      link.classList.add('active');
    }
  });

  // ── Auto-dismiss alerts ────────────────────────────────────
  document.querySelectorAll('.alert[data-autohide]').forEach(alert => {
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transition = 'opacity 0.4s';
      setTimeout(() => alert.remove(), 400);
    }, 3500);
  });

  // ── Confirm delete modals ──────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const msg  = this.dataset.confirm || 'Yakin ingin menghapus?';
      const href = this.href || this.dataset.action;
      const form = this.closest('form');

      Swal.fire({
        title: 'Konfirmasi',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#00c853',
        cancelButtonColor:  '#555',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText:  'Batal',
        background: '#1a2b1e',
        color: '#e8f5e9',
      }).then(result => {
        if (result.isConfirmed) {
          if (form) form.submit();
          else if (href) window.location.href = href;
        }
      });
    });
  });

  // ── Slot Picker ────────────────────────────────────────────
  const slotItems = document.querySelectorAll('.slot-item:not(.disabled)');
  const slotInput = document.getElementById('slot_id');
  slotItems.forEach(item => {
    item.addEventListener('click', function () {
      slotItems.forEach(s => s.classList.remove('selected'));
      this.classList.add('selected');
      if (slotInput) slotInput.value = this.dataset.slotId;
    });
  });

  // ── Booking Date Min = Today ───────────────────────────────
  const dateInput = document.getElementById('booking_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    if (!dateInput.value) dateInput.value = today;
  }

  // ── Field Selection Highlight ──────────────────────────────
  document.querySelectorAll('.field-select-card').forEach(card => {
    card.addEventListener('click', function () {
      document.querySelectorAll('.field-select-card').forEach(c => c.classList.remove('selected'));
      this.classList.add('selected');
      const fieldId = this.dataset.fieldId;
      const input   = document.getElementById('field_id');
      if (input) input.value = fieldId;
    });
  });

  // ── Price Calculator in booking form ──────────────────────
  function recalcPrice() {
    const priceEl = document.getElementById('price_per_hour');
    const totalEl = document.getElementById('total_price_display');
    if (!priceEl || !totalEl) return;
    const price = parseFloat(priceEl.value) || 0;
    totalEl.textContent = formatRupiah(price);
  }
  recalcPrice();

  // ── Format number as Rupiah ────────────────────────────────
  window.formatRupiah = function (num) {
    return 'Rp ' + num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  };

  // ── Sidebar Mobile Toggle ─────────────────────────────────
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.style.display = sidebar.style.display === 'flex' ? 'none' : 'flex';
    });
  }

});
