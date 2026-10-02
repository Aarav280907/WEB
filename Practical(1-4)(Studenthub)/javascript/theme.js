/**
 * StudentHub Portal - Theme Switcher & localStorage Management
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.getElementById(), document.querySelector()
 * - DOM Manipulation: classList.add(), classList.remove(), classList.toggle(), classList.contains()
 * - Web Storage API: localStorage.getItem(), localStorage.setItem()
 * - Event Handling: addEventListener('click'), addEventListener('change'), addEventListener('DOMContentLoaded')
 */

const THEME_STORAGE_KEY = 'studenthub_theme';

/**
 * Apply the specified theme ('light' or 'dark') to the document
 * @param {string} theme - 'dark' or 'light'
 */
function applyTheme(theme) {
  const isDark = theme === 'dark';
  
  // 1. DOM Modification using classList
  if (isDark) {
    document.body.classList.add('dark-theme');
  } else {
    document.body.classList.remove('dark-theme');
  }

  // 2. Update Theme Toggle Button in Header (if present)
  const themeToggleBtn = document.getElementById('theme-toggle');
  const themeIcon = document.getElementById('theme-icon');
  const themeText = document.getElementById('theme-text');
  
  if (themeToggleBtn) {
    themeToggleBtn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    themeToggleBtn.setAttribute('title', isDark ? 'Switch to Light Theme' : 'Switch to Dark Theme');
  }
  
  if (themeIcon) {
    themeIcon.textContent = isDark ? '☀️' : '🌙';
  }
  
  if (themeText) {
    themeText.textContent = isDark ? 'Light' : 'Dark';
  }

  // 3. Update Settings Dropdown (if on settings.html)
  const themeSelect = document.getElementById('theme');
  if (themeSelect && themeSelect.value !== theme) {
    themeSelect.value = theme;
  }
}

/**
 * Toggle between light and dark theme and save choice to localStorage
 */
function toggleTheme() {
  const isCurrentlyDark = document.body.classList.contains('dark-theme');
  const newTheme = isCurrentlyDark ? 'light' : 'dark';
  
  // Save to localStorage
  localStorage.setItem(THEME_STORAGE_KEY, newTheme);
  
  // Apply theme to DOM
  applyTheme(newTheme);
}

/**
 * Initialize theme from localStorage on page load
 */
function initTheme() {
  // Read saved theme from localStorage (with fallback to 'light')
  const savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || 'light';
  applyTheme(savedTheme);

  // Attach click event listener to the theme toggle button
  const themeToggleBtn = document.getElementById('theme-toggle');
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function (event) {
      event.preventDefault();
      toggleTheme();
    });
  }

  // Attach change event listener to settings theme dropdown (if on settings.html)
  const themeSelect = document.getElementById('theme');
  if (themeSelect) {
    themeSelect.addEventListener('change', function (event) {
      const selectedTheme = event.target.value;
      localStorage.setItem(THEME_STORAGE_KEY, selectedTheme);
      applyTheme(selectedTheme);
    });
  }
}

// Export functions for use in main.js or standalone
window.StudentHubTheme = {
  init: initTheme,
  toggle: toggleTheme,
  apply: applyTheme,
  getSavedTheme: () => localStorage.getItem(THEME_STORAGE_KEY) || 'light'
};
