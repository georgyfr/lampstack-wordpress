<?php
/**
 * NutriVitaX Pro — Bootstrap du thème
 *
 * Ce fichier configure les fonctionnalités de base du thème : support de
 * fonctionnalités WordPress, menus, sidebars, tailles d'images, styles
 * et scripts. Tout est préfixé nvx_ pour éviter les conflits.
 *
 * @package NutriVitaX_Pro
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Configuration initiale du thème.
 *
 * @since  0.1.0
 */
function nvx_setup(): void {

        // ── Support des fonctionnalités WordPress ──────────────────────────
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'html5', array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style',
                'script',
                'navigation-widgets',
        ) );
        add_theme_support( 'customize-selective-refresh-widgets' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'wp-block-styles' );
        add_theme_support( 'editor-styles' );

        // Support du logo personnalisé
        add_theme_support( 'custom-logo', array(
                'height'      => 100,
                'width'       => 350,
                'flex-height' => true,
                'flex-width'  => true,
        ) );

        // ── Menus de navigation (préfixés nvx_) ───────────────────────────
        register_nav_menus( array(
                'nvx-primary'   => esc_html__( 'Menu Principal', 'nutrivitax-pro' ),
                'nvx-secondary' => esc_html__( 'Menu Secondaire', 'nutrivitax-pro' ),
                'nvx-footer'    => esc_html__( 'Menu Pied de Page', 'nutrivitax-pro' ),
                'nvx-mobile'    => esc_html__( 'Menu Mobile', 'nutrivitax-pro' ),
        ) );

        // ── Tailles d'images personnalisées (préfixées nvx_) ─────────────
        add_image_size( 'nvx-product-card', 400, 400, true );
        add_image_size( 'nvx-product-large', 800, 800, true );
        add_image_size( 'nvx-hero-banner', 1920, 800, true );
        add_image_size( 'nvx-thumbnail', 150, 150, true );
        add_image_size( 'nvx-blog-card', 600, 400, true );

        // ── Largeur du contenu ─────────────────────────────────────────────
        if ( ! isset( $content_width ) ) {
                $content_width = 1200;
        }
}
add_action( 'after_setup_theme', 'nvx_setup', 5 );


/**
 * Chargement des styles et scripts du thème.
 *
 * Ne charge QUE les assets de NutriVitaX Pro, jamais ceux d'un autre thème.
 * Utilise wp_enqueue avec un préfixe unique pour éviter les collisions
 * de handle.
 *
 * @since  0.1.0
 */
