<?php
/**
 * Velure3 Theme Functions
 *
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VELURE3_VERSION', '1.0.0' );
define( 'VELURE3_DIR', get_template_directory() );
define( 'VELURE3_URI', get_template_directory_uri() );

/* ── Theme Setup ── */
add_action( 'after_setup_theme', 'velure3_setup' );
function velure3_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	register_nav_menus( array(
		'primary'   => __( 'Primary Menu', 'velure3' ),
		'footer'    => __( 'Footer Menu', 'velure3' ),
		'social'    => __( 'Social Links', 'velure3' ),
	) );
}

/* ── Enqueue Assets ── */
add_action( 'wp_enqueue_scripts', 'velure3_assets' );
function velure3_assets() {
	wp_enqueue_style( 'velure3-base', VELURE3_URI . '/assets/css/base.css', array(), VELURE3_VERSION );
	wp_enqueue_style( 'velure3-components', VELURE3_URI . '/assets/css/components.css', array( 'velure3-base' ), VELURE3_VERSION );
	wp_enqueue_script( 'velure3-theme', VELURE3_URI . '/assets/js/theme.js', array(), VELURE3_VERSION, true );
}

/* ── WooCommerce Customizations ── */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'velure3_wrapper_start', 10 );
function velure3_wrapper_start() {
	echo '<div class="velure-content-wrapper">';
}

add_action( 'woocommerce_after_main_content', 'velure3_wrapper_end', 10 );
function velure3_wrapper_end() {
	echo '</div>';
}

/* ── Product columns ── */
add_filter( 'loop_shop_columns', 'velure3_loop_columns' );
function velure3_loop_columns() {
	return 4;
}

add_filter( 'loop_shop_per_page', 'velure3_products_per_page' );
function velure3_products_per_page() {
	return 12;
}

/* ── Remove default WooCommerce styles if using FSE ── */
add_filter( 'woocommerce_enqueue_styles', 'velure3_dequeue_woo_styles' );
function velure3_dequeue_woo_styles( $enqueue_styles ) {
	unset( $enqueue_styles['woocommerce-general'] );
	return $enqueue_styles;
}