/**
 * StudentHub Portal - minimal initializer for the PHP-generated pages (Practical 8+).
 * The static pages use main.js; the PHP pages only need the theme switcher and the hamburger menu.
 */
document.addEventListener('DOMContentLoaded', function () {
  if (window.StudentHubTheme && typeof window.StudentHubTheme.init === 'function') {
    window.StudentHubTheme.init();
  }
  if (window.StudentHubNav && typeof window.StudentHubNav.init === 'function') {
    window.StudentHubNav.init();
  }

  // Confirm dialog for destructive buttons: <form data-confirm="Delete this record?">
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!window.confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
      }
    });
  });
});
