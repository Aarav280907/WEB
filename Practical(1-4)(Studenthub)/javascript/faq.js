/**
 * StudentHub Portal - Collapsible FAQ Accordion
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.querySelectorAll('.faq-question'), document.querySelectorAll('.faq-item')
 * - DOM Modification: classList.toggle(), classList.remove(), classList.contains(), setAttribute(), getAttribute(), textContent
 * - Event Handling: addEventListener('click')
 * - Accessibility: aria-expanded, aria-controls, keyboard-accessible button triggers
 */

function initFAQ() {
  const faqQuestions = document.querySelectorAll('.faq-question');

  if (!faqQuestions || faqQuestions.length === 0) {
    return; // Exit safely if no FAQ section on this page
  }

  faqQuestions.forEach(function (button) {
    button.addEventListener('click', function () {
      const faqItem = this.closest('.faq-item');
      if (!faqItem) return;

      const isExpanded = this.getAttribute('aria-expanded') === 'true';

      // 1. Optional Accordion behavior: Close other open FAQ items for cleaner UI
      document.querySelectorAll('.faq-item').forEach(function (item) {
        if (item !== faqItem && item.classList.contains('faq-open')) {
          item.classList.remove('faq-open');
          const otherBtn = item.querySelector('.faq-question');
          if (otherBtn) {
            otherBtn.setAttribute('aria-expanded', 'false');
            const icon = otherBtn.querySelector('.faq-icon');
            if (icon) icon.textContent = '+';
          }
        }
      });

      // 2. Toggle current item's expanded state
      if (isExpanded) {
        faqItem.classList.remove('faq-open');
        this.setAttribute('aria-expanded', 'false');
        const icon = this.querySelector('.faq-icon');
        if (icon) icon.textContent = '+';
      } else {
        faqItem.classList.add('faq-open');
        this.setAttribute('aria-expanded', 'true');
        const icon = this.querySelector('.faq-icon');
        if (icon) icon.textContent = '−';
      }
    });
  });
}

window.StudentHubFAQ = {
  init: initFAQ
};
