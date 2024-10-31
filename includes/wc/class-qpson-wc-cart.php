<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Cart
{

    public function __construct()
    {
        add_action('wp_loaded', array($this, 'update_cart_item'), 9999);
        add_action('woocommerce_before_calculate_totals', array($this, 'update_product_price_by_qty'));
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_qpson_meta_to_cart_item'), 10, 4);
        add_filter('woocommerce_cart_item_thumbnail', array($this, 'assign_builder_design_thumbnail'), 10, 3);
        add_filter('woocommerce_cart_item_price', array($this, 'get_cart_item_price_by_qty'), 10, 3);
        add_filter('woocommerce_quantity_input_args', array($this, 'bloomer_woocommerce_quantity_changes'), 9999, 2);
    }


    function bloomer_woocommerce_quantity_changes( $args, $product ) {
        $current_product_id = $product->id;
        
        if (is_cart()) {
            $cart = WC()->cart;
            // 获取购物车中的所有项目
            $cart_items = $cart->get_cart();
            
            $this->get_data_once($cart_items);
            foreach ( self::$cached_data as $data ) {
                if(strpos($args['input_name'],$data['key'])){
                    $args['min_value'] = $data['moq'];
                }
            }


        }
        return $args;
     
    }

    public static $cached_data  = null;


    function get_data_once($cart_items) {
        if(!empty($cart_items)){
            if (self::$cached_data === null) {
                $moq_list = array();
                foreach ( $cart_items as $cart_item_key => $cart_item ) {
                    $product_id = $cart_item['product_id'];
                    if (qpson_is_product($product_id)) {
                        if (qpson_product_type_is_sku($product_id)) {

                            $prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                            $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                            $sku_data = new stdClass();
                            $sku_data ->productId = $prodict_id;
                            if (!empty($versioned_attribute_id)) {
                                $sku_data->versionedAttributeId = $versioned_attribute_id;
                            }
                            $sku_data ->key = $cart_item_key;
                            $moq_list[] = $sku_data;
                        } 
                        if (qpson_product_type_is_configurable($product_id)) {
                            // $product_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                            // $qpmn_product_key_value = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_KEY_VALUE];
                            $product_id = $cart_item[Qpson_Meta::CART_ROOT]['qpson_sku_product_id'];
                            $qpson_attributeVersion = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                            $configurable_data = new stdClass();
                            $configurable_data ->productId = $product_id;
                            if (!empty($qpmn_product_key_value)) {
                                $configurable_data->attributeValues = $qpmn_product_key_value;
                            }
                            if (!empty($versioned_attribute_id)) {
                                $configurable_data->versionedAttributeId = $versioned_attribute_id;
                            }
                            $configurable_data ->key = $cart_item_key;
                            $moq_list[] = $configurable_data;
                        }
                    }
                }
                $moq_list = qpson_get_batch_moq($moq_list);
                self::$cached_data = $moq_list;
            }
        }
    }

    public function get_cart_item_price_by_qty($price, $cart_item, $cart_item_key)
    {
        if (isset($cart_item[Qpson_Meta::CART_ROOT])) {
            $product_id = $cart_item['product_id'];
            $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
            // is fix value continue
            if (qpson_product_is_fix_value($product_id)) {
                return $price;
            }
            if (qpson_product_is_table_value($product_id)) {
                if (!$product_type_is_configurable) {
                    $quantity = $cart_item['quantity'];
                    $my_price = qpson_get_product_table_value($quantity, $product_id);
                    return $my_price;
                } else {
                    $quantity = 1;
                    $my_price = qpson_get_product_table_value($quantity, $product_id);
                    return $my_price;
                }
            }
            if (qpson_product_is_attribute_price($product_id)) {
                if(isset($cart_item[Qpson_Meta::CART_ROOT]['price'])) {
                    return $cart_item[Qpson_Meta::CART_ROOT]['price'];
                }
                else {
                    $price = qpson_get_cart_product_price($product_id, $cart_item);
                    return $price;
                }
            }
        }
        return $price;
    }

    // Update the product price corresponding to each cart item according to the quantity of each cart item
    public function update_product_price_by_qty(WC_Cart $cart): void
    {
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item[Qpson_Meta::CART_ROOT])) {
                $product_id = $cart_item['product_id'];
                $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
                $product = wc_get_product( $product_id );
                // is fix value continue
                if (qpson_product_is_fix_value($product_id)) {
                    continue;
                }
                if (qpson_product_is_table_value($product_id)) {
                    if (!$product_type_is_configurable) {
                        $quantity = $cart_item['quantity'];
                        $result = qpson_get_product_table_value($quantity, $product_id);
                        $cart_item['data']->set_price($result);
                    }else{
                        $quantity = 1;
                        $result = qpson_get_product_table_value($quantity, $product_id);
                        $cart_item['data']->set_price($result);
                    }
                }

                if (qpson_product_is_attribute_price($product_id)) {
                    if(isset($cart_item[Qpson_Meta::CART_ROOT]['price'])) {
                        $cart_item['data']->set_price($cart_item[Qpson_Meta::CART_ROOT]['price']);
                    }
                    else {
                        $price = qpson_get_cart_product_price($product_id, $cart_item);
                        $cart_item['data']->set_price($price);
                    }
                }
            }
        }
    }


    // Create a new cart item and add product_instance_id and thumbnail to the cart item.
    public function add_qpson_meta_to_cart_item($cart_item_meta, $product_id, $variation_id, $quantity)
    {

        if (!qpson_is_product($product_id)) {
            return $cart_item_meta;
        }
        $cart_item_meta[Qpson_Meta::CART_ROOT] = array();
        $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_IS_QPSON_PRODUCT] = true;
        $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_ID] = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
        $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_STORE_PRODUCT_ID] = get_post_meta($product_id, Qpson_Meta::ORDER_STORE_PRODUCT_ID, true);
        

        $product_instance_id = null;
        $thumbnail = null;
        $qpson_sku_product_id = null;
        $qpson_property_model_id = null;
        $mock_id = null;
        $product_moq = null;
        $Product_key_value = null;

        if (isset($_REQUEST['productInstanceId']) && $_REQUEST['productInstanceId'] !== '') {
            //qpfb = qp fanny bag data
            $product_instance_id = intval(sanitize_text_field($_REQUEST['productInstanceId']));
            if (isset($_REQUEST['thumbnail'])) {
                $thumbnail = sanitize_text_field($_REQUEST['thumbnail']);
            }
        } else if (qpson_get_product_instance_id($product_id)) {
            $product_instance_id = qpson_get_product_instance_id($product_id);
            $thumbnail = get_post_meta($product_id, Qpson_Meta::PRODUCT_PREVIEW_IMAGE, true);
        }
        if (isset($_REQUEST['skuProductId']) && $_REQUEST['skuProductId'] !== '') {
            $qpson_sku_product_id = sanitize_text_field($_REQUEST['skuProductId']);
        }else{
            $qpson_sku_product_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_SKU_PRODUCT_ID, true);
        }
        if (isset($_REQUEST['propertyModelId']) && $_REQUEST['propertyModelId'] !== '') {
            $qpson_property_model_id = sanitize_text_field($_REQUEST['propertyModelId']);
        }
        if (isset($_REQUEST['mockId']) && $_REQUEST['mockId'] !== '') {
            $mock_id = sanitize_text_field($_REQUEST['mockId']);
        }
        if (isset($_REQUEST['productMoq']) && $_REQUEST['productMoq'] !== '') {
            $product_moq = sanitize_text_field($_REQUEST['productMoq']);
        }
        if (isset($_REQUEST['ProductKeyValue']) && $_REQUEST['ProductKeyValue'] !== '') {
            $Product_key_value = sanitize_text_field($_REQUEST['ProductKeyValue']);
            // $Product_key_value = json_decode('[' . str_replace([',', '[object Object]'], [',', '{}'], $abb) . ']', true);
            $Product_key_value = json_decode(stripslashes($Product_key_value),true);
        }

        $is_finished_product = qpson_product_finished_product($product_id);
        if ($product_instance_id || $is_finished_product) {
            if (!empty($thumbnail)) {
                $thumbnail_url = (isset($_REQUEST['mockId']) && !empty($_REQUEST['mockId'])) ? QPSON_FILE_SERVER : QPSON_COMPOSING_PREVIEW_URL;
                $thumbnail_url = $thumbnail_url . $thumbnail;
                $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_THUMBNAIL] = $thumbnail_url;
            }
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_INSTANCE_ID] = $product_instance_id;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_ALLOW_EDIT] = qpson_product_allow_design($product_id);
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_SKU_PRODUCT_ID] =  $qpson_sku_product_id;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PROPERTY_MODEL_ID] =  $qpson_property_model_id;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_IS_FINISHED_PRODUCT] =  $is_finished_product;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_MOCK_ID] =  $mock_id;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_MOQ] =  $product_moq;
            $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_KEY_VALUE] =  $Product_key_value;
            $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
            $product_is_attribute_price = qpson_product_is_attribute_price($product_id);
            $product_type_is_sku = qpson_product_type_is_sku($product_id);
            if ($product_type_is_configurable && $product_is_attribute_price) {
                $cart_item_meta[Qpson_Meta::CART_ROOT]['price'] = qpson_get_cart_product_price_two($product_id, $qpson_property_model_id);
            }
            if($is_finished_product || $product_type_is_sku){
                $qpson_attributeVersion = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                if(isset($qpson_attributeVersion)){
                    $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_ATTRIBUTEVERSION] =  $qpson_attributeVersion;
                }else{
                    $cart_item_meta[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_ATTRIBUTEVERSION] =  null;
                }
            }
        }
        return $cart_item_meta;
    }

    // Add Edit and Preview buttons below the preview image.
    public function assign_builder_design_thumbnail($thumbnail, $cart_item, $cart_item_key)
    {
        $qpmnData = $cart_item[Qpson_Meta::CART_ROOT] ?? null;
        if ($qpmnData) {
            $myThumbnail = $qpmnData[Qpson_Meta::CART_THUMBNAIL] ?? null;
            if (!empty($myThumbnail)) {
                $html = new DOMDocument();
                $html->loadHTML('<?xml encoding="utf-8" ?>' . $thumbnail);
                $node = $html->childNodes[2]->childNodes[0]->childNodes[0];
                foreach ($node->attributes as $attr) {
                    if ($attr->name == 'class') {
                        $mock_id = $qpmnData[Qpson_Meta::CART_MOCK_ID] ?? null;
                        if (!empty($mock_id)) {
                            $mock_info = qpson_get_mock_info($mock_id);
                            $mock_status = $mock_info['status'];
                            if($mock_status === 'SUCCESS'){
                                $myThumbnail = QPSON_FILE_SERVER . $mock_info['fileName'];
                                $srcSet = $myThumbnail . ' 480w, ' . $myThumbnail . ' 300w, ' . $myThumbnail . ' 1024w, ' . $myThumbnail . ' 150w, ' . $myThumbnail . ' 768w';
                                $node->setAttribute('src', $myThumbnail);
                                $node->setAttribute('srcset', $srcSet);
                            }else{
                                $node->setAttribute('class', $node->getAttribute('class') . ' qpson-preview-img-mock');
                                $parentElement = $node->parentNode;
                                // $parentElement->setAttribute('class', $parentElement->getAttribute('class') . ' qpmn_load');
                                $node->setAttribute('data-mockId', $node->getAttribute('data-mockId') . $mock_id);
                                $node->setAttribute('data-src', $myThumbnail);
                            }

                        }else{
                            $node->setAttribute('class', $node->getAttribute('class') . ' qpson-preview-img');
                            $node->setAttribute('data-src', $myThumbnail);

                        }
                    }
                }
                $thumbnail = $html->saveHTML($node);
            }
        }

        if ($cart_item && isset($cart_item[Qpson_Meta::CART_ROOT]) && !empty($cart_item[Qpson_Meta::CART_ROOT]) && isset($cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_INSTANCE_ID])) {
            $product_instance_id = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_INSTANCE_ID];
            $store_product_id = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_STORE_PRODUCT_ID];
            $allow_edit = !isset($cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_ALLOW_EDIT]) || $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_ALLOW_EDIT];
            $qpson_token = Qpson::get_apikey();
            $locale = get_locale();
            $wc_client_id = get_current_user_id();
            if ($wc_client_id > 0) {
                $wc_user = wp_get_current_user();
                $wc_user_name_string = $wc_user->data && $wc_user->data->user_login ?  '&wcUserName=' . $wc_user->data->user_login : "";
            } else {
                $wc_user_name_string = "";
            }
            $edit_link = wp_nonce_url(QPSON_BUILDER_URL . "?storeProductId=$store_product_id&productInstanceId=$product_instance_id&cartItemKey=$cart_item_key&accessToken=$qpson_token&tokenType=Basic&wcClientId=$wc_client_id&themes=qpmn&productType=sku&language=$locale&redirectUrl=" . get_permalink() . $wc_user_name_string, 'builder-cart-nonces');
            $preview_link = QPSON_BUILDER_URL . "?storeProductId=$store_product_id&productInstanceId=$product_instance_id&accessToken=$qpson_token&tokenType=Basic&isPreview=true&themes=qpmn";
            $editText = __('Edit', 'qpmn-pod-by-qp-group');
            $previewText = __('Preview', 'qpmn-pod-by-qp-group');
            $cart_preview_title = "Preview";
            $preview_html = "<a href='javascript:;'><button id='qpson-preview-buttton' type='button' class='qpson-cart-preview-button'  value='$preview_link' onclick=\"openQpsonModal(event,'$cart_preview_title')\"  style='outline:none;background-color:initial;color:#337ab7;border:none;box-shadow: none;'>$previewText</button></a>";
            if(is_cart()){
                $edit_html = "<a href=\"$edit_link\" style='text-decoration: none;color:#fa7268;'>$editText</a>";
            }else{
                $edit_html = "";
            }
            $thumbnail = $thumbnail . $preview_html;
            if ($allow_edit) {
                $thumbnail = $thumbnail . $edit_html;
            }
            $thumbnail = '<div class="qpson-thumbnail-container" style="min-height: 100px;position: relative;min-width: 100px;display:flex;flex-direction:column;align-items:center">' . $thumbnail . '</div>';
            return $thumbnail;
        }

        return $thumbnail;
    }

    // Call the WC API to get and update the cart_item by cart_item_key. 
    public function update_cart_item(): void
    {
        $cart_item_key = null;
        $product_instance_id = null;
        $thumbnail = null;
        $wpnonce = null;
        $qpson_sku_product_id = null;
        $qpson_property_model_id = null;
        $mock_id = null;


        if (isset($_REQUEST['wpnonce'])) {
            $wpnonce = sanitize_text_field($_REQUEST['wpnonce']);
        }

        if (isset($_REQUEST['cartItemKey'])) {
            $cart_item_key = sanitize_text_field($_REQUEST['cartItemKey']);
        }
        if (isset($_REQUEST['productInstanceId'])) {
            $product_instance_id = sanitize_text_field($_REQUEST['productInstanceId']);
        }

        if (isset($_REQUEST['thumbnail'])) {
            $thumbnail = sanitize_text_field($_REQUEST['thumbnail']);
        }

        if (isset($_REQUEST['skuProductId'])) {
            $qpson_sku_product_id = sanitize_text_field($_REQUEST['skuProductId']);
        }

        if (isset($_REQUEST['propertyModelId'])) {
            $qpson_property_model_id = sanitize_text_field($_REQUEST['propertyModelId']);
        }

        if (isset($_REQUEST['mockId'])) {
            $mock_id = sanitize_text_field($_REQUEST['mockId']);
        }

        if ($cart_item_key && $product_instance_id && $wpnonce && wp_verify_nonce($wpnonce, 'builder-cart-nonces')) {
            $cart_item = WC()->cart->get_cart_item($cart_item_key);
            if (!$cart_item) {
                return;
            }
            $qpmnData = $cart_item[Qpson_Meta::CART_ROOT] ?? null;
            if ($qpmnData) {
                $qpmnData[Qpson_Meta::CART_PRODUCT_INSTANCE_ID] = $product_instance_id;
                $thumbnail_url = (isset($_REQUEST['mockId'])) ? QPSON_FILE_SERVER : QPSON_COMPOSING_PREVIEW_URL;
                $qpmnData[Qpson_Meta::CART_THUMBNAIL] = $thumbnail_url . $thumbnail;
                $qpmnData[Qpson_Meta::CART_SKU_PRODUCT_ID] =  $qpson_sku_product_id;
                $qpmnData[Qpson_Meta::CART_MOCK_ID] =  $mock_id;

                if($qpson_property_model_id){
                    $qpmnData[Qpson_Meta::CART_PROPERTY_MODEL_ID] =  $qpson_property_model_id;
                }
                //update cart item
                $cart_item[Qpson_Meta::CART_ROOT] = $qpmnData;
                //update shopping cart
                $cartContent = WC()->cart->cart_contents;
                $cartContent[$cart_item_key] = $cart_item;
                WC()->cart->set_cart_contents($cartContent);
                //save updated cart item data. like db commit
                WC()->cart->set_session();
                wc_add_notice('Cart Updated');
            }
        }
    }
}

return new Qpson_Wc_Cart();
