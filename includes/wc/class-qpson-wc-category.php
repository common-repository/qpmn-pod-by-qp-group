<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Category {

    public function __construct() {
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'modify_button_text'), 10, 2);
        add_filter('woocommerce_product_add_to_cart_url', array($this, 'modify_button_url'), 10, 2);
//        add_filter('woocommerce_loop_add_to_cart_args', array($this, 'remove_button_add_to_cart'), 9999, 2);
    }
    // Products that cannot be added to the cart directly will replace the button display text.
    function modify_button_text($text, $product) {
        $product_id = $product->get_id();
        if (qpson_is_product($product_id) && !qpson_produt_can_add_to_cart_direct($product_id)) {
            return __('Select options', 'qpmn-pod-by-qp-group');
        } else
            return $text;
    }

    // Products that cannot be added to the cart directly will replace the button redirect link.
    function modify_button_url($url, $product) {
        $product_id = $product->get_id();
        if (qpson_is_product($product_id) && !qpson_produt_can_add_to_cart_direct($product_id)) {
            return $product->get_permalink();
        }
        return $url;
    }

    // Remove the Add to Cart button.
    function remove_button_add_to_cart($args, $product) {
        if (qpson_is_product($product->get_id())) {
            $class_result = str_replace('ajax_add_to_cart', '', $args['class']);
            $args['class'] = $class_result;
        }
        return $args;
    }
}

return new Qpson_Wc_Category();