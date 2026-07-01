<?php
/**
 * NutriVitaX Pro — Script de désinstallation complète
 *
 * Ce fichier est lu UNIQUEMENT lors de la suppression définitive du thème
 * (via wp-admin/themes.php → Supprimer). Il nettoie TOUTES les options,
 * tables et données créées par le thème. Les données WooCommerce et
 * WordPress ne sont JAMAIS touchées.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// ─── Suppression des options du thème (préfixées nvx_) ─────────────────
$options_to_remove = array(
	'nvx_theme_activated',
	'nvx_theme_version',
	'nvx_dark_mode',
	'nvx_show_trust_bar',
	'nvx_quiz_enabled',
	'nvx_stack_enabled',
	'nvx_ia_enabled',
	'nvx_loyalty_enabled',
	'nvx_ia_provider',
	'nvx_ia_api_key',
	'nvx_map_provider',
	'nvx_map_api_key',
);

foreach ( $options_to_remove as $option ) {
	delete_option( $option );
}

// ─── Suppression des transients ─────────────────────────────────────────
$transients_to_remove = array(
	'nvx_menu_locations',
	'nvx_css_cache',
	'nvx_product_cache',
	'nvx_recommendation_cache',
);

foreach ( $transients_to_remove as $transient ) {
	delete_transient( $transient );
}

// ─── Suppression des tables personnalisées ─────────────────────────────
// Note : Les tables custom (nvx_*) ne sont créées qu'en Phase 2+.
// On les supprime ici si elles existent, pour un nettoyage complet.
global $wpdb;

$custom_tables = array(
	$wpdb->prefix . 'nvx_health_profiles',
	$wpdb->prefix . 'nvx_supplement_stacks',
	$wpdb->prefix . 'nvx_ai_recommendations',
	$wpdb->prefix . 'nvx_loyalty_points',
	$wpdb->prefix . 'nvx_ingredient_origins',
	$wpdb->prefix . 'nvx_quiz_responses',
);

foreach ( $custom_tables as $table ) {
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// ─── Suppression des user meta (si le thème stockait des données utilisateur) ─
$wpdb->query(
	"DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'nvx_%'"
);

// ─── Nettoyage terminé ─────────────────────────────────────────────────
// Aucune donnée WooCommerce ou WordPress n'a été modifiée.
