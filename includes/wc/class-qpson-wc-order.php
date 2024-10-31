<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Order {
    public function __construct() {
        //        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'order_itemmeta_hidden_meta'));
        //        add_filter('woocommerce_order_item_display_meta_value', array($this, 'change_order_item_meta_value'), 20, 3);
        //        add_filter('woocommerce_order_item_display_meta_key', array($this, 'change_order_item_meta_title'), 20, 3);
        add_action('woocommerce_new_order_item', array($this, 'adding_custom_data_to_order_item_meta'), 10, 3);
        add_filter('woocommerce_order_item_get_formatted_meta_data', array($this, 'filter_order_metadata'), 10, 2);
        add_filter('woocommerce_order_item_name', array($this, 'add_preview_image_to_order_item_name'), 10, 3);
        add_filter('woocommerce_order_shipping_to_display_shipped_via', array($this, 'custom_shipping_via_display'), 10, 2);
    }

    public function custom_shipping_via_display($via, WC_Abstract_Order $order) {
        if (is_view_order_page()) {
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
                            $method_name = $method_name . '(trackingNo:' . implode(', ', $tracking_no_arr) . ')';
                            // 新功能：新增快递号的展示和跳转链接
                            // $data = array(
                            //     'order_name' => $method_name . '(trackingNo: ' . implode(', ', $tracking_no_arr) . ')',
                            //     'order_number' => implode(', ', $tracking_no_arr)
                            // );
                        } catch (Exception $e) {

                        }
                    }
                }
                $names[] = $method_name;
                return '&nbsp;<small class="shipped_via">' . sprintf(__('via %s', 'woocommerce'), implode(',', $names)) . '</small>';
                // 新功能：新增快递号的展示和跳转链接
                // $names[] = $data;
                // $html = "<div class='order_data_column'>"; 
                // foreach ($names as $name) {
                //     $track_link = sanitize_text_field(QPSON_TRACK_URL . $name['order_number']);
                //     $html .= '<div style="font-size:14px"><a href="' . $track_link . '">' . $name['order_name'] . '</a></li>';
                // }
                // $html .= "</div>";
                // return $html;
            }
        }
        return $via;
    }

    // Add the product thumbnails and preview buttons of the settlement page to the order page.
    public function add_preview_image_to_order_item_name($product_name, WC_Order_Item $order_item, $is_visible) {
        $allow_html = wp_kses_allowed_html('post');
        $qpson_is_finished_product = $order_item->get_meta(Qpson_Meta::ORDER_IS_FINISHED_PRODUCT);

        if (!qpson_is_order_item($order_item)) {
            return $product_name;
        }
        $html = new DOMDocument();
        $libxml_previous_state = libxml_use_internal_errors(true);
        $html->loadHTML('<?xml encoding="utf-8" ?>' .$product_name);
        libxml_clear_errors();
        libxml_use_internal_errors($libxml_previous_state);
        $node = $html->childNodes[2]->childNodes[0]->childNodes[0];
        $node->setAttribute('style', 'display: inline-block;line-height: 40px;');
        $product_name = $html->saveHTML($node);
        $product = $order_item->get_product();
        $image_id = $product->get_image_id();
        $init_img_url = wp_get_attachment_image_url( $image_id );
        if($qpson_is_finished_product){
            $img = '<img style="margin-left: 12px;" src="' . $init_img_url . '" class="" alt="" width="50" height="50">';
            $thumbnail = "<div style=\"width:100px;height:88px;float:left;\">" . $img . "</div>";
            return $thumbnail . $product_name;
        }else{
            $thumbnail_url = $order_item->get_meta(Qpson_Meta::ORDER_THUMBNAIL);
            $product_instance_id = $order_item->get_meta(Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID);
            $qpson_store_product_id = $order_item->get_meta(Qpson_Meta::ORDER_STORE_PRODUCT_ID);
            $qpson_token = Qpson::get_apikey();
            $locale = get_locale();
            $previewText = __('Preview', 'qpmn-pod-by-qp-group');
            $mock_id = $order_item->get_meta(Qpson_Meta::ORDER_MOCK_ID);
            $preview_link = sanitize_text_field(QPSON_BUILDER_URL . "?storeProductId=$qpson_store_product_id&productInstanceId=$product_instance_id&isPreview=true&themes=qpmn&accessToken=$qpson_token&tokenType=Basic&language=$locale");
            if(!empty($mock_id)){
                $img = '<img style="margin-left: 12px;" src="' . $init_img_url . '" class="qpson-preview-img-mock attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" data-mockid="'. $mock_id .'" data-src="' .$thumbnail_url. '" srcset="' . $thumbnail_url . ' 324w,' . $thumbnail_url . ' 510w" sizes="(max-width: 50px) 100vw, 50px" width="50" height="50">';
            }else{
                $img = '<img style="margin-left: 12px;" src="' . $init_img_url . '" class="qpson-preview-img attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" data-src="' .$thumbnail_url. '" srcset="' . $thumbnail_url . ' 324w,' . $thumbnail_url . ' 510w" sizes="(max-width: 50px) 100vw, 50px" width="50" height="50">';
            }
            $order_preview_title = "Preview";
            $thumbnail = "<div class='order-product-name' style=\"width:100px;height:88px;float:left;\"><div>" . $img . "</div><button type='button' value=\"$preview_link\" class=\"order-preview\" style='width:80%;padding:4px;margin:4px'>$previewText</button></div>";
            return $thumbnail . $product_name;
        }
    }

    // Filter out orders that are not woocommerce order items and qpson orders.
    public function filter_order_metadata($formatted_meta, $order_item) {
        if (!is_admin() && get_class($order_item) == 'WC_Order_Item_Product' && qpson_is_order_item($order_item)) {
            return [];
        }
        return $formatted_meta;
    }

    public function order_itemmeta_hidden_meta($meta_keys) {
        $meta_keys[] = Qpson_Meta::IS_QPSON_ORDER;
        $meta_keys[] = Qpson_Meta::IS_QPSON_PRODUCT;
        $meta_keys[] = Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID;
        $meta_keys[] = Qpson_Meta::ORDER_THUMBNAIL;
        return $meta_keys;
    }

    public function change_order_item_meta_value($value, $meta, $item) {

        if (Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID === $meta->key) {
            if (!empty($value)) {
                $url = $item->get_meta(Qpson_Meta::ORDER_THUMBNAIL);
                $btnText = 'Preview';

                $eleId = "qpmn-order-item-design-container-{$value}";
                $value = "
                    <div class='qpmn-bootstrap'>
                        <div id='{$eleId}' class='qpmn-order-item-design-container' ></div>
                    </div>
                ";
            }
        }

        return $value;
    }

    public function change_order_item_meta_title($key, $meta, $item) {
        if (Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID === $meta->key) {
            if (!empty($key)) {
                $key = 'Design';
            }
        }

        return $key;
    }

    // Update the thumbnail of the customized content on the order item.
    public function adding_custom_data_to_order_item_meta($item_id, $item, $order_id) {
        $qp_data = isset($item->legacy_values[Qpson_Meta::CART_ROOT]) ? $item->legacy_values[Qpson_Meta::CART_ROOT] : null;
        if (!is_null($qp_data)) {
            if (isset($qp_data[Qpson_Meta::CART_IS_QPSON_PRODUCT])) {
                //mark to order level to indicate this is QPMN order also
                self::set_order_meta($order_id, Qpson_Meta::IS_QPSON_ORDER, true);
                wc_add_order_item_meta($item_id, Qpson_Meta::IS_QPSON_PRODUCT, $qp_data[Qpson_Meta::CART_IS_QPSON_PRODUCT]);
            }

            if (isset($qp_data[Qpson_Meta::CART_PRODUCT_INSTANCE_ID])) {
                $product_instance_id = $qp_data[Qpson_Meta::CART_PRODUCT_INSTANCE_ID];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_PRODUCT_INSTANCE_ID, $product_instance_id);
            }
            if (isset($qp_data[Qpson_Meta::CART_THUMBNAIL])) {
                $product_thubmail = $qp_data[Qpson_Meta::CART_THUMBNAIL];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_THUMBNAIL, $product_thubmail);
            }
            if (isset($qp_data[Qpson_Meta::CART_STORE_PRODUCT_ID])) {
                $qpson_store_product_id = $qp_data[Qpson_Meta::CART_STORE_PRODUCT_ID];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_STORE_PRODUCT_ID, $qpson_store_product_id);
            }
            if (isset($qp_data[Qpson_Meta::CART_IS_FINISHED_PRODUCT])) {
                $qpson_is_finished_product = $qp_data[Qpson_Meta::CART_IS_FINISHED_PRODUCT];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_IS_FINISHED_PRODUCT, $qpson_is_finished_product);
            }
            if(isset($qp_data[Qpson_Meta::CART_SKU_PRODUCT_ID])){
                $qpson_sku_product_id = $qp_data[Qpson_Meta::CART_SKU_PRODUCT_ID];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_SKU_PRODUCT_ID, $qpson_sku_product_id);
            }
            if(isset($qp_data[Qpson_Meta::CART_ATTRIBUTEVERSION]) && $qp_data[Qpson_Meta::CART_ATTRIBUTEVERSION] !== ''){
                $qpson_attributeVersion = $qp_data[Qpson_Meta::CART_ATTRIBUTEVERSION];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_ATTRIBUTEVERSION, $qpson_attributeVersion);
            }
            if(isset($qp_data[Qpson_Meta::CART_PROPERTY_MODEL_ID]) && $qp_data[Qpson_Meta::CART_PROPERTY_MODEL_ID] !== ''){
                $qpson_property_model_id = $qp_data[Qpson_Meta::CART_PROPERTY_MODEL_ID];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_PROPERTY_MODEL_ID, $qpson_property_model_id);
            }
            if(isset($qp_data[Qpson_Meta::CART_MOCK_ID]) && $qp_data[Qpson_Meta::CART_MOCK_ID] !== ''){
                $mock_id = $qp_data[Qpson_Meta::CART_MOCK_ID];
                wc_add_order_item_meta($item_id, Qpson_Meta::ORDER_MOCK_ID, $mock_id);
            }
        }
    }

    public static function set_order_meta($orderId, $key, $value) {
        update_post_meta($orderId, $key, $value);
    }
}

return new Qpson_Wc_Order();
