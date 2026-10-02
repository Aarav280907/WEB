/**
 * StudentHub Portal - Hamburger Menu & Navigation Handler
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.getElementById(), document.querySelector(), document.querySelectorAll()
 * - DOM Modification: classList.toggle(), classList.remove(), classList.contains(), setAttribute()
 * - Event Handling: addEventListener('click'), addEventListener('resize')
 * - Accessibility: aria-expanded state management
 */

function initNavigation() {
  const hamburgerBtn = document.getElementById('hamburger-btn');
  const navElement = document.querySelector('header nav');

  if (!hamburgerBtn || !navElement) {
    return; // Exit safely if navigation elements are not present on page
  }

  // 1. Toggle mobile menu on hamburger button click
  hamburgerBtn.addEventListener('click', function (event) {
    event.stopPropagation();
    
    // Toggle active classes on nav and hamburger button
    const isOpen = navElement.classList.toggle('nav-active');
    hamburgerBtn.classList.toggle('is-active', isOpen);
    
    // Accessibility: update aria-expanded attribute
    hamburgerBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  // 2. Automatically close mobile navigation when a nav link is clicked
  const navLinks = navElement.querySelectorAll('a');
  navLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      if (navElement.classList.contains('nav-active')) {
        navElement.classList.remove('nav-active');
        hamburgerBtn.classList.remove('is-active');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
      }
    });
  });

  // 3. Close mobile menu when clicking outside the navigation header
  document.addEventListener('click', function (event) {
    if (navElement.classList.contains('nav-active')) {
      const header = document.querySelector('header');
      if (header && !header.contains(event.target)) {
        navElement.classList.remove('nav-active');
        hamburgerBtn.classList.remove('is-active');
        hamburgerBtn.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // 4. Reset mobile menu state on window resize (if resized to desktop)
  window.addEventListener('resize', function () {
    if (window.innerWidth > 768 && navElement.classList.contains('nav-active')) {
      navElement.classList.remove('nav-active');
      hamburgerBtn.classList.remove('is-active');
      hamburgerBtn.setAttribute('aria-expanded', 'false');
    }
  });
}

window.StudentHubNav = {
  init: initNavigation
};
