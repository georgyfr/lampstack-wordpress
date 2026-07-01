<?php
/**
 * NutriVitaX Pro — Hooks WooCommerce de base (Phase 1)
 *
 * Ce fichier contient uniquement les hooks et filtres WooCommerce
 * nécessaires au fonctionnement de base du thème. Il est chargé
 * conditionnellement quand WooCommerce est actif.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre les supports WooCommerce pour le thème.
 *
 * @since  0.1.0
 */
function nvx_woo_setup(): void {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 400,
		'gallery_thumbnail_image_width' => 150,
		'single_image_width'    => 800,
		'product_grid'           => array(
			'default_rows'    => 3,
			'min_rows'        => 1,
			'max_rows'        => 8,
			'default_columns' => 3,
			'min_columns'     => 1,
			'max_columns'     => 6,
		),
	) );

	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'nvx_woo_setup', 10 );


/**
 * Modifie le wrapper HTML WooCommerce pour correspondre au design system.
 * Ces wrappers ne sont actifs QUE si NutriVitaX Pro est le thème actif
 * (garanti par le guard dans functions.php).
 *
 * @since  0.1.0
 */
function nvx_woo_wrapper_start(): string {
	return '<main id="nvx-woo-content" class="nvx-woo-content" role="main">';
}

function nvx_woo_wrapper_end(): string {
	return '</main>';
}

function nvx_woo_wrapper_before(): void {
	echo '<div class="nvx-woo-container">';
}

function nvx_woo_wrapper_after(): void {
	echo '</div>';
}
