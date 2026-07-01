<?php
/**
 * Configuration WordPress — SQLite (pas de MySQL requis)
 * Environnement portable similaire à XAMPP/Laragon
 */

// ── SQLite Database Integration ────────────────────────────────────
define('DB_ENGINE', 'sqlite');
define('DB_DIR', __DIR__ . '/wp-content/database/');
define('DB_FILE', 'wordpress.db');

// ── URL et chemin du site ──────────────────────────────────────────
define('WP_HOME',    'http://localhost:8080');
define('WP_SITEURL', 'http://localhost:8080');

// ── Configuration ─────────────────────────────────────────────────
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
define('WP_ALLOW_REPAIR', true);
define('DISABLE_WP_CRON', false);

// ── Sécurité ──────────────────────────────────────────────────────
define('DISALLOW_FILE_EDIT', false);
define('WP_HTTP_BLOCK_EXTERNAL', false);

// ── Clés de sécurité ──────────────────────────────────────────────
define('AUTH_KEY',         'nvx_k1_xR7mP2qW8vL4nK9jF3hD6sA0bC5eG1tY7uI9oP');
define('SECURE_AUTH_KEY',  'nvx_k2_mN3vB6xJ1cR8wL5tH4dF7sA2bE9gK3jM6pQ0rT');
define('LOGGED_IN_KEY',    'nvx_k3_hY4uI7wE2cF9xL6tK5dG8sA3bH0jM4nO7pQ1rU');
define('NONCE_KEY',        'nvx_k4_pQ5vJ8wF3cG0xL9tM6dK4eH7sA1bI2nO5pR3jT');
define('AUTH_SALT',        'nvx_s1_aR6wK9xE3cH0yL4tN7dF5gJ8sA2bM1nO6pQ4rU');
define('SECURE_AUTH_SALT', 'nvx_s2_bS7xL0yF4cG3wK8tO5eH6dJ9sA3bN2nO7pR1jU');
define('LOGGED_IN_SALT',   'nvx_s3_cT8wM1yF5cH4xL7tP6dK0eG9sA4bO3nO8pQ2rU');
define('NONCE_SALT',       'nvx_s4_dU9wN2yF6cG5xL0tQ7dH1eJ8sA5bP4nO9pR3jU');

// ── Préfixe de table ─────────────────────────────────────────────
$table_prefix = 'nvx_';

// ── Chargement WordPress ──────────────────────────────────────────
if ( ! defined('ABSPATH') ) {
    define('ABSPATH', __DIR__ . '/');
}
require_once ABSPATH . 'wp-settings.php';