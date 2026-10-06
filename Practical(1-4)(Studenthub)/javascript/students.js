/**
 * StudentHub Portal - Student Profiles & Directory Module
 * Practical 6: Rendering External JSON Data using Fetch API, Search, Filter, Sort & Pagination
 * 
 * Capabilities:
 * - Fetch API for loading students.json
 * - Error handling & loading states
 * - Real-time search by Student Name, ID, Department, and Skills
 * - Filter by Department and Semester
 * - Sort by Name (A-Z, Z-A), Student ID, SGPA (High-to-Low), and Attendance (High-to-Low)
 * - Pagination with customizable page navigation controls
 * - Uses ES6 array methods: filter(), sort(), slice(), map(), forEach()
 */

(function () {
  'use strict';

  let allStudents = [];
  let currentSearch = '';
  let currentDepartment = 'all';
  let currentSemester = 'all';
  let currentSort = 'name-asc';
  let currentPage = 1;
  const itemsPerPage = 6;
  let isLoading = false;
  let hasError = false;

  const DATA_URLS = [
    '../data/students.json',
    './data/students.json',
    '../Practical-6/students.json',
    '../students.json',
    './students.json',
    'students.json'
  ];

  async function fetchStudentsData() {
    const container = document.getElementById('students-data-container');
    const paginationWrap = document.getElementById('students-pagination');
    const metaBar = document.getElementById('students-meta-bar');

    if (!container) return;

    isLoading = true;
    hasError = false;
    renderLoadingState(container, paginationWrap, metaBar);

    let fetchedData = null;
    let lastError = null;

    for (const url of DATA_URLS) {
      try {
        const response = await fetch(url);
        if (response.ok) {
          fetchedData = await response.json();
          break;
        }
      } catch (err) {
        lastError = err;
      }
    }

    isLoading = false;

    if (fetchedData && Array.isArray(fetchedData) && fetchedData.length > 0) {
      allStudents = fetchedData;
      hasError = false;
      renderStudents();
    } else {
      hasError = true;
      renderErrorState(container, paginationWrap, metaBar, lastError ? lastError.message : 'Failed to load student profiles.');
    }
  }

  function renderLoadingState(container, paginationWrap, metaBar) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-loading-state" role="status" aria-live="polite">
        <div class="spinner" aria-hidden="true"></div>
        <div class="loading-text">Loading student profiles and academic directory...</div>
      </div>
    `;
  }

  function renderErrorState(container, paginationWrap, metaBar, msg) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-error-state" role="alert">
        <div class="error-icon" aria-hidden="true">⚠️</div>
        <div class="error-title">Unable to Load Student Profiles</div>
        <div class="error-message">We could not fetch student records (${msg}). Please check file location or connection.</div>
        <button type="button" class="btn btn-primary btn-sm" id="retry-students-btn">
          🔄 Retry Loading
        </button>
      </div>
    `;

    const retryBtn = document.getElementById('retry-students-btn');
    if (retryBtn) {
      retryBtn.addEventListener('click', fetchStudentsData);
    }
  }

  function getProcessedStudents() {
    // 1. Filter by keyword, department, semester
    let filtered = allStudents.filter(function (student) {
      const q = currentSearch.toLowerCase().trim();
      const skillsStr = Array.isArray(student.skills) ? student.skills.join(' ').toLowerCase() : '';
      
      const matchesSearch = !q ||
        (student.name && student.name.toLowerCase().includes(q)) ||
        (student.studentId && student.studentId.toLowerCase().includes(q)) ||
        (student.department && student.department.toLowerCase().includes(q)) ||
        (student.email && student.email.toLowerCase().includes(q)) ||
        skillsStr.includes(q);

      const matchesDept = (currentDepartment === 'all') ||
        (student.department && student.department.toLowerCase() === currentDepartment.toLowerCase());

      const matchesSem = (currentSemester === 'all') ||
        (student.semester && String(student.semester) === String(currentSemester));

      return matchesSearch && matchesDept && matchesSem;
    });

    // 2. Sort
    filtered.sort(function (a, b) {
      if (currentSort === 'name-asc') {
        return (a.name || '').localeCompare(b.name || '');
      } else if (currentSort === 'name-desc') {
        return (b.name || '').localeCompare(a.name || '');
      } else if (currentSort === 'id-asc') {
        return (a.studentId || '').localeCompare(b.studentId || '');
      } else if (currentSort === 'sgpa-desc') {
        return (b.sgpa || 0) - (a.sgpa || 0);
      } else if (currentSort === 'attendance-desc') {
        return (b.attendance || 0) - (a.attendance || 0);
      }
      return 0;
    });

    return filtered;
  }

  function renderStudents() {
    const container = document.getElementById('students-data-container');
    const paginationWrap = document.getElementById('students-pagination');
    const metaBar = document.getElementById('students-meta-bar');
    const countBadge = document.getElementById('students-count-display');

    if (!container) return;

    if (isLoading || hasError) return;

    const filtered = getProcessedStudents();
    const totalRecords = filtered.length;
    const totalPages = Math.ceil(totalRecords / itemsPerPage) || 1;

    if (currentPage > totalPages) {
      currentPage = totalPages;
    }
    if (currentPage < 1) {
      currentPage = 1;
    }

    if (metaBar) metaBar.style.display = 'flex';
    if (countBadge) {
      if (totalRecords === 0) {
        countBadge.textContent = 'No students found';
      } else {
        const startIdx = (currentPage - 1) * itemsPerPage + 1;
        const endIdx = Math.min(currentPage * itemsPerPage, totalRecords);
        countBadge.textContent = `Showing ${startIdx}–${endIdx} of ${totalRecords} students`;
      }
    }

    if (totalRecords === 0) {
      container.innerHTML = `
        <div class="data-empty-state">
          <div class="empty-icon">👥</div>
          <div class="empty-title">No Student Profiles Found</div>
          <div class="empty-desc">No records matched your search filters. Try adjusting your query or resetting all filters.</div>
          <button type="button" class="btn btn-outline btn-sm" id="clear-students-filters-btn">Clear All Filters</button>
        </div>
      `;
      if (paginationWrap) paginationWrap.style.display = 'none';

      const clearBtn = document.getElementById('clear-students-filters-btn');
      if (clearBtn) {
        clearBtn.addEventListener('click', resetFilters);
      }
      return;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedItems = filtered.slice(startIndex, startIndex + itemsPerPage);

    const cardsHtml = paginatedItems.map(function (student) {
      const initials = getInitials(student.name);
      const skillsHtml = Array.isArray(student.skills)
        ? student.skills.slice(0, 3).map(skill => `<span class="skill-tag">${escapeHtml(skill)}</span>`).join('')
        : '';

      const attendanceBadgeClass = (student.attendance >= 85)
        ? 'badge-success'
        : (student.attendance >= 80 ? 'badge-info' : 'badge-danger');

      return `
        <div class="student-card-dynamic" data-id="${student.id}">
          <div class="student-header">
            <div class="student-avatar" aria-hidden="true">${initials}</div>
            <div class="student-identity">
              <span class="student-name" title="${escapeHtml(student.name)}">${escapeHtml(student.name)}</span>
              <span class="student-id">ID: <strong>${escapeHtml(student.studentId)}</strong></span>
            </div>
          </div>

          <div class="student-info-grid">
            <div class="student-stat">
              <span class="student-stat-lbl">Department</span>
              <span class="student-stat-val" style="font-size: 0.82rem;">${escapeHtml(student.department)}</span>
            </div>
            <div class="student-stat">
              <span class="student-stat-lbl">Semester</span>
              <span class="student-stat-val">${escapeHtml(student.degree)} (Sem ${student.semester})</span>
            </div>
            <div class="student-stat">
              <span class="student-stat-lbl">CGPA / SGPA</span>
              <span class="student-stat-val"><span class="badge badge-info">${student.sgpa.toFixed(2)}</span></span>
            </div>
            <div class="student-stat">
              <span class="student-stat-lbl">Attendance</span>
              <span class="student-stat-val"><span class="badge ${attendanceBadgeClass}">${student.attendance}%</span></span>
            </div>
          </div>

          ${skillsHtml ? `<div class="student-skills-list">${skillsHtml}</div>` : ''}

          <div class="student-footer">
            <a href="mailto:${escapeHtml(student.email)}" style="font-size: 0.82rem;" title="Send Email">📧 ${escapeHtml(student.email)}</a>
            <button type="button" class="btn btn-sm btn-outline view-student-btn" data-student-id="${student.id}">
              Profile &rarr;
            </button>
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `<div class="students-grid">${cardsHtml}</div>`;

    container.querySelectorAll('.view-student-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = parseInt(this.getAttribute('data-student-id'), 10);
        const st = allStudents.find(s => s.id === id);
        if (st) {
          alert(`🎓 Student Profile Details\n\nName: ${st.name}\nID: ${st.studentId}\nDepartment: ${st.department} (${st.degree})\nSemester: ${st.semester}\nEmail: ${st.email}\nPhone: ${st.phone}\nSGPA: ${st.sgpa}\nAttendance: ${st.attendance}%\nStatus: ${st.status}\nSkills: ${(st.skills || []).join(', ')}`);
        }
      });
    });

    renderPagination(totalPages, totalRecords);
  }

  function renderPagination(totalPages, totalRecords) {
    const paginationWrap = document.getElementById('students-pagination');
    if (!paginationWrap) return;

    if (totalRecords <= itemsPerPage) {
      paginationWrap.style.display = 'none';
      return;
    }

    paginationWrap.style.display = 'flex';

    let pageBtnsHtml = '';
    for (let i = 1; i <= totalPages; i++) {
      const isActive = i === currentPage ? 'active' : '';
      pageBtnsHtml += `
        <button type="button" class="page-btn ${isActive}" data-page="${i}" aria-label="Go to page ${i}" ${i === currentPage ? 'aria-current="page"' : ''}>
          ${i}
        </button>
      `;
    }

    paginationWrap.innerHTML = `
      <div class="pagination-info">Page <strong>${currentPage}</strong> of <strong>${totalPages}</strong></div>
      <div class="pagination-controls">
        <button type="button" class="page-btn prev-btn" id="students-prev-page" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">
          &lsaquo; Prev
        </button>
        <div class="page-numbers-container">${pageBtnsHtml}</div>
        <button type="button" class="page-btn next-btn" id="students-next-page" ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page">
          Next &rsaquo;
        </button>
      </div>
    `;

    const prevBtn = document.getElementById('students-prev-page');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        if (currentPage > 1) {
          currentPage--;
          renderStudents();
          scrollToContainerTop();
        }
      });
    }

    const nextBtn = document.getElementById('students-next-page');
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        if (currentPage < totalPages) {
          currentPage++;
          renderStudents();
          scrollToContainerTop();
        }
      });
    }

    paginationWrap.querySelectorAll('.page-numbers-container .page-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const page = parseInt(this.getAttribute('data-page'), 10);
        if (page && page !== currentPage) {
          currentPage = page;
          renderStudents();
          scrollToContainerTop();
        }
      });
    });
  }

  function scrollToContainerTop() {
    const container = document.getElementById('students-data-container');
    if (container) {
      container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function resetFilters() {
    currentSearch = '';
    currentDepartment = 'all';
    currentSemester = 'all';
    currentSort = 'name-asc';
    currentPage = 1;

    const searchInput = document.getElementById('students-search-input');
    const deptSelect = document.getElementById('students-dept-filter');
    const semSelect = document.getElementById('students-sem-filter');
    const sortSelect = document.getElementById('students-sort-select');

    if (searchInput) searchInput.value = '';
    if (deptSelect) deptSelect.value = 'all';
    if (semSelect) semSelect.value = 'all';
    if (sortSelect) sortSelect.value = 'name-asc';

    renderStudents();
  }

  function getInitials(name) {
    if (!name) return 'SH';
    const parts = name.trim().split(' ');
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function initStudents() {
    const container = document.getElementById('students-data-container');
    if (!container) return;

    const searchInput = document.getElementById('students-search-input');
    const deptSelect = document.getElementById('students-dept-filter');
    const semSelect = document.getElementById('students-sem-filter');
    const sortSelect = document.getElementById('students-sort-select');
    const resetBtn = document.getElementById('students-reset-btn');

    if (searchInput) {
      searchInput.addEventListener('input', function (e) {
        currentSearch = e.target.value;
        currentPage = 1;
        renderStudents();
      });
    }

    if (deptSelect) {
      deptSelect.addEventListener('change', function (e) {
        currentDepartment = e.target.value;
        currentPage = 1;
        renderStudents();
      });
    }

    if (semSelect) {
      semSelect.addEventListener('change', function (e) {
        currentSemester = e.target.value;
        currentPage = 1;
        renderStudents();
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener('change', function (e) {
        currentSort = e.target.value;
        renderStudents();
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', resetFilters);
    }

    fetchStudentsData();
  }

  window.StudentHubStudents = {
    init: initStudents,
    fetch: fetchStudentsData,
    reset: resetFilters
  };

})();
