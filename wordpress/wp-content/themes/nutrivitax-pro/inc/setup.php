<?php
/**
 * NutriVitaX Pro — Theme bootstrap
 * @package NutriVitaX_Pro
 */

defined( 'ABSPATH' ) || exit;

/* ── Theme Support ─────────────────────────────────────────── */
function nvx_setup(): void {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form','comment-form','comment-list','gallery','caption','style','script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'custom-logo', array( 'height' => 100, 'width' => 350, 'flex-height' => true, 'flex-width' => true ) );

	register_nav_menus( array(
		'nvx-primary'   => 'Menu Principal',
		'nvx-secondary' => 'Menu Secondaire',
		'nvx-footer'    => 'Menu Pied de Page',
		'nvx-mobile'    => 'Menu Mobile',
	) );

	add_image_size( 'nvx-product-card', 400, 400, true );
	add_image_size( 'nvx-product-large', 800, 800, true );
	add_image_size( 'nvx-hero-banner', 1920, 800, true );
	add_image_size( 'nvx-blog-card', 600, 400, true );

	if ( ! isset( $GLOBALS['content_width'] ) ) $GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'nvx_setup', 5 );

/* ── Assets ────────────────────────────────────────────────── */
function nvx_enqueue_assets(): void {
	wp_enqueue_style( 'nvx-style', NVX_URI . '/style.css', array(), NVX_VERSION );
	wp_enqueue_style( 'nvx-layers-base', NVX_URI . '/assets/css/layers/base.css', array( 'nvx-style' ), NVX_VERSION );
	wp_enqueue_style( 'nvx-layers-header', NVX_URI . '/assets/css/layers/header.css', array( 'nvx-style' ), NVX_VERSION );

	// Homepage CSS only on front page
	if ( is_front_page() || is_home() ) {
		wp_enqueue_style( 'nvx-layers-homepage', NVX_URI . '/assets/css/layers/homepage.css', array( 'nvx-style' ), NVX_VERSION );
	}

	wp_enqueue_script( 'nvx-header', NVX_URI . '/assets/js/header.js', array(), NVX_VERSION, array( 'strategy' => 'defer' ) );
	wp_enqueue_script( 'nvx-theme', NVX_URI . '/assets/js/theme.js', array( 'nvx-header' ), NVX_VERSION, array( 'strategy' => 'defer' ) );

	// Homepage JS
	if ( is_front_page() || is_home() ) {
		wp_enqueue_script( 'nvx-homepage', NVX_URI . '/assets/js/modules/homepage.js', array( 'nvx-theme' ), NVX_VERSION, array( 'strategy' => 'defer' ) );
	}

	// Remove default block styles
	wp_dequeue_style( 'wp-block-library-theme' );

	// Cart count & AJAX data
	$cart_count = 0;
	if ( function_exists( 'WC' ) && WC()->cart ) $cart_count = WC()->cart->get_cart_contents_count();

	wp_localize_script( 'nvx-theme', 'nvxData', array(
		'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'nvx_nonce' ),
		'homeUrl'      => home_url(),
		'themeUri'     => NVX_URI,
		'themeVersion' => NVX_VERSION,
		'isWoo'        => nvx_is_woo_active(),
		'cartCount'    => $cart_count,
		'i18n'         => array(
			'addedToCart'  => 'Produit ajouté au panier',
			'viewCart'     => 'Voir le panier',
			'loading'      => 'Chargement...',
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'nvx_enqueue_assets' );
add_action( 'wp_enqueue_scripts', function() { wp_dequeue_style( 'wp-block-library-theme' ); }, 100 );

/* ── Body classes ──────────────────────────────────────────── */
function nvx_body_classes( array $classes ): array {
	$classes[] = 'nvx-theme';
	$classes[] = 'nvx-' . sanitize_html_class( wp_get_theme()->get( 'Name' ) );
	$dm = get_option( 'nvx_dark_mode', 'auto' );
	$classes[] = 'nvx-dark-' . $dm;
	if ( nvx_is_woo_active() ) $classes[] = 'nvx-woo-active';
	return $classes;
}
add_filter( 'body_class', 'nvx_body_classes' );

/* ── HTML attributes ───────────────────────────────────────── */
function nvx_html_attrs( string $output ): string {
	return $output . sprintf(
		' data-nvx-dark="%s"',
		esc_attr( get_option( 'nvx_dark_mode', 'auto' ) )
	);
}
add_filter( 'language_attributes', 'nvx_html_attrs' );

/* ── Sidebars ──────────────────────────────────────────────── */
function nvx_sidebars(): void {
	register_sidebar( array(
		'name'        => 'Barre latérale boutique',
		'id'          => 'nvx-shop-sidebar',
		'before_widget' => '<div id="%1$s" class="widget nvx-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="nvx-widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'nvx_sidebars' );

/* ── Textdomain ────────────────────────────────────────────── */
add_action( 'after_setup_theme', function() {
	load_theme_textdomain( 'nutrivitax-pro', NVX_DIR . '/languages' );
}, 3 );

/* ── AJAX handlers ─────────────────────────────────────────── */
nvx_load( 'ajax-handlers.php' );