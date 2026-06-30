/**
 * NutriVitaX Pro — JavaScript principal du thème
 *
 * Gère le dark mode, les interactions de base et les communications
 * AJAX avec le serveur. Minimal et sans dépendance lourde.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

/* eslint-disable no-unused-vars */

/**
 * NutriVitaX Pro - Objet principal du thème
 */
const NVX = {

	/**
	 * Initialisation
	 */
	init() {
		this.darkMode();
		this.smoothScroll();
		this.accessibility();
	},

	/**
	 * Gestion du dark mode
	 * Lit l'attribut data-nvx-dark-mode sur <html>
	 */
	darkMode() {
		const html = document.documentElement;
		const mode = html.getAttribute( 'data-nvx-dark-mode' );

		if ( mode === 'auto' ) {
			const prefersDark = window.matchMedia( '(prefers-color-scheme: dark)' );
			const applyDark = ( isDark ) => {
				html.classList.toggle( 'nvx-dark', isDark );
			};
			applyDark( prefersDark.matches );
			prefersDark.addEventListener( 'change', ( e ) => applyDark( e.matches ) );
		} else if ( mode === 'enabled' ) {
			html.classList.add( 'nvx-dark' );
		}
	},

	/**
	 * Scroll fluide pour les ancres internes
	 */
	smoothScroll() {
		document.addEventListener( 'click', ( e ) => {
			const link = e.target.closest( 'a[href^="#"]' );
			if ( link ) {
				const target = document.querySelector( link.getAttribute( 'href' ) );
				if ( target ) {
					e.preventDefault();
					target.scrollIntoView( { behavior: 'smooth' } );
				}
			}
		} );
	},

	/**
	 * Améliorations d'accessibilité
	 */
	accessibility() {
		// Skip to content link
		const skipLink = document.querySelector( '.skip-link' );
		if ( skipLink ) {
			skipLink.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				const content = document.getElementById( 'nvx-woo-content' ) || document.querySelector( 'main' );
				if ( content ) {
					content.setAttribute( 'tabindex', '-1' );
					content.focus();
				}
			} );
		}

		// Focus visible uniquement au clavier
		document.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Tab' ) {
				document.body.classList.add( 'nvx-keyboard-nav' );
			}
		} );
		document.addEventListener( 'mousedown', () => {
			document.body.classList.remove( 'nvx-keyboard-nav' );
		} );
	},
};

// Initialisation au DOM ready
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => NVX.init() );
} else {
	NVX.init();
}
