/**
 * StudentHub Portal - Dynamic Notification Banner
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.getElementById(), document.querySelector()
 * - DOM Modification: textContent, classList.add(), classList.remove(), setAttribute()
 * - Event Handling: addEventListener('click')
 * - Animation/Transition: smooth slide/fade dismissal
 */

function initNotificationBanner() {
  const banner = document.getElementById('notification-banner');
  const closeBtn = document.getElementById('banner-close-btn');
  const bannerText = document.getElementById('banner-text');

  if (!banner || !closeBtn) {
    return; // Exit safely if notification banner elements are not on page
  }

  // 1. Close/Dismiss Banner Event Listener
  closeBtn.addEventListener('click', function () {
    // Add CSS dismissal animation class
    banner.classList.add('banner-hidden');
    
    // Set aria-hidden attribute for accessibility
    banner.setAttribute('aria-hidden', 'true');
  });
}

/**
 * Dynamically updates and displays the notification banner text from JavaScript
 * @param {string} message - Message text to show in banner
 * @param {string} type - 'info', 'success', 'warning', or 'alert'
 */
function showNotification(message, type = 'info') {
  const banner = document.getElementById('notification-banner');
  const bannerText = document.getElementById('banner-text');
  const bannerIcon = document.getElementById('banner-icon');

  if (!banner || !bannerText) return;

  // Update banner message using textContent DOM property
  bannerText.textContent = message;

  // Update banner icon depending on type
  if (bannerIcon) {
    switch (type) {
      case 'success':
        bannerIcon.textContent = '✅';
        break;
      case 'warning':
        bannerIcon.textContent = '⚠️';
        break;
      case 'alert':
        bannerIcon.textContent = '🚨';
        break;
      default:
        bannerIcon.textContent = '📢';
    }
  }

  // Make banner visible by removing hidden class
  banner.classList.remove('banner-hidden');
  banner.setAttribute('aria-hidden', 'false');
}

/**
 * Hide notification banner programmatically
 */
function hideNotification() {
  const banner = document.getElementById('notification-banner');
  if (banner) {
    banner.classList.add('banner-hidden');
    banner.setAttribute('aria-hidden', 'true');
  }
}

window.StudentHubNotification = {
  init: initNotificationBanner,
  show: showNotification,
  hide: hideNotification
};
