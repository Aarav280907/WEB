/**
 * StudentHub Portal - Campus Events & Announcements Module
 * Practical 6: Rendering External JSON Data using Fetch API, Search, Filter, Sort & Pagination
 * 
 * Capabilities:
 * - Fetch API for loading events.json
 * - Error handling & loading states
 * - Real-time keyword search across Title, Description, Organizer, Venue
 * - Category filtering (All, Tech Fest, Examination, Workshop, Sports, Career, Cultural, Academic, Social)
 * - Sorting by Date (Newest/Oldest), Title (A-Z, Z-A), and Category
 * - Pagination with dynamic page controls and results count
 * - Uses ES6 array methods: filter(), sort(), slice(), map(), forEach()
 */

(function () {
  'use strict';

  let allEvents = [];
  let currentSearch = '';
  let currentCategory = 'all';
  let currentSort = 'date-desc';
  let currentPage = 1;
  const itemsPerPage = 5;
  let isLoading = false;
  let hasError = false;

  // Potential relative paths for events.json based on current page location
  const DATA_URLS = [
    '../data/events.json',
    './data/events.json',
    '../Practical-6/events.json',
    '../events.json',
    './events.json',
    'events.json'
  ];

  async function fetchEventsData() {
    const container = document.getElementById('events-data-container');
    const paginationWrap = document.getElementById('events-pagination');
    const metaBar = document.getElementById('events-meta-bar');

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
      allEvents = fetchedData;
      hasError = false;
      renderEvents();
    } else {
      hasError = true;
      renderErrorState(container, paginationWrap, metaBar, lastError ? lastError.message : 'Failed to load events data.');
    }
  }

  function renderLoadingState(container, paginationWrap, metaBar) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-loading-state" role="status" aria-live="polite">
        <div class="spinner" aria-hidden="true"></div>
        <div class="loading-text">Fetching campus events and circulars...</div>
      </div>
    `;
  }

  function renderErrorState(container, paginationWrap, metaBar, msg) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-error-state" role="alert">
        <div class="error-icon" aria-hidden="true">⚠️</div>
        <div class="error-title">Unable to Load Events</div>
        <div class="error-message">We encountered an issue fetching event data (${msg}). Please verify network connection or file path.</div>
        <button type="button" class="btn btn-primary btn-sm" id="retry-events-btn">
          🔄 Retry Loading
        </button>
      </div>
    `;

    const retryBtn = document.getElementById('retry-events-btn');
    if (retryBtn) {
      retryBtn.addEventListener('click', fetchEventsData);
    }
  }

  function getProcessedEvents() {
    // 1. Filter by search keyword and category
    let filtered = allEvents.filter(function (item) {
      const q = currentSearch.toLowerCase().trim();
      const matchesSearch = !q ||
        (item.title && item.title.toLowerCase().includes(q)) ||
        (item.description && item.description.toLowerCase().includes(q)) ||
        (item.organizer && item.organizer.toLowerCase().includes(q)) ||
        (item.venue && item.venue.toLowerCase().includes(q)) ||
        (item.category && item.category.toLowerCase().includes(q));

      const matchesCategory = (currentCategory === 'all') ||
        (item.category && item.category.toLowerCase() === currentCategory.toLowerCase());

      return matchesSearch && matchesCategory;
    });

    // 2. Sort results
    filtered.sort(function (a, b) {
      if (currentSort === 'date-desc') {
        return new Date(b.date) - new Date(a.date);
      } else if (currentSort === 'date-asc') {
        return new Date(a.date) - new Date(b.date);
      } else if (currentSort === 'title-asc') {
        return (a.title || '').localeCompare(b.title || '');
      } else if (currentSort === 'title-desc') {
        return (b.title || '').localeCompare(a.title || '');
      } else if (currentSort === 'category') {
        return (a.category || '').localeCompare(b.category || '');
      }
      return 0;
    });

    return filtered;
  }

  function renderEvents() {
    const container = document.getElementById('events-data-container');
    const paginationWrap = document.getElementById('events-pagination');
    const metaBar = document.getElementById('events-meta-bar');
    const countBadge = document.getElementById('events-count-display');

    if (!container) return;

    if (isLoading || hasError) return;

    const filtered = getProcessedEvents();
    const totalRecords = filtered.length;
    const totalPages = Math.ceil(totalRecords / itemsPerPage) || 1;

    // Boundary check for current page
    if (currentPage > totalPages) {
      currentPage = totalPages;
    }
    if (currentPage < 1) {
      currentPage = 1;
    }

    if (metaBar) metaBar.style.display = 'flex';
    if (countBadge) {
      if (totalRecords === 0) {
        countBadge.textContent = 'No events found';
      } else {
        const startIdx = (currentPage - 1) * itemsPerPage + 1;
        const endIdx = Math.min(currentPage * itemsPerPage, totalRecords);
        countBadge.textContent = `Showing ${startIdx}–${endIdx} of ${totalRecords} events`;
      }
    }

    if (totalRecords === 0) {
      container.innerHTML = `
        <div class="data-empty-state">
          <div class="empty-icon">🔍</div>
          <div class="empty-title">No Matching Events Found</div>
          <div class="empty-desc">Try clearing your search query or selecting "All Categories" from the filter.</div>
          <button type="button" class="btn btn-outline btn-sm" id="clear-events-filters-btn">Clear All Filters</button>
        </div>
      `;
      if (paginationWrap) paginationWrap.style.display = 'none';

      const clearBtn = document.getElementById('clear-events-filters-btn');
      if (clearBtn) {
        clearBtn.addEventListener('click', resetFilters);
      }
      return;
    }

    // Paginate slice
    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedItems = filtered.slice(startIndex, startIndex + itemsPerPage);

    // Render cards using map()
    const cardsHtml = paginatedItems.map(function (event) {
      const badgeClass = event.badgeClass || 'badge-info';
      return `
        <article class="event-card-dynamic" data-id="${event.id}">
          <div class="event-card-header">
            <div class="event-card-meta">
              <span class="badge ${badgeClass}">${escapeHtml(event.category || 'General')}</span>
              <span class="event-meta-item">📅 ${escapeHtml(event.formattedDate || event.date)}</span>
              <span class="event-meta-item">⏰ ${escapeHtml(event.time || 'TBA')}</span>
              <span class="event-meta-item">📍 ${escapeHtml(event.venue || 'Campus')}</span>
            </div>
            <span class="badge badge-neutral">${escapeHtml(event.status || 'Active')}</span>
          </div>

          <h3 class="event-card-title">${escapeHtml(event.title)}</h3>
          <p class="event-card-desc">${escapeHtml(event.description)}</p>

          <div class="event-card-footer">
            <span class="event-organizer">🏛️ ${escapeHtml(event.organizer || 'CHARUSAT')}</span>
            <button type="button" class="btn btn-sm btn-outline event-details-btn" data-event-id="${event.id}">
              View Details &rarr;
            </button>
          </div>
        </article>
      `;
    }).join('');

    container.innerHTML = `<div class="events-list">${cardsHtml}</div>`;

    // Attach card action listeners
    container.querySelectorAll('.event-details-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const id = parseInt(this.getAttribute('data-event-id'), 10);
        const ev = allEvents.find(e => e.id === id);
        if (ev) {
          alert(`📌 ${ev.title}\n\nCategory: ${ev.category}\nDate & Time: ${ev.formattedDate} (${ev.time})\nVenue: ${ev.venue}\nOrganizer: ${ev.organizer}\nStatus: ${ev.status}\n\n${ev.description}`);
        }
      });
    });

    // Render Pagination
    renderPagination(totalPages, totalRecords);
  }

  function renderPagination(totalPages, totalRecords) {
    const paginationWrap = document.getElementById('events-pagination');
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
        <button type="button" class="page-btn prev-btn" id="events-prev-page" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">
          &lsaquo; Prev
        </button>
        <div class="page-numbers-container">${pageBtnsHtml}</div>
        <button type="button" class="page-btn next-btn" id="events-next-page" ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page">
          Next &rsaquo;
        </button>
      </div>
    `;

    // Attach pagination listeners
    const prevBtn = document.getElementById('events-prev-page');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        if (currentPage > 1) {
          currentPage--;
          renderEvents();
          scrollToContainerTop();
        }
      });
    }

    const nextBtn = document.getElementById('events-next-page');
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        if (currentPage < totalPages) {
          currentPage++;
          renderEvents();
          scrollToContainerTop();
        }
      });
    }

    paginationWrap.querySelectorAll('.page-numbers-container .page-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const page = parseInt(this.getAttribute('data-page'), 10);
        if (page && page !== currentPage) {
          currentPage = page;
          renderEvents();
          scrollToContainerTop();
        }
      });
    });
  }

  function scrollToContainerTop() {
    const container = document.getElementById('events-data-container');
    if (container) {
      container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function resetFilters() {
    currentSearch = '';
    currentCategory = 'all';
    currentSort = 'date-desc';
    currentPage = 1;

    const searchInput = document.getElementById('events-search-input');
    const categorySelect = document.getElementById('events-category-filter');
    const sortSelect = document.getElementById('events-sort-select');

    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = 'all';
    if (sortSelect) sortSelect.value = 'date-desc';

    renderEvents();
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

  function initEvents() {
    const container = document.getElementById('events-data-container');
    if (!container) return; // Exit cleanly if page does not feature events container

    const searchInput = document.getElementById('events-search-input');
    const categorySelect = document.getElementById('events-category-filter');
    const sortSelect = document.getElementById('events-sort-select');
    const resetBtn = document.getElementById('events-reset-btn');

    if (searchInput) {
      searchInput.addEventListener('input', function (e) {
        currentSearch = e.target.value;
        currentPage = 1;
        renderEvents();
      });
    }

    if (categorySelect) {
      categorySelect.addEventListener('change', function (e) {
        currentCategory = e.target.value;
        currentPage = 1;
        renderEvents();
      });
    }

    if (sortSelect) {
      sortSelect.addEventListener('change', function (e) {
        currentSort = e.target.value;
        renderEvents();
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', resetFilters);
    }

    // Initial Fetch
    fetchEventsData();
  }

  window.StudentHubEvents = {
    init: initEvents,
    fetch: fetchEventsData,
    reset: resetFilters
  };

})();
