/* Practical 13 - AJAX CRUD for students using fetch() against Practical-13/api/students.php.
 * Shows loading / empty / error states, debounced search, toasts, and sends the CSRF token as a header. */
window.StudentHubAjax = (function () {
  'use strict';
  const API = 'api/students.php';
  const $ = (s) => document.querySelector(s);
  const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
  const state = { q: '', status: '', page: 1, req: 0 };
  let timer;

  function debounce(fn, ms) { return (...a) => { clearTimeout(timer); timer = setTimeout(() => fn(...a), ms); }; }

  function toast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + (type === 'error' ? 'err' : 'ok');
    t.textContent = msg;
    $('#toast-area').appendChild(t);
    setTimeout(() => t.remove(), 3500);
  }

  function setState(kind, msg) {
    const box = $('#ajax-state');
    if (!kind) { box.hidden = true; return; }
    box.hidden = false;
    box.className = 'alert ' + (kind === 'error' ? 'alert-danger' : 'alert-info');
    box.textContent = msg;
  }

  /* One place for every request: parses JSON, turns HTTP errors into exceptions with a readable message. */
  async function api(url, opts) {
    opts = opts || {};
    const headers = { Accept: 'application/json' };
    if (opts.method && opts.method !== 'GET') { headers['X-CSRF-Token'] = csrf(); headers['Content-Type'] = 'application/json'; }
    let res;
    try { res = await fetch(url, { ...opts, headers, credentials: 'same-origin' }); }
    catch (e) { throw { status: 0, message: 'Network error - is the server running?' }; }
    let body = null;
    try { body = await res.json(); } catch (e) { /* not JSON */ }
    if (!res.ok || !body || body.ok === false) {
      const err = (body && body.error) || {};
      if (res.status === 401) { location.href = '../Practical-10/login.php?timeout=1'; }
      throw { status: res.status, message: err.message || 'Request failed (' + res.status + ').', fields: err.fields || {} };
    }
    return body;
  }

  function esc(v) { const d = document.createElement('div'); d.textContent = v == null ? '' : v; return d.innerHTML; }

  async function load() {
    const my = ++state.req;
    setState('loading', 'Loading students…');
    $('#ajax-rows').setAttribute('aria-busy', 'true');
    const p = new URLSearchParams({ q: state.q, status: state.status, page: state.page, per_page: 8 });
    try {
      const body = await api(API + '?' + p);
      if (my !== state.req) return;                      // a newer request was started: ignore this stale answer
      render(body);
      setState(null);
    } catch (e) {
      if (my !== state.req) return;
      $('#ajax-rows').innerHTML = '';
      setState('error', 'Could not load students: ' + e.message);
    } finally { $('#ajax-rows').removeAttribute('aria-busy'); }
  }

  function render(body) {
    const rows = body.data, m = body.meta;
    $('#ajax-rows').innerHTML = rows.length ? rows.map((s) =>
      '<tr><td><strong>' + esc(s.student_id) + '</strong></td><td>' + esc(s.fullname) + '</td><td>' + esc(s.email) +
      '</td><td>' + esc(s.course) + '</td><td>' + esc(s.semester) + '</td><td>' + Number(s.sgpa).toFixed(2) +
      '</td><td><span class="badge ' + (s.status === 'active' ? 'badge-success' : 'badge-neutral') + '">' + esc(s.status) + '</span></td>' +
      '<td><div class="table-actions"><button class="btn btn-outline btn-sm" data-edit="' + s.id + '">✏️ Edit</button> ' +
      '<button class="btn btn-danger btn-sm" data-del="' + s.id + '" data-name="' + esc(s.fullname) + '">🗑 Delete</button></div></td></tr>'
    ).join('') : '<tr><td colspan="8" class="empty-row">No students match your search.</td></tr>';
    $('#ajax-meta').textContent = 'Showing ' + rows.length + ' of ' + m.total + ' students (page ' + m.page + ' of ' + m.pages + ')';
    let h = '<a href="#" class="' + (m.page <= 1 ? 'disabled' : '') + '" data-page="' + (m.page - 1) + '">‹ Prev</a>';
    for (let i = 1; i <= m.pages; i++) h += i === m.page ? '<span class="current" aria-current="page">' + i + '</span>' : '<a href="#" data-page="' + i + '">' + i + '</a>';
    h += '<a href="#" class="' + (m.page >= m.pages ? 'disabled' : '') + '" data-page="' + (m.page + 1) + '">Next ›</a>';
    $('#ajax-pager').innerHTML = m.pages > 1 ? h : '';
  }

  /* ---- dialog -------------------------------------------------------- */
  function clearErrors() {
    document.querySelectorAll('#student-form [data-for]').forEach((s) => (s.textContent = ''));
    $('#dlg-error').hidden = true;
  }
  function openDialog(student) {
    const f = $('#student-form'); f.reset(); clearErrors();
    $('#dlg-title').textContent = student ? 'Edit student' : 'Add student';
    f.elements.id.value = student ? student.id : '';
    if (student) Object.keys(student).forEach((k) => { if (f.elements[k] && k !== 'id') f.elements[k].value = student[k]; });
    $('#student-dialog').showModal();
    f.elements.student_id.focus();
  }

  async function save(ev) {
    ev.preventDefault();
    clearErrors();
    const f = $('#student-form'), data = {};
    ['student_id', 'fullname', 'email', 'mobile', 'course', 'semester', 'gender', 'status', 'sgpa', 'attendance'].forEach((k) => (data[k] = f.elements[k].value));
    const id = f.elements.id.value, btn = $('#dlg-save');
    btn.disabled = true; btn.textContent = 'Saving…';
    try {
      await api(API + (id ? '?id=' + id : ''), { method: id ? 'PUT' : 'POST', body: JSON.stringify(data) });
      $('#student-dialog').close();
      toast(id ? 'Student updated.' : 'Student added.');
      load();
    } catch (e) {
      Object.entries(e.fields || {}).forEach(([k, msg]) => { const s = document.querySelector('[data-for="' + k + '"]'); if (s) s.textContent = msg; });
      const box = $('#dlg-error'); box.textContent = e.message; box.hidden = false;
    } finally { btn.disabled = false; btn.textContent = '💾 Save'; }
  }

  async function init() {
    if (!$('#ajax-rows')) return;
    $('#ajax-q').addEventListener('input', debounce((e) => { state.q = e.target.value.trim(); state.page = 1; load(); }, 300));
    $('#ajax-status').addEventListener('change', (e) => { state.status = e.target.value; state.page = 1; load(); });
    $('#btn-add').addEventListener('click', () => openDialog(null));
    $('#dlg-cancel').addEventListener('click', () => $('#student-dialog').close());
    $('#student-form').addEventListener('submit', save);
    $('#ajax-pager').addEventListener('click', (e) => {
      const a = e.target.closest('a[data-page]'); if (!a) return;
      e.preventDefault(); if (a.classList.contains('disabled')) return;
      state.page = Number(a.dataset.page); load();
    });
    $('#ajax-rows').addEventListener('click', async (e) => {
      const ed = e.target.closest('[data-edit]'), del = e.target.closest('[data-del]');
      try {
        if (ed) { const b = await api(API + '?id=' + ed.dataset.edit); openDialog(b.data); }
        if (del && confirm('Delete ' + del.dataset.name + '? This cannot be undone.')) {
          await api(API + '?id=' + del.dataset.del, { method: 'DELETE' });
          toast('Student deleted.'); load();
        }
      } catch (err) { toast(err.message, 'error'); }
    });
    load();
  }
  document.addEventListener('DOMContentLoaded', init);
  return { init, load };
})();