function nvx_enqueue_assets(): void {

        // ── Styles ─────────────────────────────────────────────────────────
        wp_enqueue_style(
                'nvx-style',
                NVX_URI . '/style.css',
                array(),
                NVX_VERSION
        );

        // CSS des couches modulaires
        $layer_files = array(
                'header'    => NVX_DIR . '/assets/css/layers/header.css',
                'homepage'  => NVX_DIR . '/assets/css/layers/homepage.css',
                'base'      => NVX_DIR . '/assets/css/layers/base.css',
        );

        // Charger homepage.css uniquement sur la page d'accueil
        $is_home = ( get_page_template_slug() === 'home' ) || is_page_template( 'home' ) || is_page_template( 'templates/home.html' ) || ( is_front_page() && 'page' === get_option( 'show_on_front' ) );
        if ( ! $is_home && isset( $layer_files['homepage'] ) ) {
                unset( $layer_files['homepage'] );
        }
        foreach ( $layer_files as $name => $file ) {
                if ( file_exists( $file ) ) {
                        wp_enqueue_style(
                                'nvx-layers-' . $name,
                                NVX_URI . '/assets/css/layers/' . $name . '.css',
                                array( 'nvx-style' ),
                                NVX_VERSION
                        );
                }
        }

        // Google Fonts (chargées depuis CDN, conditionnel)
        $google_fonts_url = nvx_get_google_fonts_url();
        if ( $google_fonts_url ) {
                wp_enqueue_style(
                        'nvx-google-fonts',
                        $google_fonts_url,
                        array(),
                        null // null = pas de version pour les fonts externes
                );
        }

        // ── Scripts ───────────────────────────────────────────────────────
        wp_enqueue_script(
                'nvx-header',
                NVX_URI . '/assets/js/header.js',
                array(),
                NVX_VERSION,
                array( 'strategy' => 'defer' )
        );

        wp_enqueue_script(
                'nvx-theme',
                NVX_URI . '/assets/js/theme.js',
                array( 'nvx-header' ),
                NVX_VERSION,
                array( 'strategy' => 'defer' )
        );

        // Homepage JS (uniquement sur la home)
        if ( $is_home && file_exists( NVX_DIR . '/assets/js/modules/homepage.js' ) ) {
                wp_enqueue_script(
                        'nvx-homepage',
                        NVX_URI . '/assets/js/modules/homepage.js',
                        array( 'nvx-theme' ),
                        NVX_VERSION,
                        array( 'strategy' => 'defer' )
                );

                // Passer données utilisateur pour la personnalisation dynamique
                if ( is_user_logged_in() ) {
                        $current_user = wp_get_current_user();
                        wp_localize_script( 'nvx-homepage', 'nvxUser', array(
                                'isLoggedIn' => true,
                                'firstName'  => esc_html( $current_user->first_name ),
                                'profile'    => get_user_meta( $current_user->ID, 'nvx_health_profile', true ),
                        ) );
                } else {
                        wp_localize_script( 'nvx-homepage', 'nvxUser', array(
                                'isLoggedIn' => false,
                        ) );
                }
        }

        // Compteur du panier pour le JS
        $cart_count = 0;
        if ( function_exists( 'WC' ) && WC()->cart ) {
                $cart_count = WC()->cart->get_cart_contents_count();
        }

        // Passer des variables PHP au JavaScript de manière sécurisée
        $nvx_data = array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( 'nvx_theme_nonce' ),
                'homeUrl'   => home_url(),
                'themeUri'  => NVX_URI,
                'isWoo'      => nvx_is_woo_active(),
                'cartCount'  => $cart_count,
                'i18n'      => array(
                        'addedToCart'   => esc_html__( 'Produit ajouté au panier', 'nutrivitax-pro' ),
                        'viewCart'      => esc_html__( 'Voir le panier', 'nutrivitax-pro' ),
                        'loading'       => esc_html__( 'Chargement...', 'nutrivitax-pro' ),
                        'quizRequired'  => esc_html__( 'Veuillez répondre à toutes les questions.', 'nutrivitax-pro' ),
                ),
        );
        wp_localize_script( 'nvx-theme', 'nvxData', $nvx_data );

        // Comment reply script (uniquement sur les pages avec commentaires)
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script(
                        'comment-reply',
                        '',
                        array(),
                        NVX_VERSION,
                        true
                );
        }
}
add_action( 'wp_enqueue_scripts', 'nvx_enqueue_assets' );

/**
 * Retourne l'URL Google Fonts pour les polices NutriVitaX.
 *
 * @since  0.1.0
 * @return string
 */
function nvx_get_google_fonts_url(): string {
        // theme.json handles all fontFace definitions including Google Fonts CDN URLs.
        // This function is kept as a fallback for non-FSE contexts (classic templates).
        // Inter, Space Mono, and DM Serif Display are loaded via theme.json fontFace.
        // Cabinet Grotesk and Clash Display use local woff2 files in /assets/fonts/.
        return '';
}


/**
 * Modifie le body class avec des classes préfixées nvx_.
 * N'utilise JAMAIS de filtre sur les classes d'un autre thème.
 *
 * @since  0.1.0
 * @param  string[] $classes  Classes CSS existantes.
 * @return string[]           Classes modifiées.
 */
function nvx_body_classes( array $classes ): array {
        $classes[] = 'nvx-theme';
        $classes[] = 'nvx-' . sanitize_html_class( wp_get_theme()->get( 'Name' ) );

        // Dark mode
        $dark_mode = get_option( 'nvx_dark_mode', 'auto' );
        if ( 'auto' === $dark_mode ) {
                $classes[] = 'nvx-dark-auto';
        } elseif ( 'enabled' === $dark_mode ) {
                $classes[] = 'nvx-dark-active';
        }

        // WooCommerce actif
        if ( nvx_is_woo_active() ) {
                $classes[] = 'nvx-woo-active';
                if ( is_shop() || is_product_category() || is_product_tag() ) {
                        $classes[] = 'nvx-archive-product';
                }
                if ( is_product() ) {
                        $classes[] = 'nvx-single-product';
                }
        }

        return $classes;
}
add_filter( 'body_class', 'nvx_body_classes' );


/**
 * Ajoute des attributs data-* au <html> pour le dark mode et l'IA.
 * N'interfère jamais avec les attributs d'un autre thème.
 *
 * @since  0.1.0
 */
