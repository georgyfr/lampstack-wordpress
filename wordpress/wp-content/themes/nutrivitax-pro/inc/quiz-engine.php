<?php
defined('ABSPATH') || exit;

// AJAX handler for quiz submission
add_action('wp_ajax_nvx_quiz_submit', 'nvx_ajax_quiz_submit');
add_action('wp_ajax_nopriv_nvx_quiz_submit', 'nvx_ajax_quiz_submit');

function nvx_ajax_quiz_submit() {
    check_ajax_referer('nvx_nonce', 'nonce');

    $answers = isset($_POST['answers']) ? json_decode(stripslashes($_POST['answers']), true) : array();
    if (empty($answers)) {
        wp_send_json_error(array('message' => 'Veuillez répondre à toutes les questions.'));
    }

    // Simple recommendation engine based on quiz answers
    $goal = isset($answers['q1']) ? $answers['q1'] : '';
    $recommendations = array(
        'energy'    => array('whey-proteine-isolat', 'creatine-monohydrate', 'vitamine-d3-5000-ui'),
        'immunity'  => array('vitamine-d3-5000-ui', 'omega-3-ultra-pur', 'vitamine-c-1000'),
        'cognition' => array('omega-3-ultra-pur', 'bacopa-monnieri', 'rhodiola-rosea'),
        'sport'     => array('whey-proteine-isolat', 'creatine-monohydrate', 'bcaa-4-1-1', 'pre-workout-extreme'),
        'weight'    => array('cla-1000', 'garcinia-cambogia', 'the-vert-extrait', 'chromium-picolinate'),
    );

    $stack = isset($recommendations[$goal]) ? $recommendations[$goal] : $recommendations['energy'];

    wp_send_json_success(array(
        'stack' => $stack,
        'goal'  => $goal,
    ));
}

// AJAX handler for stack builder
add_action('wp_ajax_nvx_stack_add', 'nvx_ajax_stack_add');
add_action('wp_ajax_nopriv_nvx_stack_add', 'nvx_ajax_stack_add');

function nvx_ajax_stack_add() {
    check_ajax_referer('nvx_nonce', 'nonce');
    $product_id = intval($_POST['product_id'] ?? 0);
    if ($product_id <= 0) wp_send_json_error('Produit invalide.');

    $added = WC()->cart->add_to_cart($product_id);
    if ($added) {
        wp_send_json_success(array(
            'message'   => 'Produit ajouté à votre stack.',
            'cart_count'=> WC()->cart->get_cart_contents_count(),
        ));
    }
    wp_send_json_error('Impossible d\'ajouter ce produit.');
}