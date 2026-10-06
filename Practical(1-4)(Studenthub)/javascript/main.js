/**
 * StudentHub Portal - Main Application JavaScript
 * Central controller that initializes:
 * 1. Theme Switcher (Light/Dark mode & localStorage persistence)
 * 2. Mobile Navigation & Hamburger Menu
 * 3. Dynamic Notification Banner
 * 4. Collapsible FAQ Accordion & Dynamic JSON Loader (faqs.json)
 * 5. Interactive Modal Popup Dialogs
 * 6. Responsive Image/Content Slider
 * 7. Dynamic Greeting and Live Time Utility
 * 8. Dynamic Campus Events & Circulars (events.json)
 * 9. Dynamic Student Directory & Profiles (students.json)
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Initialize Light/Dark Theme & LocalStorage
  if (window.StudentHubTheme && typeof window.StudentHubTheme.init === 'function') {
    window.StudentHubTheme.init();
  }

  // 2. Initialize Hamburger Mobile Navigation
  if (window.StudentHubNav && typeof window.StudentHubNav.init === 'function') {
    window.StudentHubNav.init();
  }

  // 3. Initialize Notification Banner
  if (window.StudentHubNotification && typeof window.StudentHubNotification.init === 'function') {
    window.StudentHubNotification.init();
  }

  // 4. Initialize FAQ Module (faqs.json)
  if (window.StudentHubFAQ && typeof window.StudentHubFAQ.init === 'function') {
    window.StudentHubFAQ.init();
  }

  // 5. Initialize Modals
  if (window.StudentHubModal && typeof window.StudentHubModal.init === 'function') {
    window.StudentHubModal.init();
  }

  // 6. Initialize Content Slider
  if (window.StudentHubSlider && typeof window.StudentHubSlider.init === 'function') {
    window.StudentHubSlider.init();
  }

  // 7. Dynamic Campus Events Module (events.json)
  if (window.StudentHubEvents && typeof window.StudentHubEvents.init === 'function') {
    window.StudentHubEvents.init();
  }

  // 8. Dynamic Student Profiles Module (students.json)
  if (window.StudentHubStudents && typeof window.StudentHubStudents.init === 'function') {
    window.StudentHubStudents.init();
  }

  // 9. Dynamic Student Greeting
  const greetingElement = document.getElementById('dynamic-greeting');
  if (greetingElement) {
    const currentHour = new Date().getHours();
    let greetingText = 'Good day';
    if (currentHour < 12) {
      greetingText = 'Good morning';
    } else if (currentHour < 17) {
      greetingText = 'Good afternoon';
    } else {
      greetingText = 'Good evening';
    }
    greetingElement.textContent = `${greetingText}!`;
  }

  // 10. Dynamic Notification Demo Buttons
  const alertDemoBtn = document.getElementById('trigger-alert-btn');
  if (alertDemoBtn) {
    alertDemoBtn.addEventListener('click', function () {
      if (window.StudentHubNotification) {
        window.StudentHubNotification.show('📢 Mid-Term Exam schedule for Semester 4 has been released! Check the Exams tab for details.', 'alert');
      }
    });
  }
});
