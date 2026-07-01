<?php
/**
 * NutriVitaX Pro — FSE Block Theme pour nutraceutique
 *
 * @package NutriVitaX_Pro
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// ─── Constants ────────────────────────────────────────────────────
define( 'NVX_VERSION', '1.0.0' );
define( 'NVX_DIR', get_template_directory() );
define( 'NVX_URI', get_template_directory_uri() );
define( 'NVX_INC', NVX_DIR . '/inc' );
define( 'NVX_ASSETS', NVX_URI . '/assets' );

// ─── Guard: only load when this theme is active ──────────────────
function nvx_is_active(): bool {
	$theme = wp_get_theme();
	return $theme->get( 'stylesheet' ) === 'nutrivitax-pro';
}

// ─── Default options (idempotent, safe before guard) ──────────────
if ( function_exists( 'get_option' ) ) {
	foreach ( array(
		'nvx_version'      => NVX_VERSION,
		'nvx_dark_mode'    => 'auto',
		'nvx_quiz_enabled' => 'yes',
		'nvx_stack_enabled'=> 'no',
	) as $k => $v ) {
		if ( get_option( $k ) === false ) update_option( $k, $v );
	}
}

if ( ! nvx_is_active() ) return;

// ─── Bootstrap ────────────────────────────────────────────────────
require_once NVX_INC . '/setup.php';

// ─── Helpers ──────────────────────────────────────────────────────
function nvx_is_woo_active(): bool {
	return class_exists( 'WooCommerce' ) && version_compare( WC_VERSION, '9.0', '>=' );
}

function nvx_load( string $file ): bool {
	$path = NVX_INC . '/' . $file;
	if ( file_exists( $path ) ) { require_once $path; return true; }
	return false;
}

// ─── Components (lazy) ────────────────────────────────────────────
function nvx_load_components(): void {
	nvx_load( 'woo-enhancements.php' );
	if ( get_option( 'nvx_quiz_enabled', 'yes' ) === 'yes' ) nvx_load( 'quiz-engine.php' );
}
add_action( 'after_setup_theme', 'nvx_load_components', 20 );

// ─── Activation / Deactivation ────────────────────────────────────
register_activation_hook( __FILE__, function() {
	update_option( 'nvx_version', NVX_VERSION );
	update_option( 'nvx_activated', current_time( 'mysql' ) );
	// Set front page to "latest posts" so front-page.html is used
	update_option( 'show_on_front', 'posts' );
	update_option( 'blogname', 'NutriVitaX Pro' );
	update_option( 'blogdescription', 'Compléments alimentaires premium validés par la science' );
	if ( function_exists( 'flush_rewrite_rules' ) ) flush_rewrite_rules();
} );

add_action( 'after_switch_theme', function() {
	if ( nvx_is_active() ) {
		update_option( 'show_on_front', 'posts' );
		update_option( 'blogname', 'NutriVitaX Pro' );
		update_option( 'blogdescription', 'Compléments alimentaires premium validés par la science' );
		if ( function_exists( 'flush_rewrite_rules' ) ) flush_rewrite_rules();
	}
}, 5 );

// ─── One-time cleanup (delete default post, disable store notice) ──
add_action( 'after_setup_theme', function() {
	if ( get_option( 'nvx_cleanup_done' ) ) return;

	// Delete default "Hello world" / "Bonjour tout le monde" post
	foreach ( array( 'Hello world!', 'Bonjour tout le monde !' ) as $title ) {
		$p = get_page_by_title( $title, OBJECT, 'post' );
		if ( $p ) wp_delete_post( $p->ID, true );
	}

	// Disable WooCommerce demo notice
	update_option( 'woocommerce_demo_store', 'no' );

	update_option( 'nvx_cleanup_done', '1' );
}, 99 );