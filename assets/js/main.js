/**
 * assets/js/main.js
 * Main JavaScript file for BlogWithMe (clean, robust, and DOM-ready)
 */

document.addEventListener('DOMContentLoaded', function() {
  initDeleteConfirmation();
  initFlashMessages();
  initFormValidation();
  initMobileMenu();
  initCharacterCounter(); // call the character counter on load
});

/**
 * Confirm before deleting a blog post
 * Uses event delegation to catch links added after DOM load too.
 */
function initDeleteConfirmation() {
  document.body.addEventListener('click', function(e) {
    const el = e.target.closest('a');
    if (!el) return;
    if (el.getAttribute('href') && el.getAttribute('href').includes('delete_blog.php')) {
      const confirmed = confirm(
        'Are you sure you want to delete this blog post? This action cannot be undone.'
      );
      if (!confirmed) e.preventDefault();
    }
  });
}

/**
 * Auto-hide flash messages after 5 seconds
 */
function initFlashMessages() {
  const messages = document.querySelectorAll('.success-message, .alert, .user-message');

  messages.forEach(message => {
    setTimeout(() => {
      message.style.transition = 'opacity 0.4s ease, max-height 0.4s ease';
      message.style.opacity = '0';
      message.style.maxHeight = '0';
      setTimeout(() => {
        if (message.parentNode) message.parentNode.removeChild(message);
      }, 450);
    }, 5000);
  });
}

/**
 * Client-side form validation for required fields
 */
function initFormValidation() {
  const forms = document.querySelectorAll('form');

  forms.forEach(form => {
    form.addEventListener('submit', function(e) {
      const inputs = form.querySelectorAll('input[required], textarea[required]');
      let isValid = true;

      inputs.forEach(input => {
        if (!input.value.trim()) {
          isValid = false;
          input.style.borderColor = 'red';
        } else {
          input.style.borderColor = '';
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
      }
    });
  });
}

/**
 * Mobile menu toggle (simple)
 */
function initMobileMenu() {
  const toggle = document.getElementById('navToggle');
  const menu = document.getElementById('navMenu');

  if (!toggle || !menu) return;

  toggle.addEventListener('click', function(e) {
    e.stopPropagation();
    menu.classList.toggle('active');
  });

  document.addEventListener('click', function(e) {
    if (!toggle.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove('active');
    }
  });
}

/**
 * Character counter for textareas with maxlength attribute
 */
function initCharacterCounter() {
  const textareas = document.querySelectorAll('textarea[maxlength], textarea[maxlength]');

  textareas.forEach(textarea => {
    const maxLength = parseInt(textarea.getAttribute('maxlength') || textarea.getAttribute('maxLength'), 10);
    if (!maxLength) return;

    const counter = document.createElement('div');
    counter.className = 'char-counter';
    counter.style.textAlign = 'right';
    counter.style.fontSize = '0.9em';
    counter.style.color = '#666';
    counter.style.marginTop = '5px';

    textarea.parentNode.insertBefore(counter, textarea.nextSibling);

    function updateCounter() {
      const remaining = maxLength - textarea.value.length;
      counter.textContent = `${remaining} characters remaining`;
      counter.style.color = remaining < 50 ? 'red' : '#666';
    }

    textarea.addEventListener('input', updateCounter);
    updateCounter();
  });
}

/**
 * Exported helper function that can be used inline if needed,
 * e.g. <a href="#" onclick="confirmDelete(event)">Delete</a>
 */
function confirmDelete(event) {
  if (!confirm('Are you sure you want to delete this blog post?')) {
    event.preventDefault();
  }
}
