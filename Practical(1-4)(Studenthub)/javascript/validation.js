/**
 * StudentHub Portal - Registration Form Validation (Practical 5)
 * - Regular-expression based validation for every field
 * - Inline error messages shown next to the relevant field
 * - Password strength meter
 * - Real-time validation on input / change / blur events
 * - Custom CAPTCHA drawn on an HTML5 <canvas>
 *
 * The same rules are repeated on the server (Practical 7 / 9) because
 * client-side validation can always be bypassed.
 */
(function () {
  'use strict';

  // ---------------------------------------------------------------
  // 1. Regular expressions (kept in one place so they are easy to test)
  // ---------------------------------------------------------------
  const PATTERNS = {
    fullname: /^[A-Za-z][A-Za-z .'-]{2,59}$/,                // 3-60 letters, spaces, dot, hyphen, apostrophe
    student_id: /^[A-Za-z0-9]{5,15}$/,                       // e.g. 25DCE072
    email: /^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*\.[A-Za-z]{2,}$/,
    mobile: /^[0-9]{10}$/,                                   // exactly 10 digits
    passwordRules: {
      length: /^.{8,64}$/,
      upper: /[A-Z]/,
      lower: /[a-z]/,
      digit: /[0-9]/,
      special: /[^A-Za-z0-9]/
    }
  };

  // ---------------------------------------------------------------
  // 2. One validator function per field: returns '' when valid,
  //    otherwise the error message that should be shown.
  // ---------------------------------------------------------------
  const validators = {
    fullname: function (form) {
      const v = form.fullname.value.trim();
      if (!v) return 'Full name is required.';
      if (!PATTERNS.fullname.test(v)) return 'Use 3-60 letters only (spaces, dots, hyphens allowed).';
      return '';
    },
    student_id: function (form) {
      const v = form.student_id.value.trim();
      if (!v) return 'Student ID / roll number is required.';
      if (!PATTERNS.student_id.test(v)) return 'Student ID must be 5-15 letters/digits, e.g. 25DCE072.';
      return '';
    },
    mobile: function (form) {
      const v = form.mobile.value.trim();
      if (!v) return 'Mobile number is required.';
      if (!PATTERNS.mobile.test(v)) return 'Mobile number must be exactly 10 digits.';
      return '';
    },
    email: function (form) {
      const v = form.email.value.trim();
      if (!v) return 'Email address is required.';
      if (!PATTERNS.email.test(v)) return 'Enter a valid email, e.g. name@charusat.edu.in.';
      return '';
    },
    course: function (form) {
      return form.course.value ? '' : 'Please select your department / course.';
    },
    year: function (form) {
      return form.year.value ? '' : 'Please select your academic year.';
    },
    gender: function (form) {
      return form.querySelector('input[name="gender"]:checked') ? '' : 'Please select your gender.';
    },
    password: function (form) {
      const v = form.password.value;
      if (!v) return 'Password is required.';
      const r = PATTERNS.passwordRules;
      if (!r.length.test(v)) return 'Password must be 8-64 characters long.';
      if (!r.upper.test(v)) return 'Add at least one uppercase letter (A-Z).';
      if (!r.lower.test(v)) return 'Add at least one lowercase letter (a-z).';
      if (!r.digit.test(v)) return 'Add at least one digit (0-9).';
      return '';
    },
    confirm_password: function (form) {
      const v = form.confirm_password.value;
      if (!v) return 'Please confirm your password.';
      if (v !== form.password.value) return 'Passwords do not match.';
      return '';
    },
    terms: function (form) {
      return form.terms.checked ? '' : 'You must accept the academic terms to register.';
    },
    captcha: function (form) {
      if (!form.captcha) return '';
      const v = form.captcha.value.trim().toUpperCase();
      if (!v) return 'Type the characters shown in the image.';
      if (v !== captchaCode) return 'CAPTCHA does not match. Try again or refresh the image.';
      return '';
    }
  };

  // ---------------------------------------------------------------
  // 3. Showing / clearing errors next to the field
  // ---------------------------------------------------------------
  function fieldWrapper(form, name) {
    const el = form.querySelector('[name="' + name + '"]');
    return el ? el.closest('.form-group') || el.parentElement : null;
  }

  function showResult(form, name, message) {
    const errBox = document.getElementById(name + '-error');
    const wrap = fieldWrapper(form, name);
    const inputs = form.querySelectorAll('[name="' + name + '"]');
    if (!errBox || !wrap) return;

    errBox.textContent = message;
    wrap.classList.toggle('has-error', message !== '');
    wrap.classList.toggle('is-valid', message === '');
    inputs.forEach(function (input) {
      input.setAttribute('aria-invalid', message !== '' ? 'true' : 'false');
      input.setAttribute('aria-describedby', name + '-error');
    });
  }

  function validateField(form, name) {
    const message = validators[name](form);
    showResult(form, name, message);
    return message === '';
  }

  // ---------------------------------------------------------------
  // 4. Password strength meter
  // ---------------------------------------------------------------
  function scorePassword(value) {
    let score = 0;
    const r = PATTERNS.passwordRules;
    if (value.length >= 8) score++;
    if (value.length >= 12) score++;
    if (r.upper.test(value) && r.lower.test(value)) score++;
    if (r.digit.test(value)) score++;
    if (r.special.test(value)) score++;
    return Math.min(score, 4);                // 0-4
  }

  function updateStrengthMeter(value) {
    const bar = document.getElementById('strength-bar');
    const label = document.getElementById('strength-text');
    if (!bar || !label) return;
    const levels = [
      { text: 'Too weak', cls: 'strength-0', width: '8%' },
      { text: 'Weak', cls: 'strength-1', width: '25%' },
      { text: 'Fair', cls: 'strength-2', width: '50%' },
      { text: 'Good', cls: 'strength-3', width: '75%' },
      { text: 'Strong', cls: 'strength-4', width: '100%' }
    ];
    const level = value ? levels[scorePassword(value)] : { text: 'Enter a password', cls: '', width: '0%' };
    bar.style.width = level.width;
    bar.className = 'strength-bar ' + level.cls;
    label.textContent = value ? 'Password strength: ' + level.text : 'Password strength: —';
  }

  // ---------------------------------------------------------------
  // 5. Custom CAPTCHA drawn with the Canvas API
  // ---------------------------------------------------------------
  let captchaCode = '';

  function generateCaptcha() {
    const canvas = document.getElementById('captcha-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';   // no confusing characters (0/O, 1/I)
    captchaCode = '';
    for (let i = 0; i < 5; i++) {
      captchaCode += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#ecfdf5';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // noise lines and dots make it harder for simple bots
    for (let i = 0; i < 6; i++) {
      ctx.strokeStyle = 'rgba(6,95,70,' + (0.2 + Math.random() * 0.3) + ')';
      ctx.beginPath();
      ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
      ctx.stroke();
    }
    for (let i = 0; i < 40; i++) {
      ctx.fillStyle = 'rgba(6,95,70,' + Math.random() * 0.5 + ')';
      ctx.fillRect(Math.random() * canvas.width, Math.random() * canvas.height, 2, 2);
    }

    // draw each character with a small random rotation
    ctx.font = 'bold 28px monospace';
    ctx.textBaseline = 'middle';
    for (let i = 0; i < captchaCode.length; i++) {
      ctx.save();
      ctx.translate(20 + i * 27, canvas.height / 2);
      ctx.rotate((Math.random() - 0.5) * 0.5);
      ctx.fillStyle = '#064e3b';
      ctx.fillText(captchaCode.charAt(i), 0, 0);
      ctx.restore();
    }
  }

  // ---------------------------------------------------------------
  // 6. Wiring everything to the form
  // ---------------------------------------------------------------
  function init() {
    const form = document.getElementById('registration-form');
    if (!form) return;                       // page has no registration form

    generateCaptcha();

    const refresh = document.getElementById('captcha-refresh');
    if (refresh) {
      refresh.addEventListener('click', function () {
        generateCaptcha();
        if (form.captcha) { form.captcha.value = ''; }
      });
    }

    // Real-time validation: input / change / blur events (fields turn red or green as the user types)
    Object.keys(validators).forEach(function (name) {
      form.querySelectorAll('[name="' + name + '"]').forEach(function (el) {
        const evt = (el.type === 'checkbox' || el.type === 'radio' || el.tagName === 'SELECT') ? 'change' : 'input';
        el.addEventListener(evt, function () {
          validateField(form, name);
          // confirm-password depends on password, so re-check it too
          if (name === 'password') {
            updateStrengthMeter(form.password.value);
            if (form.confirm_password.value) validateField(form, 'confirm_password');
          }
        });
        el.addEventListener('blur', function () {
          if (el.type !== 'checkbox' && el.type !== 'radio') validateField(form, name);
        });
      });
    });

    // Only digits allowed in the mobile field
    form.mobile.addEventListener('input', function () {
      form.mobile.value = form.mobile.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    // Show / hide password toggle
    const toggle = document.getElementById('toggle-password');
    if (toggle) {
      toggle.addEventListener('click', function () {
        const show = form.password.type === 'password';
        form.password.type = show ? 'text' : 'password';
        form.confirm_password.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Hide passwords' : 'Show passwords';
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
      });
    }

    // Final validation when the form is submitted
    form.addEventListener('submit', function (event) {
      let firstInvalid = null;
      const summary = document.getElementById('form-summary');

      Object.keys(validators).forEach(function (name) {
        const ok = validateField(form, name);
        if (!ok && !firstInvalid) firstInvalid = form.querySelector('[name="' + name + '"]');
      });

      if (firstInvalid) {
        event.preventDefault();               // stop the request: nothing is sent to the server
        if (summary) {
          summary.textContent = 'Please correct the highlighted fields and submit again.';
          summary.className = 'form-summary summary-error';
        }
        firstInvalid.focus();
        generateCaptchaIfWrong(form);
      } else if (summary) {
        summary.textContent = 'All fields are valid. Submitting…';
        summary.className = 'form-summary summary-ok';
      }
    });

    updateStrengthMeter('');
  }

  // after a failed submit with a wrong CAPTCHA, give the user a fresh image
  function generateCaptchaIfWrong(form) {
    if (form.captcha && validators.captcha(form) !== '' && form.captcha.value.trim() !== '') {
      generateCaptcha();
      form.captcha.value = '';
    }
  }

  window.StudentHubValidation = {
    init: init,
    // exposed so the rules can be unit-tested from the browser console
    patterns: PATTERNS,
    scorePassword: scorePassword
  };
})();
