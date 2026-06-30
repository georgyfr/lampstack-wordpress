/**
 * NutriVitaX Pro — Header JavaScript
 *
 * Comportements conformes a la spec header:
 * - Scroll: shrink header (80→64px), hide on scroll down, show on scroll up
 * - Mobile menu: open/close, overlay, body scroll lock, keyboard (Escape, arrows)
 * - Mega menu: hover (desktop), click (mobile), keyboard navigation
 * - Search bar: expand on focus (320→400px)
 * - Cart badge: bounce animation on count change, WooCommerce AJAX sync
 * - Hamburger: 3 lines → X animation (CSS), aria-expanded toggle
 * - All animations GPU-accelerated (transform, opacity)
 *
 * @package NutriVitaX_Pro
 * @since   0.2.0
 */

/* global nvxData */

const NVX_Header = (() => {
	'use strict';

	// ─── DOM Cache ────────────────────────────────────────────────────────
	const $ = (sel, ctx = document) => ctx.querySelector(sel);
	const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

	let header, spacer, hamburger, mobileMenu, mobileOverlay, mobileClose;
	let megaTrigger, megaMenu, navItems;
	let searchInput, searchResults;
	let cartBadge;
	let lastScrollY = 0;
	let scrollTicking = false;
	let mobileOpen = false;
	let megaOpen = false;

	// ─── Init ─────────────────────────────────────────────────────────────
	function init() {
		cacheDom();
		if (!header) return;

		bindScroll();
		bindMobileMenu();
		bindMegaMenu();
		bindSearch();
		bindCartBadge();
		updateSpacer();
		updateCartCount();

		// Listen for WooCommerce cart events
		if (typeof jQuery !== 'undefined') {
			jQuery(document.body).on('added_to_cart removed_from_cart', updateCartFromWoo);
		}
		document.addEventListener('nvx_cart_updated', updateCartFromEvent);
	}

	function cacheDom() {
		header       = $('#nvx-header');
		spacer       = $('#nvx-header-spacer');
		hamburger    = $('#nvx-hamburger-btn');
		mobileMenu   = $('#nvx-mobile-menu');
		mobileOverlay = $('#nvx-mobile-overlay');
		mobileClose  = $('#nvx-mobile-close-btn');
		megaTrigger  = $('#nvx-mega-trigger');
		megaMenu     = $('#nvx-mega-menu');
		searchInput  = $('.nvx-search__input');
		searchResults = $('#nvx-search-results');
		cartBadge    = $('#nvx-cart-count');
		navItems     = $$('.nvx-nav__item');
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   1. SCROLL BEHAVIOR
	   ═══════════════════════════════════════════════════════════════════════ */
	function bindScroll() {
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	function onScroll() {
		if (scrollTicking) return;
		scrollTicking = true;
		requestAnimationFrame(() => {
			const y = window.scrollY;
			const scrollThreshold = 50;

			// Scrolled state (shrink)
			if (y > scrollThreshold) {
				header.classList.add('nvx-header--scrolled');
			} else {
				header.classList.remove('nvx-header--scrolled');
			}

			// Hide/show
			if (y > lastScrollY && y > 200) {
				header.classList.add('nvx-header--hidden');
			} else {
				header.classList.remove('nvx-header--hidden');
			}

			lastScrollY = y;
			updateSpacer();
			scrollTicking = false;
		});
	}

	function updateSpacer() {
		if (!spacer || !header) return;
		const topbar = $('.nvx-topbar');
		let topbarH = 0;
		if (topbar && window.getComputedStyle(topbar).display !== 'none') {
			topbarH = topbar.offsetHeight;
		}
		const headerH = header.offsetHeight;
		spacer.style.height = (topbarH + headerH) + 'px';
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   2. MOBILE MENU
	   ═══════════════════════════════════════════════════════════════════════ */
	function bindMobileMenu() {
		if (!hamburger || !mobileMenu) return;

		hamburger.addEventListener('click', toggleMobile);
		if (mobileClose) mobileClose.addEventListener('click', closeMobile);
		if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobile);

		// Escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && mobileOpen) {
				closeMobile();
				hamburger.focus();
			}
		});

		// Mobile submenu toggles
		$$('.nvx-mobile-nav__item--has-children > .nvx-mobile-nav__link').forEach(btn => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				const item = btn.closest('.nvx-mobile-nav__item');
				const submenu = item.querySelector('.nvx-mobile-submenu');
				const isOpen = item.classList.contains('nvx-mobile-nav__item--open');

				if (isOpen) {
					item.classList.remove('nvx-mobile-nav__item--open');
					btn.setAttribute('aria-expanded', 'false');
					submenu.classList.remove('nvx-mobile-submenu--open');
					submenu.setAttribute('aria-hidden', 'true');
				} else {
					item.classList.add('nvx-mobile-nav__item--open');
					btn.setAttribute('aria-expanded', 'true');
					submenu.classList.add('nvx-mobile-submenu--open');
					submenu.setAttribute('aria-hidden', 'false');
				}
			});
		});

		// Keyboard arrow navigation in mobile menu
		mobileMenu.addEventListener('keydown', handleMobileKeyboard);
	}

	function toggleMobile() {
		mobileOpen ? closeMobile() : openMobile();
	}

	function openMobile() {
		mobileOpen = true;
		hamburger.classList.add('nvx-hamburger--open');
		hamburger.setAttribute('aria-expanded', 'true');
		mobileMenu.classList.add('nvx-mobile-menu--open');
		mobileMenu.setAttribute('aria-hidden', 'false');
		mobileOverlay.classList.add('nvx-mobile-overlay--open');
		mobileOverlay.setAttribute('aria-hidden', 'false');
		document.body.classList.add('nvx-no-scroll');

		// Focus the close button
		if (mobileClose) {
			setTimeout(() => mobileClose.focus(), 350);
		}
	}

	function closeMobile() {
		mobileOpen = false;
		hamburger.classList.remove('nvx-hamburger--open');
		hamburger.setAttribute('aria-expanded', 'false');
		mobileMenu.classList.remove('nvx-mobile-menu--open');
		mobileMenu.setAttribute('aria-hidden', 'true');
		mobileOverlay.classList.remove('nvx-mobile-overlay--open');
		mobileOverlay.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('nvx-no-scroll');
	}

	function handleMobileKeyboard(e) {
		const links = $$('.nvx-mobile-nav__link, .nvx-mobile-submenu__link, .nvx-mobile-menu__action, .nvx-mobile-menu__close');
		const currentIndex = links.indexOf(document.activeElement);

		if (e.key === 'ArrowDown' && currentIndex >= 0) {
			e.preventDefault();
			const next = links[currentIndex + 1];
			if (next) next.focus();
		} else if (e.key === 'ArrowUp' && currentIndex > 0) {
			e.preventDefault();
			links[currentIndex - 1].focus();
		}
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   3. MEGA MENU
	   ═══════════════════════════════════════════════════════════════════════ */
	function bindMegaMenu() {
		if (!megaTrigger || !megaMenu) return;

		// Desktop: hover
		const isDesktop = () => window.innerWidth >= 768;

		megaTrigger.addEventListener('mouseenter', () => {
			if (isDesktop()) openMega();
		});

		megaTrigger.addEventListener('mouseleave', () => {
			if (isDesktop()) closeMega();
		});

		// Click (works on both desktop and mobile, but mobile uses different UI)
		const megaLink = megaTrigger.querySelector('.nvx-nav__link');
		if (megaLink) {
			megaLink.addEventListener('click', (e) => {
				if (isDesktop()) {
					e.preventDefault();
					megaOpen ? closeMega() : openMega();
				}
				// On mobile, the link navigates normally or opens mobile submenu
			});
		}

		// Close on Escape
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && megaOpen) {
				closeMega();
				if (megaLink) megaLink.focus();
			}
		});

		// Close when clicking outside
		document.addEventListener('click', (e) => {
			if (megaOpen && !megaTrigger.contains(e.target)) {
				closeMega();
			}
		});

		// Keyboard navigation inside mega menu
		megaMenu.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
				e.preventDefault();
				const items = $$('[role="menuitem"]', megaMenu);
				const current = items.indexOf(document.activeElement);
				if (current >= 0 && current < items.length - 1) {
					items[current + 1].focus();
				}
			} else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
				e.preventDefault();
				const items = $$('[role="menuitem"]', megaMenu);
				const current = items.indexOf(document.activeElement);
				if (current > 0) {
					items[current - 1].focus();
				}
			}
		});
	}

	function openMega() {
		megaOpen = true;
		megaTrigger.classList.add('nvx-nav__item--open');
		const megaLink = megaTrigger.querySelector('.nvx-nav__link');
		if (megaLink) megaLink.setAttribute('aria-expanded', 'true');
	}

	function closeMega() {
		megaOpen = false;
		megaTrigger.classList.remove('nvx-nav__item--open');
		const megaLink = megaTrigger.querySelector('.nvx-nav__link');
		if (megaLink) megaLink.setAttribute('aria-expanded', 'false');
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   4. SEARCH BAR
	   ═══════════════════════════════════════════════════════════════════════ */
	function bindSearch() {
		if (!searchInput) return;

		let debounceTimer = null;

		searchInput.addEventListener('focus', () => {
			if (searchResults) searchResults.classList.add('nvx-search__results--open');
		});

		searchInput.addEventListener('blur', () => {
			// Delay closing to allow click on results
			setTimeout(() => {
				if (searchResults) searchResults.classList.remove('nvx-search__results--open');
			}, 200);
		});

		searchInput.addEventListener('input', () => {
			clearTimeout(debounceTimer);
			debounceTimer = setTimeout(performSearch, 300);
		});

		// Search form submit
		const searchBtn = $('.nvx-search__btn');
		if (searchBtn) {
			searchBtn.addEventListener('click', () => {
				performSearch();
			});
		}
	}

	function performSearch() {
		if (!searchInput || !searchInput.value.trim()) {
			if (searchResults) {
				searchResults.innerHTML = '';
				searchResults.classList.remove('nvx-search__results--open');
			}
			return;
		}

		if (typeof nvxData === 'undefined' || !nvxData.ajaxUrl) return;

		const formData = new FormData();
		formData.append('action', 'nvx_product_search');
		formData.append('nonce', nvxData.nonce || '');
		formData.append('query', searchInput.value.trim());

		fetch(nvxData.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
		.then(r => r.json())
		.then(data => {
			if (searchResults && data.success && data.data) {
				searchResults.innerHTML = data.data;
				searchResults.classList.add('nvx-search__results--open');
			}
		})
		.catch(() => {
			// Silent fail for search
		});
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   5. CART BADGE
	   ═══════════════════════════════════════════════════════════════════════ */
	function bindCartBadge() {
		// Listen for custom events from fragments
		if (typeof jQuery !== 'undefined') {
			jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', updateCartFromFragments);
		}
	}

	function updateCartCount() {
		if (!cartBadge) return;

		// Try to get count from WooCommerce cart fragment
		if (typeof nvxData !== 'undefined' && nvxData.isWoo) {
			if (typeof jQuery !== 'undefined') {
				const cartCountEl = jQuery('.woocommerce-cart-count');
				if (cartCountEl.length) {
					const count = parseInt(cartCountEl.text(), 10) || 0;
					setCartCount(count);
					return;
				}
			}
		}

		// Fallback: try to read from nvxData
		if (typeof nvxData !== 'undefined' && nvxData.cartCount !== undefined) {
			setCartCount(nvxData.cartCount);
		}
	}

	function setCartCount(count) {
		if (!cartBadge) return;
		const current = parseInt(cartBadge.textContent, 10) || 0;
		if (count !== current) {
			cartBadge.textContent = count;
			cartBadge.setAttribute('aria-label', count + ' articles dans le panier');
			cartBadge.classList.remove('nvx-cart-badge--bounce');
			// Force reflow for animation restart
			void cartBadge.offsetWidth;
			cartBadge.classList.add('nvx-cart-badge--bounce');
		}
	}

	function updateCartFromWoo(e, fragments) {
		if (fragments && fragments['.woocommerce-cart-count']) {
			const match = fragments['.woocommerce-cart-count'].match(/\d+/);
			if (match) {
				setCartCount(parseInt(match[0], 10));
			}
		}
		// Also try to get from the updated DOM
		setTimeout(updateCartCount, 100);
	}

	function updateCartFromEvent(e) {
		if (e.detail && e.detail.count !== undefined) {
			setCartCount(e.detail.count);
		}
	}

	function updateCartFromFragments() {
		setTimeout(updateCartCount, 100);
	}


	/* ═══════════════════════════════════════════════════════════════════════
	   6. PUBLIC API
	   ═══════════════════════════════════════════════════════════════════════ */
	return {
		init,
		openMobile,
		closeMobile,
		openMega,
		closeMega,
		updateSpacer,
		updateCartCount: setCartCount,
	};
})();

// ─── Boot ────────────────────────────────────────────────────────────────
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => NVX_Header.init());
} else {
	NVX_Header.init();
}