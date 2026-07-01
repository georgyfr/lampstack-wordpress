<?php
/**
 * NutriVitaX Pro — AJAX Handlers
 *
 * Server-side handlers for front-end AJAX requests.
 * Loaded by setup.php after theme bootstrap.
 *
 * @package NutriVitaX_Pro
 * @since   0.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product search AJAX handler.
 * Returns HTML results for the header search dropdown.
 *
 * @since  0.3.0
 */
function nvx_ajax_product_search(): void {
        check_ajax_referer( 'nvx_theme_nonce', 'nonce' );

        $query = sanitize_text_field( wp_unslash( $_POST['query'] ?? '' ) );
        if ( empty( $query ) || mb_strlen( $query ) < 2 ) {
                wp_send_json_success( '' );
        }

        $products = get_posts( array(
                'post_type'      => 'product',
                'posts_per_page' => 5,
                's'              => $query,
                'post_status'    => 'publish',
        ) );

        if ( empty( $products ) ) {
                wp_send_json_success( '<p class="nvx-search__no-results">Aucun produit trouvé.</p>' );
        }

        ob_start();
        foreach ( $products as $product ) {
                $title = get_the_title( $product );
                $link  = get_permalink( $product );
                $thumb = get_the_post_thumbnail( $product, 'nvx-thumbnail', array( 'class' => 'nvx-search__result-thumb' ) );
                $price = function_exists( 'wc_get_product' ) ? wc_get_product( $product )->get_price_html() : '';

                printf(
                        '<a href="%s" class="nvx-search__result-item">%s<div class="nvx-search__result-info"><span class="nvx-search__result-title">%s</span><span class="nvx-search__result-price">%s</span></div></a>',
                        esc_url( $link ),
                        $thumb,
                        esc_html( $title ),
                        $price
                );
        }
        $html = ob_get_clean();

        wp_send_json_success( $html );
}
add_action( 'wp_ajax_nvx_product_search', 'nvx_ajax_product_search' );
add_action( 'wp_ajax_nopriv_nvx_product_search', 'nvx_ajax_product_search' );


/**
 * Newsletter subscription AJAX handler.
 * Stores email in a transient. In production, integrate with a real ESP.
 *
 * @since  0.3.0
 */
function nvx_ajax_newsletter_subscribe(): void {
        check_ajax_referer( 'nvx_theme_nonce', 'nonce' );

        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        if ( ! is_email( $email ) ) {
                wp_send_json_error( array( 'message' => 'Adresse e-mail invalide.' ) );
        }

        // Simple transient-based storage (replace with Mailchimp/Brevo in production)
        $subscribers = get_transient( 'nvx_newsletter_subscribers' );
        if ( ! is_array( $subscribers ) ) {
                $subscribers = array();
        }

        if ( in_array( $email, $subscribers, true ) ) {
                wp_send_json_success( array( 'message' => 'Vous êtes déjà inscrit !' ) );
        }

        $subscribers[] = $email;
        set_transient( 'nvx_newsletter_subscribers', $subscribers, DAY_IN_SECONDS * 365 );

        // Log for admin review
        $log = get_option( 'nvx_newsletter_log', array() );
        $log[] = array(
                'email'      => $email,
                'subscribed' => current_time( 'mysql' ),
                'source'     => 'homepage',
        );
        update_option( 'nvx_newsletter_log', array_slice( $log, -500 ) );

        wp_send_json_success( array( 'message' => 'Inscription réussie ! Vérifiez votre email.' ) );
}
add_action( 'wp_ajax_nvx_newsletter_subscribe', 'nvx_ajax_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_nvx_newsletter_subscribe', 'nvx_ajax_newsletter_subscribe' );