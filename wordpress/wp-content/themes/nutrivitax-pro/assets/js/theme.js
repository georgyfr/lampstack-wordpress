/**
 * NutriVitaX Pro — Main theme JS
 * Dark mode, smooth scroll, accessibility, footer dynamics
 */
const NVX = {
	init() {
		this.darkMode();
		this.smoothScroll();
		this.accessibility();
		this.footerDynamic();
	},

	darkMode() {
		const html = document.documentElement;
		const mode = html.getAttribute('data-nvx-dark');
		if (mode === 'auto') {
			const mq = window.matchMedia('(prefers-color-scheme: dark)');
			html.classList.toggle('nvx-dark', mq.matches);
			mq.addEventListener('change', e => html.classList.toggle('nvx-dark', e.matches));
		} else if (mode === 'enabled') {
			html.classList.add('nvx-dark');
		}
	},

	smoothScroll() {
		document.addEventListener('click', e => {
			const a = e.target.closest('a[href^="#"]');
			if (a) {
				const t = document.querySelector(a.getAttribute('href'));
				if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
			}
		});
	},

	accessibility() {
		const skip = document.querySelector('.nvx-skip-link');
		if (skip) {
			skip.addEventListener('click', e => {
				e.preventDefault();
				const c = document.getElementById('nvx-woo-content') || document.querySelector('main');
				if (c) { c.setAttribute('tabindex', '-1'); c.focus(); }
			});
		}
		document.addEventListener('keydown', e => {
			if (e.key === 'Tab') document.body.classList.add('nvx-keyboard-nav');
		});
		document.addEventListener('mousedown', () => {
			document.body.classList.remove('nvx-keyboard-nav');
		});
	},

	footerDynamic() {
		document.querySelectorAll('[data-nvx-dynamic="year"]').forEach(el => {
			el.textContent = new Date().getFullYear();
		});
		document.querySelectorAll('[data-nvx-dynamic="version"]').forEach(el => {
			el.textContent = (typeof nvxData !== 'undefined' && nvxData.themeVersion)
				? nvxData.themeVersion : '1.0.0';
		});
	}
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => NVX.init());
} else {
	NVX.init();
}