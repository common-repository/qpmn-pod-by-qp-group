<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc {

    public function __construct() {
        add_action('init', array($this, 'includes'));
    }

    public function includes() {
        include_once __DIR__ . '/class-qpson-wc-category.php';
        include_once __DIR__ . '/class-qpson-wc-product.php';
        include_once __DIR__ . '/class-qpson-wc-cart.php';
        include_once __DIR__ . '/class-qpson-wc-order.php';
        include_once __DIR__ . '/class-qpson-wc-checkout.php';
        include_once __DIR__ . '/class-qpson-frontend-scripts.php';
        include_once __DIR__ . '/class-qpson-wc-ajax.php';
    }
}

return new Qpson_Wc();