/**
 * StudentHub Portal - Accessible Modal Popup System
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.getElementById(), document.querySelectorAll('[data-modal-target]')
 * - DOM Modification: classList.add(), classList.remove(), setAttribute(), style.overflow
 * - Event Handling: addEventListener('click'), addEventListener('keydown') (Escape key)
 * - Accessibility: aria-modal, role="dialog", aria-hidden state, background scroll lock
 */

let activeModal = null;

/**
 * Open a modal by its ID or element reference
 * @param {string|HTMLElement} modalTarget 
 */
function openModal(modalTarget) {
  const modal = typeof modalTarget === 'string' 
    ? document.getElementById(modalTarget) 
    : modalTarget;

  if (!modal) return;

  activeModal = modal;
  modal.classList.add('modal-active');
  modal.setAttribute('aria-hidden', 'false');
  
  // Prevent background page from scrolling while modal is open
  document.body.style.overflow = 'hidden';

  // Focus on close button or first interactive element inside modal for accessibility
  const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  if (focusable) {
    focusable.focus();
  }
}

/**
 * Close the currently active modal or a specific modal
 * @param {string|HTMLElement} [modalTarget]
 */
function closeModal(modalTarget) {
  const modal = modalTarget 
    ? (typeof modalTarget === 'string' ? document.getElementById(modalTarget) : modalTarget) 
    : activeModal;

  if (!modal) return;

  modal.classList.remove('modal-active');
  modal.setAttribute('aria-hidden', 'true');
  
  // Restore background page scrolling
  document.body.style.overflow = '';
  activeModal = null;
}

/**
 * Initialize all modal triggers and backdrop/keyboard event listeners
 */
function initModals() {
  // 1. Open triggers (buttons/links with data-modal-target attribute or specific IDs)
  const openButtons = document.querySelectorAll('[data-modal-target], .open-modal-btn');
  openButtons.forEach(function (btn) {
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      const targetId = this.getAttribute('data-modal-target') || 'portal-modal';
      openModal(targetId);
    });
  });

  // 2. Close buttons inside all modals
  const closeButtons = document.querySelectorAll('.modal-close-btn, .modal-cancel-btn, [data-modal-close]');
  closeButtons.forEach(function (btn) {
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      const modal = this.closest('.modal-backdrop');
      closeModal(modal);
    });
  });

  // 3. Close when clicking on backdrop (outside the modal dialog)
  const allModals = document.querySelectorAll('.modal-backdrop');
  allModals.forEach(function (modal) {
    modal.addEventListener('click', function (event) {
      // If user clicks on the backdrop itself (not the inner dialog)
      if (event.target === modal) {
        closeModal(modal);
      }
    });
  });

  // 4. Close using Escape key
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' || event.key === 'Esc') {
      if (activeModal) {
        closeModal(activeModal);
      }
    }
  });
}

window.StudentHubModal = {
  init: initModals,
  open: openModal,
  close: closeModal
};
