/**
 * NutriVitaX Pro — Header JavaScript
 * Scroll shrink/hide, mega menu, mobile menu, search toggle
 */
const NVXHeader = {
	init() {
		this.scrollBehavior();
		this.megaMenu();
		this.mobileMenu();
		this.searchToggle();
	},

	scrollBehavior() {
		const header = document.getElementById('nvx-header');
		const spacer = document.getElementById('nvx-header-spacer');
		if (!header) return;
		let lastY = 0, ticking = false;

		const onScroll = () => {
			const y = window.scrollY;
			header.classList.toggle('nvx-header--scrolled', y > 60);
			if (spacer) spacer.style.height = header.offsetHeight + 'px';
			lastY = y;
			ticking = false;
		};

		window.addEventListener('scroll', () => {
			if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
		}, { passive: true });
		onScroll();
	},

	megaMenu() {
		const trigger = document.getElementById('nvx-mega-trigger');
		const mega = document.getElementById('nvx-mega-menu');
		if (!trigger || !mega) return;

		trigger.addEventListener('mouseenter', () => {
			mega.classList.add('nvx-mega--open');
			trigger.querySelector('a').setAttribute('aria-expanded', 'true');
		});
		trigger.addEventListener('mouseleave', () => {
			mega.classList.remove('nvx-mega--open');
			trigger.querySelector('a').setAttribute('aria-expanded', 'false');
		});

		// Keyboard
		trigger.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				mega.classList.remove('nvx-mega--open');
				trigger.querySelector('a').focus();
			}
		});
	},

	mobileMenu() {
		const btn = document.getElementById('nvx-hamburger-btn');
		const menu = document.getElementById('nvx-mobile-menu');
		const overlay = document.getElementById('nvx-mobile-overlay');
		const closeBtn = document.getElementById('nvx-mobile-close-btn');
		if (!btn || !menu) return;

		const open = () => {
			menu.classList.add('nvx-mobile-menu--open');
			if (overlay) overlay.classList.add('nvx-mobile-overlay--open');
			document.body.classList.add('nvx-no-scroll');
			btn.setAttribute('aria-expanded', 'true');
			menu.setAttribute('aria-hidden', 'false');
			closeBtn?.focus();
		};
		const close = () => {
			menu.classList.remove('nvx-mobile-menu--open');
			if (overlay) overlay.classList.remove('nvx-mobile-overlay--open');
			document.body.classList.remove('nvx-no-scroll');
			btn.setAttribute('aria-expanded', 'false');
			menu.setAttribute('aria-hidden', 'true');
			btn.focus();
		};

		btn.addEventListener('click', open);
		closeBtn?.addEventListener('click', close);
		overlay?.addEventListener('click', close);

		// Submenu toggle (Products)
		menu.querySelectorAll('.nvx-mobile-nav__item--has-children > button').forEach(toggle => {
			toggle.addEventListener('click', () => {
				const sub = toggle.nextElementSibling;
				if (!sub) return;
				const expanded = toggle.getAttribute('aria-expanded') === 'true';
				toggle.setAttribute('aria-expanded', String(!expanded));
				sub.setAttribute('aria-hidden', String(expanded));
				sub.style.display = expanded ? 'none' : 'block';
			});
		});

		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && menu.classList.contains('nvx-mobile-menu--open')) close();
		});
	},

	searchToggle() {
		const toggle = document.querySelector('.nvx-search__toggle');
		const form = document.querySelector('.nvx-search');
		const input = document.querySelector('.nvx-search__input');
		if (!toggle || !form) return;

		toggle.addEventListener('click', (e) => {
			e.preventDefault();
			const isOpen = form.classList.toggle('nvx-search--open');
			if (isOpen) input?.focus();
		});

		document.addEventListener('click', (e) => {
			if (!form.contains(e.target) && !toggle.contains(e.target)) {
				form.classList.remove('nvx-search--open');
			}
		});
	}
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => NVXHeader.init());
} else {
	NVXHeader.init();
}