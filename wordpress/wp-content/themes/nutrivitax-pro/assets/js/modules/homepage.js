/**
 * NutriVitaX Pro — Homepage JS
 * Particles, counter, quiz, newsletter, scroll animations
 */
const NVXHome = {
	init() {
		this.particles();
		this.counter();
		this.quiz();
		this.newsletter();
		this.observeSections();
	},

	particles() {
		const c = document.getElementById('nvx-particles');
		if (!c || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		const n = window.innerWidth < 768 ? 15 : 30;
		for (let i = 0; i < n; i++) {
			const p = document.createElement('span');
			p.className = 'nvx-particle' + (Math.random() > 0.7 ? ' nvx-particle--lg' : '');
			p.style.left = Math.random() * 100 + '%';
			p.style.animationDuration = (8 + Math.random() * 12) + 's';
			p.style.animationDelay = (Math.random() * 10) + 's';
			p.style.opacity = (0.2 + Math.random() * 0.6).toString();
			c.appendChild(p);
		}
	},

	counter() {
		document.querySelectorAll('[data-nvx-target]').forEach(el => {
			new IntersectionObserver(entries => {
				entries.forEach(entry => {
					if (!entry.isIntersecting) return;
					const target = parseInt(el.dataset.nvxTarget, 10);
					const duration = parseInt(el.dataset.nvxDuration, 10) || 2000;
					const start = performance.now();
					const animate = now => {
						const p = Math.min((now - start) / duration, 1);
						const eased = 1 - Math.pow(1 - p, 3);
						el.textContent = Math.floor(eased * target).toLocaleString('fr-FR');
						if (p < 1) requestAnimationFrame(animate);
						else el.textContent = target.toLocaleString('fr-FR');
					};
					requestAnimationFrame(animate);
					observer.unobserve(el);
				});
			}, { threshold: 0.3 }).observe(el);
		});
	},

	quiz() {
		const form = document.getElementById('nvx-quiz-mini-form');
		if (!form) return;
		const steps = form.querySelectorAll('.nvx-quiz-mini__step');
		const prev = document.getElementById('nvx-quiz-prev');
		const next = document.getElementById('nvx-quiz-next');
		const submit = document.getElementById('nvx-quiz-submit');
		const fill = form.querySelector('.nvx-quiz-mini__progress-fill');
		const pText = form.querySelector('.nvx-quiz-mini__progress-text');
		let cur = 1;
		const total = steps.length;

		const show = n => {
			steps.forEach(s => s.hidden = parseInt(s.dataset.step) !== n);
			if (fill) fill.style.width = ((n - 1) / total * 100) + '%';
			if (pText) pText.textContent = `Question ${n} / ${total}`;
			if (prev) prev.hidden = n === 1;
			if (next) next.hidden = n === total;
			if (submit) submit.hidden = n !== total;
			cur = n;
		};

		const valid = () => {
			const fs = form.querySelector(`[data-step="${cur}"] fieldset`);
			return fs && fs.querySelector('input:checked') !== null;
		};

		show(1);

		if (next) next.addEventListener('click', () => {
			if (!valid()) {
				const fs = form.querySelector(`[data-step="${cur}"] fieldset`);
				if (fs) { fs.style.animation = 'none'; fs.offsetHeight; fs.style.animation = 'nvx-shake 0.4s ease'; }
				return;
			}
			if (cur < total) show(cur + 1);
		});

		if (prev) prev.addEventListener('click', () => { if (cur > 1) show(cur - 1); });

		if (submit) form.addEventListener('submit', e => {
			e.preventDefault();
			const answers = {};
			for (let i = 1; i <= total; i++) {
				const sel = form.querySelector(`input[name="nvx_q${i}"]:checked`);
				if (sel) answers[`q${i}`] = sel.value;
			}
			try {
				sessionStorage.setItem('nvx_quiz_answers', JSON.stringify(answers));
				window.location.href = '/quiz-sante/?result=1';
			} catch { window.location.href = '/quiz-sante/'; }
		});
	},

	newsletter() {
		const form = document.getElementById('nvx-newsletter-form');
		if (!form) return;
		form.addEventListener('submit', e => {
			e.preventDefault();
			const input = form.querySelector('#nvx-nl-email');
			const email = input?.value.trim();
			if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
				if (input) input.style.borderColor = '#EF4444';
				return;
			}
			if (input) input.style.borderColor = '';
			try { localStorage.setItem('nvx_nl_subscribed', '1'); } catch {}
			const btn = form.querySelector('.nvx-newsletter__btn');
			if (btn) {
				btn.textContent = 'Inscription réussie !';
				btn.style.background = '#2ECC71';
				input.value = '';
				setTimeout(() => { btn.textContent = 'Obtenir mon programme IA'; btn.style.background = ''; }, 4000);
			}
		});
	},

	observeSections() {
		if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
		document.querySelectorAll('.nvx-trust-bar,.nvx-quiz-section,.nvx-featured-section,.nvx-social-proof,.nvx-univers,.nvx-lab-section,.nvx-blog-section,.nvx-newsletter-section').forEach(s => {
			new IntersectionObserver(entries => {
				entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('nvx-in-view'); o.unobserve(e.target); } });
			}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }).observe(s);
		});
	}
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', () => NVXHome.init());
} else {
	NVXHome.init();
}