<?php
/**
 * SQLite integration (Drop-in) — Customized for mu-plugins path
 *
 * @package wp-sqlite-integration
 */

define( 'SQLITE_DB_DROPIN_VERSION', '1.8.0' );

// Path to the SQLite plugin in mu-plugins
$sqlite_plugin_implementation_folder_path = realpath( __DIR__ . '/mu-plugins/sqlite-database-integration' );

// Bail early if the SQLite implementation was not located.
if ( ! $sqlite_plugin_implementation_folder_path || ! file_exists( $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/db.php' ) ) {
	return;
}

// Constant for backward compatibility.
if ( ! defined( 'DATABASE_TYPE' ) ) {
	define( 'DATABASE_TYPE', 'sqlite' );
}
if ( ! defined( 'DB_ENGINE' ) ) {
	define( 'DB_ENGINE', 'sqlite' );
}

// Require the implementation from the plugin.
require_once $sqlite_plugin_implementation_folder_path . '/wp-includes/sqlite/db.php';