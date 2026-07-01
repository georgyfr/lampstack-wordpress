<?php
defined('ABSPATH') || exit;
// Guard: only run if WooCommerce is active
if (!class_exists('WooCommerce')) return;

// 1. Remove default WooCommerce wrappers (FSE theme handles layout)
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// 2. Change products per row
add_filter('loop_shop_columns', function() { return 3; });
add_filter('loop_shop_per_page', function() { return 12; });

// 3. Customize product badge "En stock" / "Rupture"
add_filter('woocommerce_get_stock_html', function($html, $product) {
    if ($product->is_in_stock()) {
        return '<span class="nvx-stock-badge nvx-stock-badge--in">En stock</span>';
    }
    return '<span class="nvx-stock-badge nvx-stock-badge--out">Rupture</span>';
}, 10, 2);

// 4. Add custom body class for product pages
add_filter('body_class', function($classes) {
    if (is_product()) $classes[] = 'nvx-single-product';
    if (is_shop() || is_product_category()) $classes[] = 'nvx-shop';
    return $classes;
});

// 5. Disable default WooCommerce styles (theme handles them)
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// 6. Add "Compléments alimentaires" as default product category on activation
// (no-op if already exists)