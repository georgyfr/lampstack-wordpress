<?php
defined('ABSPATH') || exit;

// Newsletter subscription (local only, no external service)
add_action('wp_ajax_nvx_newsletter_subscribe', 'nvx_newsletter_subscribe');
add_action('wp_ajax_nopriv_nvx_newsletter_subscribe', 'nvx_newsletter_subscribe');

function nvx_newsletter_subscribe() {
    check_ajax_referer('nvx_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Adresse e-mail invalide.'));
    }

    // Store in a custom option (simple implementation, real site would use Mailchimp/Brevo)
    $subscribers = get_option('nvx_newsletter_subscribers', array());
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        update_option('nvx_newsletter_subscribers', $subscribers);
    }

    wp_send_json_success(array(
        'message' => 'Inscription réussie ! Vérifiez votre email.',
    ));
}

// Dynamic cart count update
add_action('wp_ajax_nvx_cart_count', 'nvx_get_cart_count');
add_action('wp_ajax_nopriv_nvx_cart_count', 'nvx_get_cart_count');

function nvx_get_cart_count() {
    $count = 0;
    if (function_exists('WC') && WC()->cart) {
        $count = WC()->cart->get_cart_contents_count();
    }
    wp_send_json_success(array('cart_count' => $count));
}