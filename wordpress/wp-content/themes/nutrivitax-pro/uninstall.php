<?php
/**
 * NutriVitaX Pro — Clean Uninstall
 * Removes all nvx_ options, transients, and theme data.
 */
defined('WP_UNINSTALL_PLUGIN') || defined('WP_UNINSTALL_THEME') || exit;

// Remove all nvx_ options
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'nvx_%'");

// Remove transients
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nvx_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_nvx_%'");