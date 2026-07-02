/**
 * Velure3 Theme JavaScript
 * @version 1.0.0
 */

(function() {
  'use strict';

  /* ── Sticky Header Scroll Effect ── */
  const navbar = document.getElementById('velure-navbar');
  if (navbar) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      if (currentScroll > 50) {
        navbar.classList.add('velure-scrolled');
      } else {
        navbar.classList.remove('velure-scrolled');
      }
      lastScroll = currentScroll;
    }, { passive: true });
  }

  /* ── Search Overlay ── */
  const searchToggle = document.getElementById('velure-search-toggle');
  const searchOverlay = document.getElementById('velure-search-overlay');
  const searchClose = document.getElementById('velure-search-close');
  const searchInput = document.getElementById('velure-search-input');

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', () => {
      searchOverlay.classList.add('velure-active');
      document.body.style.overflow = 'hidden';
      setTimeout(() => searchInput && searchInput.focus(), 100);
    });

    const closeSearch = () => {
      searchOverlay.classList.remove('velure-active');
      document.body.style.overflow = '';
    };

    if (searchClose) searchClose.addEventListener('click', closeSearch);
    searchOverlay.addEventListener('click', (e) => {
      if (e.target === searchOverlay) closeSearch();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && searchOverlay.classList.contains('velure-active')) {
        closeSearch();
      }
    });
  }

  /* ── Mobile Menu ── */
  const menuToggle = document.getElementById('velure-menu-toggle');
  const mobileMenu = document.getElementById('velure-mobile-menu');
  const mobileOverlay = document.getElementById('velure-mobile-overlay');
  const mobileClose = document.getElementById('velure-mobile-close');

  if (menuToggle && mobileMenu) {
    const openMobile = () => {
      mobileMenu.classList.add('velure-active');
      mobileOverlay.classList.add('velure-active');
      document.body.style.overflow = 'hidden';
    };

    const closeMobile = () => {
      mobileMenu.classList.remove('velure-active');
      mobileOverlay.classList.remove('velure-active');
      document.body.style.overflow = '';
    };

    menuToggle.addEventListener('click', openMobile);
    if (mobileClose) mobileClose.addEventListener('click', closeMobile);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobile);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileMenu.classList.contains('velure-active')) {
        closeMobile();
      }
    });
  }

  /* ── Cart Count (WooCommerce) ── */
  function updateCartCount() {
    const cartCount = document.getElementById('velure-cart-count');
    if (cartCount && typeof wc_add_to_cart_params !== 'undefined') {
      fetch(wc_add_to_cart_params.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=velure_get_cart_count&nonce=' + wc_add_to_cart_params.nonce
      })
      .then(r => r.json())
      .then(data => {
        if (data.count !== undefined) {
          cartCount.textContent = data.count;
          cartCount.style.display = data.count > 0 ? 'flex' : 'none';
        }
      })
      .catch(() => {});
    }
  }

  /* Listen for cart fragments refresh */
  document.body.addEventListener('added_to_cart', updateCartCount);
  document.body.addEventListener('removed_from_cart', updateCartCount);

  /* ── Animate on Scroll ── */
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('velure-visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.velure-animate').forEach(el => {
    observer.observe(el);
  });

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

})();