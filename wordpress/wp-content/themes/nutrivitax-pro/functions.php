<?php
/**
 * NutriVitaX Pro — Fonctions principales du thème
 *
 * Ce fichier est le point d'entrée unique du thème. Il charge le bootstrap
 * (inc/setup.php) uniquement si NutriVitaX Pro est le thème actif,
 * garantissant ZERO conflit avec les autres thèmes installés.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

// ─── Constantes du thème ───────────────────────────────────────────────
if ( ! defined( 'NVX_VERSION' ) ) {
        define( 'NVX_VERSION', '0.1.0' );
}
if ( ! defined( 'NVX_DIR' ) ) {
        define( 'NVX_DIR', get_template_directory() );
}
if ( ! defined( 'NVX_URI' ) ) {
        define( 'NVX_URI', get_template_directory_uri() );
}
if ( ! defined( 'NVX_INC' ) ) {
        define( 'NVX_INC', NVX_DIR . '/inc' );
}
if ( ! defined( 'NVX_ASSETS' ) ) {
        define( 'NVX_ASSETS', NVX_URI . '/assets' );
}
if ( ! defined( 'NVX_MIN_PHP' ) ) {
        define( 'NVX_MIN_PHP', '8.3' );
}
if ( ! defined( 'NVX_MIN_WP' ) ) {
        define( 'NVX_MIN_WP', '6.7' );
}

// ─── Vérification que le thème est actif (anti-conflit) ─────────────────
/**
 * Retourne true uniquement si NutriVitaX Pro est le thème actif.
 * Toutes les initialisations passent par ce guard pour éviter
 * d'interférer avec un autre thème actif.
 *
 * @since  0.1.0
 * @return bool
 */
function nvx_is_theme_active(): bool {
        $theme = wp_get_theme();
        return (
                $theme->get( 'stylesheet' ) === 'nutrivitax-pro'
                || $theme->get( 'template' ) === 'nutrivitax-pro'
        );
}

// ─── Initialisation des options par défaut (idempotent, sans guard) ──
// Ces options sont préfixées nvx_ et ne créent aucun conflit.
// Elles se mettent à jour uniquement si la version a changé ou si elles
// n'existent pas encore. Ceci est sûr car update_option() est idempotent.
if ( function_exists( 'get_option' ) && function_exists( 'update_option' ) ) {
        $nvx_defaults = array(
                'nvx_theme_version'  => NVX_VERSION,
                'nvx_dark_mode'     => 'auto',
                'nvx_show_trust_bar'=> 'yes',
                'nvx_quiz_enabled'  => 'yes',
                'nvx_stack_enabled' => 'no',
                'nvx_ia_enabled'    => 'no',
                'nvx_loyalty_enabled'=> 'no',
        );
        foreach ( $nvx_defaults as $key => $value ) {
                if ( get_option( $key ) === false ) {
                        update_option( $key, $value );
                }
        }
        unset( $nvx_defaults );
}

// ─── Guard principal : ne rien charger si un autre thème est actif ───
if ( ! nvx_is_theme_active() ) {
        return;
}

// ─── Vérifications de compatibilité ─────────────────────────────────────
/**
 * Vérifie que la version de PHP est suffisante.
 * Affiche un avis administrateur si ce n'est pas le cas, mais ne crash pas.
 *
 * @since  0.1.0
 */
function nvx_check_php_version(): void {
        if ( version_compare( PHP_VERSION, NVX_MIN_PHP, '<' ) ) {
                add_action( 'admin_notices', function () {
                        $message = sprintf(
                                /* translators: 1: theme name, 2: PHP version, 3: required version */
                                esc_html__( '%1$s nécessite PHP %3$s ou supérieur. Vous utilisez PHP %2$s.', 'nutrivitax-pro' ),
                                'NutriVitaX Pro',
                                PHP_VERSION,
                                NVX_MIN_PHP
                        );
                        printf(
                                '<div class="notice notice-error"><p>%s</p></div>',
                                esc_html( $message )
                        );
                } );
                return;
        }
}
add_action( 'after_setup_theme', 'nvx_check_php_version', 1 );

// ─── Chargement du bootstrap ───────────────────────────────────────────
require_once NVX_INC . '/setup.php';

// ─── Hook d'activation du thème ────────────────────────────────────────
/**
 * Actions exécutées lors de l'activation de NutriVitaX Pro.
 * Ne modifie PAS les options globales d'autres thèmes.
 *
 * @since  0.1.0
 */
function nvx_activate_theme(): void {
        // Flag d'activation (pour tracking interne)
        update_option( 'nvx_theme_activated', current_time( 'mysql' ) );
        update_option( 'nvx_theme_version', NVX_VERSION );

        // Ajouter les options par défaut du thème (préfixées nvx_)
        $defaults = array(
                'nvx_dark_mode'       => 'auto',
                'nvx_show_trust_bar'  => 'yes',
                'nvx_quiz_enabled'    => 'yes',
                'nvx_stack_enabled'   => 'no',
                'nvx_ia_enabled'      => 'no',
                'nvx_loyalty_enabled' => 'no',
        );

        foreach ( $defaults as $key => $value ) {
                if ( false === get_option( $key ) ) {
                        add_option( $key, $value );
                }
        }

        // Purger le rewrite rules WooCommerce après activation
        if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
        }
}
register_activation_hook( __FILE__, 'nvx_activate_theme' );

