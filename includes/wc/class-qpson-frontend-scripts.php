<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class Qpson_Frontend_Scripts {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
    }

    public function load_scripts() {

        $this->register_scripts();
        if (is_cart() || is_checkout() || is_order_received_page()) {
            // wp_enqueue_script('qpson_admin_preview_image');
        }
        if(!is_admin()) {
//        if (is_cart() || is_product() || is_checkout() || is_view_order_page()) {
            // wp_enqueue_script('qpson_bootstrap');
            wp_enqueue_script('qpson_builder_preview');
            wp_enqueue_script('qpson_admin_preview_image');
            wp_enqueue_style('qpson_admin_preview_image');
            // wp_enqueue_style('qpson_bootstrap');
        }

        if (is_product()) {
            wp_enqueue_script('qpmn_product_attribute');
            wp_enqueue_style('qpmn_product_attribute');
            wp_enqueue_script('qpmn_ajax_add_to_cart');;
        }

        wp_enqueue_style('qpson_styles');
    }

    public function register_scripts() {
        // $suffix = constant('SCRIPT_DEBUG') ? '' : '.min';
        $version = constant('QPSON_VERSION');
        wp_register_script('qpson_admin_preview_image', QPSON()->plugin_url() . '/assets/js/preview-image.js', array('jquery'), $version);
        wp_register_script('qpson_builder_preview', QPSON()->plugin_url() . '/assets/js/builder-preview.js', array('jquery'), $version);
        wp_register_script('qpmn_product_attribute', QPSON()->plugin_url() . '/assets/js/qpmn-product-attribute.js', array('jquery'), $version);
        wp_register_script('qpmn_ajax_add_to_cart', QPSON()->plugin_url() . '/assets/js/qpmn-ajax-add-to-cart.js', array('jquery'), $version);
        wp_register_style('qpmn_product_attribute', QPSON()->plugin_url() . '/assets/css/qpmn-product-attribute.css', array(), $version);
        wp_register_style('qpson_admin_preview_image', QPSON()->plugin_url() . '/assets/css/preview-image.css', array(), $version);

        wp_localize_script('qpson_admin_preview_image', 'myData', array(
            'server' => QPSON_SERVER,
            'token' => Qpson::get_apikey(),
            'file' => QPSON_FILE_SERVER
        ));

        $product_id = get_post()->ID;
        if(get_post()){

            wp_localize_script('qpmn_product_attribute', 'qpmnProductData', array(
                'server' => QPSON_SERVER,
                'accessToken' => Qpson::get_apikey(),
                'file' => QPSON_FILE_SERVER,
                'tokenType' => 'Basic',
                'qpmnProductId' => $product_id,
                'qpmnProductType' => get_post_meta($product_id, Qpson_Meta::QPSON_STORE_PRODUCT_TYPE, true),
                'isQpmnFinishedProduct' => get_post_meta($product_id, Qpson_Meta::IS_QPSON_FINISHED_PRODUCT, true),
                'attributePrice' => get_post_meta($product_id, Qpson_Meta::PRODUCT_PRICE_STRATEGY, true),
                'storeProductId' => get_post_meta($product_id, Qpson_Meta::STORE_PRODUCT_ID, true),
                'qpson_publishProfileIds' =>  get_post_meta($product_id, Qpson_Meta::QPSON_PUBLISHPROFILEIDS, true),
                'qpson_publishProfileCodes' =>  get_post_meta($product_id, Qpson_Meta::QPSON_PUBLISHPROFILECODES, true),
                'qpson_attributeVersion' => get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true),
                'currencyCode' => get_woocommerce_currency(),
                'currencySymbol' => get_woocommerce_currency_symbol(),
                'qpsonProductId' => get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true),
    
    
    
            ));

            if(is_cart()){
                wp_localize_script('qpson_admin_preview_image', 'myData', array(
                    'server' => QPSON_SERVER,
                    'token' => Qpson::get_apikey(),
                    'file' => QPSON_FILE_SERVER,
                    'type' => 'Cart'
                ));
            }
        }
        
    }

}


return new Qpson_Frontend_Scripts();