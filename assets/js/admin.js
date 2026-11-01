/**
 * Durva Eco Ware - Admin Dashboard Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // Mobile Sidebar Toggle
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.querySelector('.sidebar');

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 1024 && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  // Delete Confirmations
  const deleteButtons = document.querySelectorAll('[data-confirm]');
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      const msg = btn.getAttribute('data-confirm') || 'Are you sure you want to delete this item? This action cannot be undone.';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // Auto-dismiss Alerts
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 6000);
  });
});
