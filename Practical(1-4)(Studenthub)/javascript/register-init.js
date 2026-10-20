// The PHP pages do not load main.js, so start the validator directly.
document.addEventListener('DOMContentLoaded', function () {
  if (window.StudentHubValidation) { window.StudentHubValidation.init(); }
});