function nvx_html_attributes( string $output ): string {
        $dark_mode = get_option( 'nvx_dark_mode', 'auto' );
        $ia_enabled = get_option( 'nvx_ia_enabled', 'no' );
        $output .= sprintf(
                ' data-nvx-dark-mode="%s" data-nvx-ia="%s"',
                esc_attr( $dark_mode ),
                esc_attr( $ia_enabled )
        );
        return $output;
}
add_filter( 'language_attributes', 'nvx_html_attributes' );


/**
 * Supprime les styles Gutenberg par défaut qui entrent en conflit
 * avec le design system NutriVitaX Pro (uniquement quand nvx est actif).
 *
 * @since  0.1.0
 */
function nvx_remove_default_block_styles(): void {
        wp_dequeue_style( 'wp-block-library-theme' );
}
add_action( 'wp_enqueue_scripts', 'nvx_remove_default_block_styles', 100 );


/**
 * Enregistre des widget areas (préfixées nvx_) si WooCommerce est actif.
 *
 * @since  0.1.0
 */
function nvx_register_sidebars(): void {
        register_sidebar( array(
                'name'          => esc_html__( 'Barre latérale boutique', 'nutrivitax-pro' ),
                'id'            => 'nvx-shop-sidebar',
                'description'   => esc_html__( 'Apparaît sur les pages de la boutique WooCommerce.', 'nutrivitax-pro' ),
                'before_widget' => '<div id="%1$s" class="widget nvx-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="nvx-widget-title">',
                'after_title'   => '</h3>',
        ) );

        register_sidebar( array(
                'name'          => esc_html__( 'Footer Colonne 1', 'nutrivitax-pro' ),
                'id'            => 'nvx-footer-1',
                'before_widget' => '<div id="%1$s" class="nvx-footer-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="nvx-footer-widget-title">',
                'after_title'   => '</h4>',
        ) );

        register_sidebar( array(
                'name'          => esc_html__( 'Footer Colonne 2', 'nutrivitax-pro' ),
                'id'            => 'nvx-footer-2',
                'before_widget' => '<div id="%1$s" class="nvx-footer-widget %2$s">',
                'after_widget'  => '</div>',
                'before_title'  => '<h4 class="nvx-footer-widget-title">',
                'after_title'   => '</h4>',
        ) );
}
add_action( 'widgets_init', 'nvx_register_sidebars' );


/**
 * Textdomain pour la traduction.
 *
 * @since  0.1.0
 */
function nvx_load_textdomain(): void {
        load_theme_textdomain(
                'nutrivitax-pro',
                NVX_DIR . '/languages'
        );
}
add_action( 'after_setup_theme', 'nvx_load_textdomain', 3 );


/**
 * Largeur du contenu globale (pour les widgets et le content area).
 * Modifiée uniquement via le hook 'after_setup_theme' pour ne pas
 * interférer avec les valeurs d'autres thèmes.
 *
 * @since  0.1.0
 * @global int $content_width
 */
function nvx_content_width(): void {
        $GLOBALS['content_width'] = apply_filters( 'nvx_content_width', 1200 );
}
add_action( 'after_setup_theme', 'nvx_content_width', 6 );


// ─── Shortcodes for FSE templates (replaces PHP in .html files) ──────
/**
 * Returns a theme asset URL. Usage: [nvx_asset_url path="assets/images/photo.jpg"]
 *
 * @since  0.3.0
 * @param  array $atts
 * @return string
 */
function nvx_shortcode_asset_url( $atts ): string {
        $atts = shortcode_atts( array( 'path' => '' ), $atts, 'nvx_asset_url' );
        if ( empty( $atts['path'] ) ) {
                return '';
        }
        return esc_url( NVX_URI . '/' . ltrim( $atts['path'], '/' ) );
}
add_shortcode( 'nvx_asset_url', 'nvx_shortcode_asset_url' );

/**
 * Returns the current year. Usage: [nvx_year]
 *
 * @since  0.3.0
 * @return string
 */
function nvx_shortcode_year(): string {
        return esc_html( date_i18n( 'Y' ) );
}
add_shortcode( 'nvx_year', 'nvx_year' );

/**
 * Returns the theme version. Usage: [nvx_version]
 *
 * @since  0.3.0
 * @return string
 */
function nvx_shortcode_version(): string {
        return esc_html( NVX_VERSION );
}
add_shortcode( 'nvx_version', 'nvx_shortcode_version' );


// ─── AJAX Handlers ────────────────────────────────────────────────────
nvx_load( 'ajax-handlers.php' );
