// ===================================
// BANK SAMPAH - Main App JS
// Autentikasi ditangani server (session Laravel), bukan localStorage.
// ===================================

function showToast(message, type = 'success', duration = 3000) {
  let toast = document.getElementById('globalToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'globalToast';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '❌'}</span> ${message}`;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), duration);
}

function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('show');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('show');
}

function formatRupiah(num) {
  return 'Rp ' + Number(num).toLocaleString('id-ID');
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

function confirmAction(message, onConfirm) {
  if (confirm(message)) onConfirm();
}

function initSidebar() {
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const toggleBtn = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');

  if (!sidebar || !toggleBtn) return;

  const isMobile = () => window.innerWidth <= 992;

  if (!isMobile() && localStorage.getItem('sidebarCollapsed') === 'true') {
    sidebar.classList.add('collapsed');
    if (mainContent) mainContent.classList.add('expanded');
  }

  toggleBtn.addEventListener('click', () => {
    if (isMobile()) {
      sidebar.classList.toggle('open');
      if (overlay) overlay.classList.toggle('show');
    } else {
      sidebar.classList.toggle('collapsed');
      if (mainContent) mainContent.classList.toggle('expanded');
      localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
  });

  if (overlay) {
    overlay.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  }

  // Tutup sidebar otomatis saat menu diklik di perangkat mobile
  document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', () => {
      if (isMobile()) {
        sidebar.classList.remove('open');
        if (overlay) overlay.classList.remove('show');
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initSidebar();

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('show');
    });
  });

  window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (window.innerWidth > 768) {
      if (sidebar) sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('show');
    }
  });
});

window.formatRupiah = formatRupiah;
window.formatDate = formatDate;
window.showToast = showToast;
window.confirmAction = confirmAction;
window.openModal = openModal;
window.closeModal = closeModal;
