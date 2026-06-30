/**
 * NutriVitaX Pro — Homepage JavaScript
 *
 * Gère les interactions des 10 sections de la page d'accueil:
 * 1. Particules CSS (Hero)
 * 2. Compteur animé (Trust Bar)
 * 3. Quiz multi-étapes (Onboarding)
 * 4. Flip-card 3D (produits) — géré par CSS, JS pour touch
 * 5. Lazy loading natif (images)
 * 6. Personnalisation dynamique (utilisateur connecté)
 * 7. Newsletter AJAX
 *
 * @package NutriVitaX_Pro
 * @since   0.3.0
 */

/* eslint-disable no-unused-vars */

const NVXHome = {

	/**
	 * Initialize all homepage modules
	 */
	init() {
		this.particles();
		this.counter();
		this.quiz();
		this.personalize();
		this.newsletter();
		this.observeSections();
	},

	/* ═══════════════════════════════════════════════════════════════
	   1. CSS PARTICLES (Hero)
	   Génère 30 particules flottantes dans le hero
	   ═══════════════════════════════════════════════════════════════ */
	particles() {
		const container = document.getElementById( 'nvx-particles' );
		if ( ! container ) return;

		// Respect reduced motion
		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

		const count = window.innerWidth < 768 ? 15 : 30;

		for ( let i = 0; i < count; i++ ) {
			const p = document.createElement( 'span' );
			p.className = 'nvx-particle' + ( Math.random() > 0.7 ? ' nvx-particle--lg' : '' );
			p.style.left = Math.random() * 100 + '%';
			p.style.animationDuration = ( 8 + Math.random() * 12 ) + 's';
			p.style.animationDelay = ( Math.random() * 10 ) + 's';
			p.style.opacity = ( 0.2 + Math.random() * 0.6 ).toString();
			container.appendChild( p );
		}
	},

	/* ═══════════════════════════════════════════════════════════════
	   2. ANIMATED COUNTER (Trust Bar)
	   Anime le compteur de clients de 0 → target
	   ═══════════════════════════════════════════════════════════════ */
	counter() {
		const counters = document.querySelectorAll( '[data-nvx-target]' );
		if ( ! counters.length ) return;

		const observer = new IntersectionObserver( ( entries ) => {
			entries.forEach( ( entry ) => {
				if ( ! entry.isIntersecting ) return;

				const el = entry.target;
				const target = parseInt( el.dataset.nvxTarget, 10 );
				const duration = parseInt( el.dataset.nvxDuration, 10 ) || 2000;
				const start = performance.now();

				const animate = ( now ) => {
					const elapsed = now - start;
					const progress = Math.min( elapsed / duration, 1 );
					// Ease-out cubic
					const eased = 1 - Math.pow( 1 - progress, 3 );
					const current = Math.floor( eased * target );
					el.textContent = current.toLocaleString( 'fr-FR' );

					if ( progress < 1 ) {
						requestAnimationFrame( animate );
					} else {
						el.textContent = target.toLocaleString( 'fr-FR' );
					}
				};

				requestAnimationFrame( animate );
				observer.unobserve( el );
			} );
		}, { threshold: 0.3 } );

		counters.forEach( ( c ) => observer.observe( c ) );
	},

	/* ═══════════════════════════════════════════════════════════════
	   3. QUIZ MULTI-ÉTAPES (Onboarding)
	   Gère la navigation entre les 5 questions + soumission
	   ═══════════════════════════════════════════════════════════════ */
	quiz() {
		const form = document.getElementById( 'nvx-quiz-mini-form' );
		if ( ! form ) return;

		const steps = form.querySelectorAll( '.nvx-quiz-mini__step' );
		const prevBtn = document.getElementById( 'nvx-quiz-prev' );
		const nextBtn = document.getElementById( 'nvx-quiz-next' );
		const submitBtn = document.getElementById( 'nvx-quiz-submit' );
		const progressFill = form.querySelector( '.nvx-quiz-mini__progress-fill' );
		const progressText = form.querySelector( '.nvx-quiz-mini__progress-text' );
		const progressBar = form.closest( '[role="progressbar"]' );

		let currentStep = 1;
		const totalSteps = steps.length;

		/**
		 * Show a specific step
		 */
		function showStep( stepNum ) {
			steps.forEach( ( s ) => {
				s.hidden = ( parseInt( s.dataset.step, 10 ) !== stepNum );
			} );

			// Update progress
			const percent = ( ( stepNum - 1 ) / totalSteps ) * 100;
			if ( progressFill ) progressFill.style.width = percent + '%';
			if ( progressText ) progressText.textContent = `Question ${stepNum} / ${totalSteps}`;
			if ( progressBar ) progressBar.setAttribute( 'aria-valuenow', stepNum );

			// Toggle buttons
			if ( prevBtn ) prevBtn.hidden = ( stepNum === 1 );
			if ( nextBtn ) nextBtn.hidden = ( stepNum === totalSteps );
			if ( submitBtn ) submitBtn.hidden = ( stepNum !== totalSteps );

			currentStep = stepNum;
		}

		/**
		 * Check if current step has a selection
		 */
		function isCurrentStepValid() {
			const currentFieldset = form.querySelector( `[data-step="${currentStep}"] fieldset` );
			if ( ! currentFieldset ) return false;
			return currentFieldset.querySelector( 'input[type="radio"]:checked' ) !== null;
		}

		// Show first step
		showStep( 1 );

		// Next button
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', () => {
				if ( ! isCurrentStepValid() ) {
					// Shake the fieldset
					const fieldset = form.querySelector( `[data-step="${currentStep}"] fieldset` );
					if ( fieldset ) {
						fieldset.style.animation = 'none';
						fieldset.offsetHeight; // reflow
						fieldset.style.animation = 'shake 0.4s ease';
					}
					return;
				}
				if ( currentStep < totalSteps ) {
					showStep( currentStep + 1 );
				}
			} );
		}

		// Previous button
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', () => {
				if ( currentStep > 1 ) {
					showStep( currentStep - 1 );
				}
			} );
		}

		// Submit
		if ( submitBtn ) {
			form.addEventListener( 'submit', ( e ) => {
				e.preventDefault();

				if ( ! isCurrentStepValid() ) return;

				// Collect answers
				const answers = {};
				for ( let i = 1; i <= totalSteps; i++ ) {
					const selected = form.querySelector( `input[name="nvx_q${i}"]:checked` );
					if ( selected ) {
						answers[ `q${i}` ] = selected.value;
					}
				}

				// Store in sessionStorage for the quiz page
				try {
					sessionStorage.setItem( 'nvx_quiz_answers', JSON.stringify( answers ) );
					sessionStorage.setItem( 'nvx_quiz_timestamp', Date.now().toString() );
				} catch ( err ) {
					// Storage full or unavailable — fallback to URL params
					const params = new URLSearchParams( answers );
					window.location.href = '/quiz-sante/?' + params.toString();
					return;
				}

				// Redirect to quiz results / stack builder
				window.location.href = '/quiz-sante/?result=1';
			} );
		}
	},

	/* ═══════════════════════════════════════════════════════════════
	   4. PERSONALISATION DYNAMIQUE
	   Si l'utilisateur est connecté, adapte le Hero
	   ═══════════════════════════════════════════════════════════════ */
	personalize() {
		const el = document.querySelector( '[data-nvx-personalized]' );
		if ( ! el ) return;

		// Only run if user data is available (set via wp_localize_script)
		if ( typeof nvxUser === 'undefined' || ! nvxUser.isLoggedIn ) return;

		const welcomeEl = el.querySelector( '.nvx-hero__welcome-back' );
		if ( welcomeEl ) {
			const firstName = nvxUser.firstName || '';
			welcomeEl.textContent = firstName
				? `Bon retour parmi nous, ${firstName} ! Votre stack personnalisé vous attend.`
				: 'Bon retour ! Votre stack personnalisé vous attend.';
		}
		el.hidden = false;
	},

	/* ═══════════════════════════════════════════════════════════════
	   5. NEWSLETTER AJAX
	   Soumission du formulaire newsletter sans rechargement
	   ═══════════════════════════════════════════════════════════════ */
	newsletter() {
		const form = document.getElementById( 'nvx-newsletter-form' );
		if ( ! form ) return;

		form.addEventListener( 'submit', ( e ) => {
			e.preventDefault();

			const input = form.querySelector( '#nvx-nl-email' );
			const email = input ? input.value.trim() : '';
			const btn = form.querySelector( '.nvx-newsletter__btn' );

			if ( ! email || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
				input.style.borderColor = '#EF4444';
				return;
			}
			input.style.borderColor = '';

			// Optimistic UI
			if ( btn ) {
				btn.disabled = true;
				const origHTML = btn.innerHTML;
				btn.innerHTML = '<span class="nvx-btn-animated__text">Inscription en cours...</span>';
				btn.style.opacity = '0.7';

				// Simulate success (real implementation would use fetch to REST API)
				setTimeout( () => {
					btn.innerHTML = '<span class="nvx-btn-animated__text">✓ Inscrit ! Vérifiez votre email</span>';
					btn.style.opacity = '1';
					btn.style.background = '#2ECC71';
					input.value = '';
					input.disabled = true;

					// Store in localStorage
					try {
						localStorage.setItem( 'nvx_nl_subscribed', '1' );
						localStorage.setItem( 'nvx_nl_email', email );
					} catch ( err ) { /* ignore */ }

					// Reset after 5s
					setTimeout( () => {
						btn.innerHTML = origHTML;
						btn.disabled = false;
						btn.style.background = '';
						input.disabled = false;
					}, 5000 );
				}, 1500 );
			}
		} );
	},

	/* ═══════════════════════════════════════════════════════════════
	   6. INTERSECTION OBSERVER (Animations au scroll)
	   Ajoute .nvx-in-view quand les sections entrent dans le viewport
	   ═══════════════════════════════════════════════════════════════ */
	observeSections() {
		if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) return;

		const sections = document.querySelectorAll(
			'.nvx-trust-bar, .nvx-quiz-section, .nvx-featured-section, ' +
			'.nvx-social-proof, .nvx-univers, .nvx-lab-section, .nvx-blog-section, ' +
			'.nvx-newsletter-section'
		);

		const observer = new IntersectionObserver( ( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'nvx-in-view' );
					observer.unobserve( entry.target );
				}
			} );
		}, {
			threshold: 0.1,
			rootMargin: '0px 0px -50px 0px'
		} );

		sections.forEach( ( s ) => observer.observe( s ) );
	},
};

// Initialize
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => NVXHome.init() );
} else {
	NVXHome.init();
}

// Add shake keyframe for quiz validation
const shakeStyle = document.createElement( 'style' );
shakeStyle.textContent = `
@keyframes shake {
	0%, 100% { transform: translateX(0); }
	20% { transform: translateX(-8px); }
	40% { transform: translateX(8px); }
	60% { transform: translateX(-4px); }
	80% { transform: translateX(4px); }
}
`;
document.head.appendChild( shakeStyle );