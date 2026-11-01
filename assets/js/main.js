/**
 * Durva Eco Ware - Frontend Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
  // Mobile Navigation Toggle
  const menuToggle = document.getElementById('menuToggle');
  const navLinks = document.getElementById('navLinks');

  if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', () => {
      navLinks.classList.toggle('open');
      menuToggle.textContent = navLinks.classList.contains('open') ? '✕' : '☰';
    });

    // Close menu when clicking outside or link
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        menuToggle.textContent = '☰';
      });
    });
  }

  // Dynamic Year in Footer
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  // Auto-dismiss Flash Alerts after 5 seconds
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, 6000);
  });

  // Wholesale Quote Form Handling
  const quoteForm = document.getElementById('quoteForm');
  if (quoteForm) {
    quoteForm.addEventListener('submit', function(e) {
      const waCheckbox = document.getElementById('open_whatsapp_cb');
      if (waCheckbox && waCheckbox.checked) {
        // Let form submit normally to redirect to WhatsApp via PHP
        return true;
      }
    });
  }
});