// ─── Hook alternatif d'activation via after_setup_theme ─────────────────
// Les options sont déjà initialisées ci-dessus (avant le guard).
// Ce hook permet de les ré-initialiser si nécessaire au switch_theme.
add_action( 'after_switch_theme', function () {
        if ( nvx_is_theme_active() ) {
                $defaults = array(
                        'nvx_theme_version'  => NVX_VERSION,
                        'nvx_dark_mode'     => 'auto',
                        'nvx_show_trust_bar'=> 'yes',
                        'nvx_quiz_enabled'  => 'yes',
                        'nvx_stack_enabled' => 'no',
                        'nvx_ia_enabled'    => 'no',
                        'nvx_loyalty_enabled'=> 'no',
                );
                foreach ( $defaults as $key => $value ) {
                        update_option( $key, $value );
                }
                update_option( 'nvx_theme_activated', current_time( 'mysql' ) );
                if ( function_exists( 'flush_rewrite_rules' ) ) {
                        flush_rewrite_rules();
                }
        }
}, 5 );

// ─── Hook de désactivation du thème ────────────────────────────────────
/**
 * Actions exécutées lors de la désactivation de NutriVitaX Pro.
 * Nettoie uniquement ce que le thème a créé. Ne touche PAS aux
 * options d'autres thèmes ou à WooCommerce.
 *
 * @since  0.1.0
 */
function nvx_deactivate_theme(): void {
        // Ne PAS supprimer les options nvx_* lors de la désactivation
        // pour permettre une réactivation sans perte de données.
        // Suppression uniquement sur uninstall (voir uninstall.php).

        // Purger les rewrite rules
        if ( function_exists( 'flush_rewrite_rules' ) ) {
                flush_rewrite_rules();
        }
}
register_deactivation_hook( __FILE__, 'nvx_deactivate_theme' );

// ─── Hook de changement de thème (cleanup si un autre thème prend le dessus) ─
/**
 * Quand un autre thème est activé, on s'assure que NutriVitaX Pro
 * ne laisse rien trainer dans les hooks globaux.
 *
 * @since  0.1.0
 */
function nvx_on_switch_theme_cleanup( $new_name, $new_theme ): void {
        if ( $new_theme->get( 'stylesheet' ) !== 'nutrivitax-pro' ) {
                // Nettoyer les transients du thème
                delete_transient( 'nvx_menu_locations' );
                delete_transient( 'nvx_css_cache' );
                delete_transient( 'nvx_product_cache' );
                delete_transient( 'nvx_recommendation_cache' );
        }
}
add_action( 'switch_theme', 'nvx_on_switch_theme_cleanup', 10, 2 );

// ─── Fonction utilitaire : chargement conditionnel de fichier ─────────
/**
 * Charge un fichier d'include uniquement s'il existe.
 * Évite les fatal errors si un composant est manquant.
 *
 * @since  0.1.0
 * @param  string $file  Chemin relatif depuis NVX_INC/
 * @return bool
 */
function nvx_load( string $file ): bool {
        $path = NVX_INC . '/' . $file;
        if ( file_exists( $path ) ) {
                require_once $path;
                return true;
        }
        return false;
}

// ─── Auto-chargement des composants (lazy load, pas de conflit) ───────
/**
 * Charge les composants du thème selon le contexte.
 * Chaque composant est chargé uniquement quand c'est nécessaire,
 * réduisant les risques de conflit avec d'autres plugins/themes.
 *
 * @since  0.1.0
 */
function nvx_load_components(): void {
        // Composants toujours actifs (Phase 1)
        nvx_load( 'woo-enhancements.php' );

        // Composants conditionnels
        if ( get_option( 'nvx_quiz_enabled', 'yes' ) === 'yes' ) {
                nvx_load( 'quiz-engine.php' );
        }

        if ( get_option( 'nvx_stack_enabled', 'no' ) === 'yes' ) {
                nvx_load( 'stack-builder.php' );
        }

        if ( get_option( 'nvx_ia_enabled', 'no' ) === 'yes' ) {
                nvx_load( 'ai-recommendations.php' );
        }

        // Hook pour charger des composants personnalisés
        do_action( 'nvx_load_components' );
}
add_action( 'after_setup_theme', 'nvx_load_components', 20 );

// ─── Fonction helper pour vérifier WooCommerce ──────────────────────────
/**
 * Vérifie si WooCommerce est actif et à une version suffisante.
 *
 * @since  0.1.0
 * @return bool
 */
function nvx_is_woo_active(): bool {
        return class_exists( 'WooCommerce' )
                && version_compare( WC_VERSION, '9.0', '>=' );
}

// ─── Fonction helper pour vérifier si le plugin est actif ───────────────
/**
 * Vérifie si un plugin spécifique est actif.
 *
 * @since  0.1.0
 * @param  string $plugin  Plugin basename (ex: 'woocommerce/woocommerce.php')
 * @return bool
 */
function nvx_is_plugin_active( string $plugin ): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $plugin );
}

// ─── URL de sécurité du thème (ne jamais exposer les chemins internes) ─
/**
 * Retourne une URL du thème sans jamais exposer les chemins serveur.
 *
 * @since  0.1.0
 * @param  string $path  Chemin relatif depuis la racine du thème
 * @return string
 */
function nvx_asset_url( string $path = '' ): string {
        return esc_url( NVX_URI . '/' . ltrim( $path, '/' ) );
}
