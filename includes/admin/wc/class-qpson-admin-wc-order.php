<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Admin_Wc_Order {

    public function __construct() {
        add_filter('woocommerce_admin_order_item_thumbnail', array($this, 'modify_thumbnail'), 10, 3);
        add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'hide_meta_key'), 10, 2);
        add_action('woocommerce_order_action_push_order_to_qpson',array($this,'push_order_to_qpson'),7777,1);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_script'));
        // add_action('woocommerce_admin_order_data_after_order_details', array($this,'brain_display_order_data_in_admin'), 10, 1 );
    }

    public function brain_display_order_data_in_admin( $order ){
        $names = array();
        foreach ($order->get_shipping_methods() as $shipping_method) {
                $method_name = $shipping_method->get_name();

                if ($shipping_method->get_method_id() == 'qpmn_shipping') {
                    $tracking_info_str = $shipping_method->get_meta(Qpson_Meta::SHIPPING_TRACKING);
                    if ($tracking_info_str) {
                        try {
                            $tracking_info = json_decode($tracking_info_str, true);
                            $tracking_no_arr = [];
                            foreach ($tracking_info as $tracking) {
                                $tracking_no_arr[] = $tracking[Qpson_Meta::SHIPPING_TRACKING_NO];
                            }
                            $data = array(
                                'order_name' => 'trackingNo: ' . implode(', ', $tracking_no_arr),
                                'order_number' => implode(', ', $tracking_no_arr)
                            );
                        } catch (Exception $e) {

                        }
                    }
                }
                $names[] = $data; 
            } 
        ?>
            <div class="order_data_column">
                <?php
                    echo '<p><strong>' . __('Track list') . ':</strong></p>';
                    echo '<ul>'; 
                    foreach ($names as $name) {
                        $track_link = sanitize_text_field(QPSON_TRACK_URL . $name['order_number']);
                        echo '<li><a href="' . $track_link . '">' . $name['order_name'] . '</a></li>';
                    }
                    echo '</ul>';
                ?>
            </div>
        <?php 
    }
    

    public function enqueue_custom_script(){
        global $post_type, $pagenow;
        // 判断是否在订单详情页面
        if ($post_type === 'shop_order' && $pagenow === 'post.php') {
            $version = constant('QPSON_VERSION');
            wp_register_script('qpson_admin_preview_image', QPSON()->plugin_url() . '/assets/js/preview-image.js', array('jquery'), $version);
            wp_register_style('qpson_admin_preview_image', QPSON()->plugin_url() . '/assets/css/preview-image.css', array(), $version);
            wp_localize_script('qpson_admin_preview_image', 'myData', array(
                'server' => QPSON_SERVER,
                'token' => Qpson::get_apikey(),
                'file' => QPSON_FILE_SERVER
            ));
            // 引入脚本
            wp_enqueue_script('qpson_admin_preview_image');
            wp_enqueue_style('qpson_admin_preview_image');
        }

    }

    function push_order_to_qpson($order) {
        create_qpson_order(null,$order);
    }


    public function hide_meta_key($formatted_meta, WC_Order_Item $wc_order_item) {

        $class = get_class($wc_order_item);
        if ($class == 'WC_Order_Item_Shipping' && $wc_order_item->meta_exists('qpsonShippingMethod')) {

            $result = [];
            foreach ($formatted_meta as $key => $value) {
                if ($value->key == __('Items', 'woocommerce')) {
                    $result[$key] = $value;
                }
            }
            return $result;
        }

        if ($class == 'WC_Order_Item_Product' && qpson_is_order_item($wc_order_item)) {
            return [];
        }

        return $formatted_meta;
    }


    public function modify_thumbnail($thumbnail, $item_id, WC_Order_Item $item) {

        $qpson_thumbnail_url = $item->get_meta(Qpson_Meta::ORDER_THUMBNAIL);
        $order_id = $item->get_order()->get_id();
        $product_instance_id = $item->get_meta(Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID);
        $qpson_store_product_id = $item->get_meta(Qpson_Meta::STORE_PRODUCT_ID);
        $qpson_is_finished_product = $item->get_meta(Qpson_Meta::ORDER_IS_FINISHED_PRODUCT);
        if (empty($qpson_thumbnail_url) || $qpson_is_finished_product) return $thumbnail;

        $html = new DOMDocument();
        $html->loadHTML('<?xml encoding="utf-8" ?>' .$thumbnail);
        $node = $html->childNodes[2]->childNodes[0]->childNodes[0];
        foreach ($node->attributes as $attr) {
            if ($attr->name == 'class') {
                $mock_id = $item->get_meta(Qpson_Meta::ORDER_MOCK_ID) ?? null;
                if (!empty($mock_id)) {
                    $node->setAttribute('class', $node->getAttribute('class') . ' qpson-preview-img-mock');
                    $node->setAttribute('data-mockId', $node->getAttribute('data-mockId') . $mock_id);
                }else{
                    $node->setAttribute('class', $node->getAttribute('class') . ' qpson-preview-img');
                }
            }
        }
        $node->setAttribute('data-src', $qpson_thumbnail_url);
        $thumbnail = $html->saveHTML($node);

        $qpson_token = Qpson::get_apikey();
        $locale = get_locale();
        $preview_link = sanitize_text_field(QPSON_BUILDER_URL . "?storeProductId=$qpson_store_product_id&productInstanceId=$product_instance_id&isPreview=true&themes=qpmn&accessToken=$qpson_token&tokenType=Basic&language=$locale");
        $edit_link = sanitize_text_field(QPSON_BUILDER_URL . "?storeProductId=$qpson_store_product_id&wcOrderId=$order_id&productInstanceId=$product_instance_id&wcOrderItemId=$item_id&themes=qpmn&accessToken=$qpson_token&tokenType=Basic&language=$locale&redirectUrl=" . get_permalink());
        $order_is_editable = $item->get_order()->is_editable();
        $editText = __('Edit', 'qpmn-pod-by-qp-group');
        $previewText = __('Preview', 'qpmn-pod-by-qp-group');
        $preview_button = "<a href=\"$preview_link\" target='_blank' style='font-size: 14px;float: left;margin-top: 6px;'>$previewText</a>";
        $edit_button = "<a href=\"$edit_link\" target='_blank' style='font-size: 14px;float: left;margin-top: 6px;'>$editText</a>";

        if($order_is_editable){
            return '<div class="order-product-name">' . $thumbnail . '</div>' . $preview_button . $edit_button;
        }else{
            return '<div class="order-product-name">' . $thumbnail . '</div>' . $preview_button;
        }
    }
}

return new Qpson_Admin_Wc_Order();
