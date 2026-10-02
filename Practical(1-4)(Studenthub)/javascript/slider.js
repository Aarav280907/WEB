/**
 * StudentHub Portal - Responsive Image & Content Slider
 * Practical 4: JavaScript DOM Manipulation, Event Handling, and UI Interactivity
 * 
 * Demonstrates:
 * - DOM Selection: document.getElementById(), document.querySelector(), document.querySelectorAll()
 * - DOM Modification: innerHTML, textContent, classList.add(), classList.remove(), style.transform
 * - Event Handling: addEventListener('click'), addEventListener('mouseenter'), addEventListener('mouseleave'), addEventListener('keydown')
 * - Timers & Animation: setInterval(), clearInterval(), CSS transform transitions
 */

function initSlider() {
  const sliderContainer = document.getElementById('slider-container');
  const sliderTrack = document.getElementById('slider-track');
  const slides = document.querySelectorAll('.slider-slide');
  const prevBtn = document.getElementById('slider-prev');
  const nextBtn = document.getElementById('slider-next');
  const dotsContainer = document.getElementById('slider-dots');
  const counterElement = document.getElementById('slide-counter');

  // Guard against missing slider elements on other pages
  if (!sliderContainer || !sliderTrack || slides.length === 0) {
    return;
  }

  let currentIndex = 0;
  const totalSlides = slides.length;
  let autoPlayTimer = null;
  const AUTO_PLAY_INTERVAL = 4500; // 4.5 seconds

  // 1. Generate Dot Navigation Indicators using innerHTML & DOM creation
  if (dotsContainer) {
    dotsContainer.innerHTML = '';
    for (let i = 0; i < totalSlides; i++) {
      const dot = document.createElement('button');
      dot.classList.add('slider-dot');
      if (i === 0) dot.classList.add('active');
      dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
      dot.setAttribute('data-index', i);
      
      // Dot click event listener
      dot.addEventListener('click', function () {
        goToSlide(i);
        resetAutoPlay();
      });
      
      dotsContainer.appendChild(dot);
    }
  }

  /**
   * Update the slider display to the given index
   * @param {number} index 
   */
  function goToSlide(index) {
    // Handle wrap-around index
    if (index < 0) {
      currentIndex = totalSlides - 1;
    } else if (index >= totalSlides) {
      currentIndex = 0;
    } else {
      currentIndex = index;
    }

    // Move track using CSS transform
    sliderTrack.style.transform = `translateX(-${currentIndex * 100}%)`;

    // Update active slide class
    slides.forEach(function (slide, idx) {
      if (idx === currentIndex) {
        slide.classList.add('active');
      } else {
        slide.classList.remove('active');
      }
    });

    // Update active dot
    if (dotsContainer) {
      const dots = dotsContainer.querySelectorAll('.slider-dot');
      dots.forEach(function (dot, idx) {
        if (idx === currentIndex) {
          dot.classList.add('active');
        } else {
          dot.classList.remove('active');
        }
      });
    }

    // Update slide counter text
    if (counterElement) {
      counterElement.textContent = `Slide ${currentIndex + 1} of ${totalSlides}`;
    }
  }

  function nextSlide() {
    goToSlide(currentIndex + 1);
  }

  function prevSlide() {
    goToSlide(currentIndex - 1);
  }

  // 2. Button Event Listeners
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      nextSlide();
      resetAutoPlay();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      prevSlide();
      resetAutoPlay();
    });
  }

  // 3. Auto-play functionality
  function startAutoPlay() {
    if (!autoPlayTimer) {
      autoPlayTimer = setInterval(nextSlide, AUTO_PLAY_INTERVAL);
    }
  }

  function stopAutoPlay() {
    if (autoPlayTimer) {
      clearInterval(autoPlayTimer);
      autoPlayTimer = null;
    }
  }

  function resetAutoPlay() {
    stopAutoPlay();
    startAutoPlay();
  }

  // Pause on mouse hover, resume on mouse leave
  sliderContainer.addEventListener('mouseenter', stopAutoPlay);
  sliderContainer.addEventListener('mouseleave', startAutoPlay);

  // Keyboard navigation when focusing within slider
  sliderContainer.addEventListener('keydown', function (event) {
    if (event.key === 'ArrowLeft') {
      prevSlide();
      resetAutoPlay();
    } else if (event.key === 'ArrowRight') {
      nextSlide();
      resetAutoPlay();
    }
  });

  // Start auto-play initially
  goToSlide(0);
  startAutoPlay();
}

window.StudentHubSlider = {
  init: initSlider
};
