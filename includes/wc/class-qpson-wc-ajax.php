<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Ajax {

    public static function init() {
        self::add_ajax_events();
    }


    public static function add_ajax_events() {
        $ajax_events_nopriv = array(
            'get_product_metas'
        );
        foreach ($ajax_events_nopriv as $ajax_event) {
            // WC AJAX can be used for frontend ajax requests.
            add_action('wc_ajax_' . $ajax_event, array(__CLASS__, $ajax_event));
        }
    }

    public static function get_product_metas() {
        ob_start();

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if (empty($_GET['variation_id'])) {
            wp_die();
        }
        $product_id = absint($_GET['variation_id']);
        if (!$product_id) {
            wp_die();
        }
        if (empty($_GET['metas'])) {
            wp_die();
        }
        $meta_keys = explode(",", $_GET['metas']);
        $data = array();
        foreach ($meta_keys as $meta_key) {
            $meta = get_post_meta($product_id, $meta_key,true);
            $data[$meta_key] = $meta;
        }

        wp_send_json($data);
    }
}

Qpson_Wc_Ajax::init();