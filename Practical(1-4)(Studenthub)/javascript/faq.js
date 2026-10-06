/**
 * StudentHub Portal - Collapsible FAQ Accordion & Dynamic JSON Loader
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * Practical 6: Rendering External JSON Data using Fetch API, Search, Filter, Sort & Pagination
 * 
 * Capabilities:
 * - Dynamic Fetch API loader for faqs.json with loading & error handling
 * - Search across FAQ Questions & Answers
 * - Filter by FAQ Category
 * - Sort by Question (A-Z, Z-A) and Category
 * - Pagination controls with page numbers
 * - Accessible accordion toggle interaction (aria-expanded, aria-controls, keyboard-ready)
 * - Backwards-compatible with static FAQ sections
 */

(function () {
  'use strict';

  let allFaqs = [];
  let currentSearch = '';
  let currentCategory = 'all';
  let currentSort = 'id-asc';
  let currentPage = 1;
  const itemsPerPage = 5;
  let isLoading = false;
  let hasError = false;

  const DATA_URLS = [
    '../data/faqs.json',
    './data/faqs.json',
    '../Practical-6/faqs.json',
    '../faqs.json',
    './faqs.json',
    'faqs.json'
  ];

  // 1. Static FAQ Accordion Handler (Practical 4 legacy support)
  function bindAccordionListeners(rootElement) {
    const scope = rootElement || document;
    const faqQuestions = scope.querySelectorAll('.faq-question');

    if (!faqQuestions || faqQuestions.length === 0) return;

    faqQuestions.forEach(function (button) {
      // Remove any existing click handler to prevent duplicates
      button.onclick = null;
      button.addEventListener('click', function () {
        const faqItem = this.closest('.faq-item');
        if (!faqItem) return;

        const isExpanded = this.getAttribute('aria-expanded') === 'true';

        // Close other open FAQ items in this container
        const parentContainer = faqItem.closest('.faq-container') || document;
        parentContainer.querySelectorAll('.faq-item').forEach(function (item) {
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

        // Toggle current item
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

  // 2. Dynamic Fetch API FAQ Loader (Practical 6)
  async function fetchFaqsData() {
    const container = document.getElementById('faqs-data-container');
    const paginationWrap = document.getElementById('faqs-pagination');
    const metaBar = document.getElementById('faqs-meta-bar');

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
      allFaqs = fetchedData;
      hasError = false;
      renderFaqs();
    } else {
      hasError = true;
      renderErrorState(container, paginationWrap, metaBar, lastError ? lastError.message : 'Failed to load FAQ records.');
    }
  }

  function renderLoadingState(container, paginationWrap, metaBar) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-loading-state" role="status" aria-live="polite">
        <div class="spinner" aria-hidden="true"></div>
        <div class="loading-text">Loading frequently asked questions...</div>
      </div>
    `;
  }

  function renderErrorState(container, paginationWrap, metaBar, msg) {
    if (metaBar) metaBar.style.display = 'none';
    if (paginationWrap) paginationWrap.style.display = 'none';
    container.innerHTML = `
      <div class="data-error-state" role="alert">
        <div class="error-icon" aria-hidden="true">⚠️</div>
        <div class="error-title">Unable to Load FAQs</div>
        <div class="error-message">Failed to retrieve questions from JSON source (${msg}). Please retry.</div>
        <button type="button" class="btn btn-primary btn-sm" id="retry-faqs-btn">
          🔄 Retry Loading
        </button>
      </div>
    `;

    const retryBtn = document.getElementById('retry-faqs-btn');
    if (retryBtn) {
      retryBtn.addEventListener('click', fetchFaqsData);
    }
  }

  function getProcessedFaqs() {
    let filtered = allFaqs.filter(function (faq) {
      const q = currentSearch.toLowerCase().trim();
      const matchesSearch = !q ||
        (faq.question && faq.question.toLowerCase().includes(q)) ||
        (faq.answer && faq.answer.toLowerCase().includes(q)) ||
        (faq.keywords && faq.keywords.toLowerCase().includes(q)) ||
        (faq.category && faq.category.toLowerCase().includes(q));

      const matchesCat = (currentCategory === 'all') ||
        (faq.category && faq.category.toLowerCase() === currentCategory.toLowerCase());

      return matchesSearch && matchesCat;
    });

    filtered.sort(function (a, b) {
      if (currentSort === 'question-asc') {
        return (a.question || '').localeCompare(b.question || '');
      } else if (currentSort === 'question-desc') {
        return (b.question || '').localeCompare(a.question || '');
      } else if (currentSort === 'category') {
        return (a.category || '').localeCompare(b.category || '');
      }
      return (a.id || 0) - (b.id || 0);
    });

    return filtered;
  }

  function renderFaqs() {
    const container = document.getElementById('faqs-data-container');
    const paginationWrap = document.getElementById('faqs-pagination');
    const metaBar = document.getElementById('faqs-meta-bar');
    const countBadge = document.getElementById('faqs-count-display');

    if (!container) return;

    if (isLoading || hasError) return;

    const filtered = getProcessedFaqs();
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
        countBadge.textContent = 'No FAQs found';
      } else {
        const startIdx = (currentPage - 1) * itemsPerPage + 1;
        const endIdx = Math.min(currentPage * itemsPerPage, totalRecords);
        countBadge.textContent = `Showing ${startIdx}–${endIdx} of ${totalRecords} FAQs`;
      }
    }

    if (totalRecords === 0) {
      container.innerHTML = `
        <div class="data-empty-state">
          <div class="empty-icon">❓</div>
          <div class="empty-title">No Matching Questions Found</div>
          <div class="empty-desc">We couldn't find any FAQs matching your search query or category filter.</div>
          <button type="button" class="btn btn-outline btn-sm" id="clear-faqs-filters-btn">Clear All Filters</button>
        </div>
      `;
      if (paginationWrap) paginationWrap.style.display = 'none';

      const clearBtn = document.getElementById('clear-faqs-filters-btn');
      if (clearBtn) {
        clearBtn.addEventListener('click', resetFilters);
      }
      return;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;
    const paginatedItems = filtered.slice(startIndex, startIndex + itemsPerPage);

    const itemsHtml = paginatedItems.map(function (faq, index) {
      const uniqueId = `dyn-faq-${faq.id}-${index}`;
      return `
        <div class="faq-item" data-faq-id="${faq.id}">
          <button class="faq-question" aria-expanded="false" aria-controls="${uniqueId}">
            <span style="display: flex; align-items: center; gap: 8px;">
              <span class="badge badge-info" style="font-size: 0.72rem; padding: 2px 7px;">${escapeHtml(faq.category)}</span>
              <span>${escapeHtml(faq.question)}</span>
            </span>
            <span class="faq-icon" aria-hidden="true">+</span>
          </button>
          <div class="faq-answer" id="${uniqueId}" role="region">
            <div class="faq-answer-inner">
              <p>${escapeHtml(faq.answer)}</p>
            </div>
          </div>
        </div>
      `;
    }).join('');

    container.innerHTML = `<div class="faq-container">${itemsHtml}</div>`;

    // Rebind accordion event listeners to newly rendered items
    bindAccordionListeners(container);

    renderPagination(totalPages, totalRecords);
  }

  function renderPagination(totalPages, totalRecords) {
    const paginationWrap = document.getElementById('faqs-pagination');
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
        <button type="button" class="page-btn prev-btn" id="faqs-prev-page" ${currentPage === 1 ? 'disabled' : ''} aria-label="Previous page">
          &lsaquo; Prev
        </button>
        <div class="page-numbers-container">${pageBtnsHtml}</div>
        <button type="button" class="page-btn next-btn" id="faqs-next-page" ${currentPage === totalPages ? 'disabled' : ''} aria-label="Next page">
          Next &rsaquo;
        </button>
      </div>
    `;

    const prevBtn = document.getElementById('faqs-prev-page');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        if (currentPage > 1) {
          currentPage--;
          renderFaqs();
          scrollToContainerTop();
        }
      });
    }

    const nextBtn = document.getElementById('faqs-next-page');
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        if (currentPage < totalPages) {
          currentPage++;
          renderFaqs();
          scrollToContainerTop();
        }
      });
    }

    paginationWrap.querySelectorAll('.page-numbers-container .page-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const page = parseInt(this.getAttribute('data-page'), 10);
        if (page && page !== currentPage) {
          currentPage = page;
          renderFaqs();
          scrollToContainerTop();
        }
      });
    });
  }

  function scrollToContainerTop() {
    const container = document.getElementById('faqs-data-container');
    if (container) {
      container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function resetFilters() {
    currentSearch = '';
    currentCategory = 'all';
    currentSort = 'id-asc';
    currentPage = 1;

    const searchInput = document.getElementById('faqs-search-input');
    const categorySelect = document.getElementById('faqs-category-filter');
    const sortSelect = document.getElementById('faqs-sort-select');

    if (searchInput) searchInput.value = '';
    if (categorySelect) categorySelect.value = 'all';
    if (sortSelect) sortSelect.value = 'id-asc';

    renderFaqs();
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

  function initFAQ() {
    // 1. Check if dynamic FAQ container exists
    const dynamicContainer = document.getElementById('faqs-data-container');

    if (dynamicContainer) {
      const searchInput = document.getElementById('faqs-search-input');
      const categorySelect = document.getElementById('faqs-category-filter');
      const sortSelect = document.getElementById('faqs-sort-select');
      const resetBtn = document.getElementById('faqs-reset-btn');

      if (searchInput) {
        searchInput.addEventListener('input', function (e) {
          currentSearch = e.target.value;
          currentPage = 1;
          renderFaqs();
        });
      }

      if (categorySelect) {
        categorySelect.addEventListener('change', function (e) {
          currentCategory = e.target.value;
          currentPage = 1;
          renderFaqs();
        });
      }

      if (sortSelect) {
        sortSelect.addEventListener('change', function (e) {
          currentSort = e.target.value;
          renderFaqs();
        });
      }

      if (resetBtn) {
        resetBtn.addEventListener('click', resetFilters);
      }

      fetchFaqsData();
    } else {
      // 2. Fallback: bind static accordion items on page (e.g. home.html)
      bindAccordionListeners();
    }
  }

  window.StudentHubFAQ = {
    init: initFAQ,
    fetch: fetchFaqsData,
    reset: resetFilters
  };

})();
