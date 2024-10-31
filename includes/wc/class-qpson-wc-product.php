<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Product
{

    public function __construct()
    {
        add_action('woocommerce_before_add_to_cart_button', array($this, 'add_product_meta_form'));
        add_action('woocommerce_after_add_to_cart_button', array($this, 'add_edit_button'));
        add_action('woocommerce_simple_add_to_cart', array($this, 'add_design_button'));
        add_action('woocommerce_is_purchasable', array($this, 'disable_add_to_cart'), 10, 2);
        add_action('woocommerce_get_price_html', array($this, 'modify_product_price_html'), 10, 2);
        add_filter('woocommerce_product_supports', array($this, 'modify_product_supports'), 9999, 3);
        // add update design notice
        add_action('wp_loaded', array($this, 'add_update_product_instance_notice'), 9999);
        // add_shortcode('qpmn_product_attribute', 'qpmn_product_attribute_shortcode');
        add_action('woocommerce_simple_add_to_cart', array($this, 'qpmn_product_attribute_shortcode'), 6);
        add_filter('wc_product_sku_enabled', array($this, 'qpmn_remove_configurable_product_skus'), 10, 3);
        // add_filter('woocommerce_currency_symbol', array($this,'qpmn_update_currency_symbol'), 10, 2 );

        add_action('woocommerce_after_quantity_input_field', array($this, 'qpmn_product_quantity_input'), 6);
        add_filter('woocommerce_quantity_input_args', array($this, 'bloomer_woocommerce_quantity_changes'), 9999, 2);
    }

    public function bloomer_woocommerce_quantity_changes( $args, $product ) {
        $product_id = $product->id;
        
        if (is_product()) {
            if (qpson_is_product($product_id)) {

                if(qpson_product_type_is_sku($product_id)){
                    $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                    $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                    $qpmn_product_moq = qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id);
                    // $qpmn_product_moq = get_post_meta($product_id, Qpson_Meta::QPSON_MOQ, true);
                    if(isset($qpmn_product_moq) && !empty($qpmn_product_moq) ){
                        $args['input_value'] = $qpmn_product_moq;
                        $args['min_value'] = $qpmn_product_moq; 
                    }
                }
            }
        }
        
        return $args;
     
     }


    public function qpmn_remove_configurable_product_skus($enabled)
    {
        if (!is_admin() && is_product()) {
            $product_id = get_post()->ID;
            $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
            if ($product_type_is_configurable) {
                return false;
            }
        }

        return $enabled;
    }

    public function qpmn_product_quantity_input()
    {
        $product_id = get_post()->ID;
        if (qpson_is_product($product_id)) {
            if(qpson_product_type_is_sku($product_id)){
                $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                $product_moq = qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id);
                // $product_moq = get_post_meta($product_id, Qpson_Meta::QPSON_MOQ, true);
                if(isset($product_moq) && !empty($product_moq)){
                    echo
                    '<div style="display:inline;font-size:14px;margin-left:8px;color:red;">
                    The minimum order quantity is  ' . $product_moq . '.
                    </div>';
                }
            }   
            if(qpson_product_type_is_configurable($product_id)){
                echo
                '<div id="qpmn-product-input-moq" style="display:none;font-size:14px;margin-left:8px;color:red;">
                </div>';
            }
        }
    }

    public function qpmn_product_attribute_shortcode()
    {
        $product_id = get_post()->ID;
        if (qpson_is_product($product_id)) {
            $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
            if ($product_type_is_configurable) {
                echo
                '<div class="iframe-container">
                    <iframe width="100%" id="product-attribut-frame" name=' . QPSON_PRODUCT_ATTRIBUTE . '></iframe>
                    <div id="skeleton-detail-product-attribute" class="skeleton-detail-product-attribute">
                        <ul class="skeleton-detail-attribute">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                    </div>
                </div>';
            }
        }
    }


    public function add_update_product_instance_notice()
    {
        if (isset($_POST['productInstanceId']) && isset($_REQUEST['edit']) && isset($_REQUEST['wpnonce'])) {
            wp_verify_nonce(sanitize_text_field($_REQUEST['wpnonce']), 'builder-nonces');
            wc_add_notice("update design success!");
        }
    }

    // Products that cannot be added directly to the cart on the product category page will not display the Add to Cart button.
    public function modify_product_supports($result, $feature, Wc_Product $product)
    {

        $product_id = $product->get_id();
        if ($feature == 'ajax_add_to_cart') {
            if (qpson_is_product($product_id) && !qpson_produt_can_add_to_cart_direct($product_id)) {
                return false;
            }
        }
        return $result;
    }

    // Get product fixed price or price list based on product information. 
    public function modify_product_price_html($price_html, $product)
    {
        if (wc_get_loop_prop('name') == 'related') {
            return $price_html;
        }
        $product_id = $product->get_id();
        if (is_product() && qpson_is_product($product_id)) {
            $attribute_price = qpson_product_is_attribute_price($product_id);
            $fix_price = qpson_product_is_fix_value($product_id);
            $table_price = qpson_product_is_table_value($product_id);
            $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
            $product_type_is_sku = qpson_product_type_is_sku($product_id);

            if ($fix_price) {
                if ($product_type_is_sku) {
                    $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                    $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                    $product_moq = qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id);
                    // $product_moq = get_post_meta($product_id, Qpson_Meta::QPSON_MOQ, true);
                    if (isset($product_moq) && !empty($product_moq) && !qpson_product_finished_product($product_id)) {
                        

                        if(isset($_REQUEST['productInstanceId'])){
                            if(qpson_get_product_instance_id($product_id) == $_REQUEST['productInstanceId']){
                                return "<div style='display: flex;flex-direction: row;align-items: center;'>$price_html<div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                            }else{
                                return $price_html;
                            }
    
                        }else{
                            if(qpson_product_can_add_to_cart($product_id)){
                                if(qpson_product_allow_design($product_id)){
                                    if(qpson_product_must_design($product_id)){
                                        return "<div style='display: flex;flex-direction: row;align-items: center;'>$price_html<div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                                    }else{
                                        return $price_html;
                                    }
                                }else{
                                    return $price_html;
                                }
                            }else{
                                return "<div style='display: flex;flex-direction: row;align-items: center;'>$price_html<div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                            }
                        }
                    }else{
                        return $price_html;
                    }
                }else{
                    return $price_html;
                }
                
            }
            if ($table_price) {
                if (!$product_type_is_configurable) {
                    $price_item = "";
                    $prices = qpson_get_product_price_list_from_meta($product_id);
                    if ($prices) {
                        $price_item = "<li style='width: 100%;height: 30px;font-size: 12px;margin:0'>
                        <span style='line-height: 30px;height: 30px;width: 48%;text-align: center;display: inline-block;border-right: 1px solid rgba(0,0,0,.08);'>Qty</span>
                        <span  style='line-height: 30px;height: 30px;width: 48%;text-align: center;display: inline-block;'>Price</span></li>";
                        // get qpmn product moq  
                        $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                        $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                        $product_moq = qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id);
                        // $product_moq = get_post_meta($product_id, Qpson_Meta::QPSON_MOQ, true);
                        foreach ($prices as $price) {
                            $from = $price['from'];
                            $to = $price['to'];
                            if(isset($product_moq) && !empty($product_moq) && $product_moq && $product_moq> $from){
                                if($product_moq<= $to){
                                    $from = $product_moq;
                                }else{
                                    continue;
                                }
                            }
                            $currency_symbol = get_woocommerce_currency_symbol();
                            if (isset($price['value'])) {
                                $currency_symbol = get_woocommerce_currency_symbol();
                                $price_value = $price['value'];
                                $result = $currency_symbol . $price_value;
                            } else {
                                $result = $price['result'];
                            }
                            // $result = $price['result'];
                            
                            if($from != $to){
                                $price = end($prices)['from'] == $from ? $from . '+' : $from . '-' . $to;
                            }else{
                                $price = $from;
                            }
                            $price_item = $price_item . "<li style='width: 100%;height: 30px;font-size: 12px;border-top: 1px solid rgba(0,0,0,.08);margin:0'>
                            <span style='line-height: 30px;height: 30px;width: 48%;text-align: center;display: inline-block;border-right: 1px solid rgba(0,0,0,.08);overflow: hidden;white-space: nowrap;text-overflow: ellipsis;padding: 0 4px;'>$price</span>
                            <span  style='line-height: 30px;height: 30px;width: 48%;text-align: center;display: inline-block;overflow: hidden; white-space: nowrap;text-overflow: ellipsis;padding: 0 4px;'>$result</span></li>";
                        }
                    }
                    $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                    $versioned_attribute_id = get_post_meta($product_id, Qpson_Meta::QPSON_ATTRIBUTEVERSION, true);
                    $product_moq = qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id);
                    // $product_moq = get_post_meta($product_id, Qpson_Meta::QPSON_MOQ, true);
                    if (isset($product_moq) && !empty($product_moq) && !qpson_product_finished_product($product_id)) {
                        if(isset($_REQUEST['productInstanceId'])){
                            if(qpson_get_product_instance_id($product_id) == $_REQUEST['productInstanceId']){
                                return "<div style='display: flex;flex-direction: row;align-items: center;'><ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul><div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                            }else{
                                return "<ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul>";
                            }
    
                        }else{
                            if(qpson_product_can_add_to_cart($product_id)){
                                if(qpson_product_allow_design($product_id)){
                                    if(qpson_product_must_design($product_id)){
                                        return "<div style='display: flex;flex-direction: row;align-items: center;'><ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul><div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                                    }else{
                                        return "<ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul>";
                                    }
                                }else{
                                    return "<ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul>";
                                }
                            }else{
                                return "<div style='display: flex;flex-direction: row;align-items: center;'><ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul><div style='margin-left: 8px;font-size:14px;color:red;'>The minimum order quantity is $product_moq.</div></div>";
                            }
                        }
                    }else{
                        return "<ul id='qpson-price-list' style='width:170px; list-style: none;padding: 0;margin:0;height: auto;border: 1px solid rgba(0,0,0,.08);color: #000;margin-top:16px'>$price_item</ul>";
                    }
                    
                }else{
                    $currency_symbol = get_woocommerce_currency_symbol();
                    $prices = qpson_get_product_price_list_from_meta($product->get_id());
                    $result = "";
                    if(isset($prices[0]['value'])){
                        $result = $prices[0]['value'];
                        return "<span class='woocommerce-Price-amount amount'><bdi><span class='woocommerce-Price-currencySymbol'>$currency_symbol</span>$result</bdi></span>" ;
                    }else{
                        $result = $prices[0]['result'];
                        return "<span class='woocommerce-Price-amount amount'><bdi><span class='woocommerce-Price-currencySymbol'></span>$result</bdi></span>" ;
                    }
                }
            }
            if ($attribute_price) {
                return "
                <div style='overflow: hidden;margin-left:12px;display: flex;align-items: center;' class='price-iframe-container'>
                    <div id='skeleton-detail-product-price' class='skeleton-detail-product-price' style='width:80px;'>
                        <ul class='skeleton-detail-price'><li style='width:100%;'></li></ul>
                    </div>
                    <span class='woocommerce-Price-amount amount'><bdi id='qpson-product-price-fix'><span class='woocommerce-Price-currencySymbol'></span></bdi></span>
                    <div id='qpmn-product-price-moq' style='display:none;margin-left: 8px;font-size:14px;color:red;'></div>
                </div>";
            }
        }
        return $price_html;
    }

    // Disable Add to Cart
    public function disable_add_to_cart($is_purchased, WC_Product $product)
    {

        if (is_product()) {
            $product_id = $product->get_id();
            if (qpson_is_product($product_id)) {
                if(qpson_product_finished_product($product_id)){
                    return $is_purchased;
                }
                if (qpson_product_can_add_to_cart($product_id)){
                    if(qpson_product_allow_design($product_id)){
                        if(qpson_product_must_design($product_id)){
                            if(isset($_REQUEST['productInstanceId'])){
                                if(qpson_get_product_instance_id($product_id) == $_REQUEST['productInstanceId']){
                                    return false;
                                }else{
                                    return $is_purchased; 
                                }
                            }else{
                                return false;
                            }
                        }else{
                            return $is_purchased; 
                        }
                    }else{
                        return $is_purchased;
                    }
                }else{
                    if (!isset($_REQUEST['productInstanceId'])) {
                        return false;
                    }else{
                        return $is_purchased;
                    }
                }
            }
        }
        return $is_purchased;
    }

    // Add a Preview Design button to the product details page
    public function add_design_button()
    {
        if (is_product()) {
            $product_id = get_post()->ID;
            if (qpson_is_product($product_id)) {
                if (!qpson_product_finished_product($product_id) && !qpson_product_can_add_to_cart($product_id) && !isset($_REQUEST['productInstanceId'])) {
                    $product_instance_id = null;
                    $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
                    $property_model_id = null;
                    if (!$product_type_is_configurable) {
                        include dirname(__FILE__) . '/views/html-qpson-sku-design-button.php';
                    } else {
                        include dirname(__FILE__) . '/views/html-qpson-configurable-design-button.php';
                    }
                }
                if(qpson_product_can_add_to_cart($product_id)&& qpson_product_allow_design($product_id) && qpson_product_must_design($product_id)){
                    if(isset($_REQUEST['productInstanceId'])){
                        if(qpson_get_product_instance_id($product_id) == $_REQUEST['productInstanceId']){
                            $product_instance_id = qpson_get_product_instance_id($product_id);
                            include dirname(__FILE__) . '/views/html-qpson-sku-design-button.php';
                        }

                    }else{
                        $product_instance_id = qpson_get_product_instance_id($product_id);
                        include dirname(__FILE__) . '/views/html-qpson-sku-design-button.php';
                    }
                }
            }
        }
    }

    // Add an Edit button to the product details page
    public function add_edit_button()
    {
        $wc_user_name = wp_get_current_user();
        if (is_product()) {
            $product_id = get_post()->ID;
            if (qpson_is_product($product_id)) {
                $is_create = isset($_REQUEST['productInstanceId']);
                $product = wc_get_product( $product_id );
                $product_has_design = qpson_product_has_design_product($product_id);
                $is_allow_edit = qpson_product_allow_design($product_id);
                $product_type_is_sku = qpson_product_type_is_sku($product_id);
                $product_type_is_configurable = qpson_product_type_is_configurable($product_id);
                $is_finished_product = qpson_product_finished_product($product_id);
                $is_must_design = qpson_product_must_design($product_id);


                if($is_finished_product){
                    include dirname(__FILE__) . '/views/html-qpson-finished-product.php';
                }else{
                    if ( !$product_type_is_configurable) {
                        if($product_has_design){
                            if ($is_create) {
                                $product_instance_id = sanitize_text_field($_REQUEST['productInstanceId']);
                            }else{
                                $product_instance_id = qpson_get_product_instance_id($product_id);
                            }
                            if ($is_allow_edit) {
                                include dirname(__FILE__) . '/views/html-qpson-preview-button.php';
                                include dirname(__FILE__) . '/views/html-qpson-sku-design-button.php';
                            }else{
                                include dirname(__FILE__) . '/views/html-qpson-preview-button.php';
                            }


                        }else{
                            $product_instance_id = sanitize_text_field($_REQUEST['productInstanceId']);
                            include dirname(__FILE__) . '/views/html-qpson-preview-button.php';
                            include dirname(__FILE__) . '/views/html-qpson-sku-design-button.php';
                        }
                    } else {
                        $product_instance_id = sanitize_text_field($_REQUEST['productInstanceId']);
                        $property_model_id = sanitize_text_field($_REQUEST['propertyModelId']);
                        if ($is_create) {
                            include dirname(__FILE__) . '/views/html-qpson-preview-button.php';
                        }
                        include dirname(__FILE__) . '/views/html-qpson-configurable-design-button.php';
                    }
                }
            }
        }
    }

    // Add product_instance_id and thumbnail to the form information. 
    public function add_product_meta_form()
    {

        if (!is_product()) {
            return;
        }
        global $post;
        $product_id = $post->ID;
        $product = wc_get_product( $product_id );
        $is_qpson_product = qpson_is_product($post->ID);
        $product = wc_get_product( $product_id );
        if ($is_qpson_product) {
            $product_instance_id = null;
            $thumbnail = null;
            $qpson_sku_product_id = null;
            $property_model_id = null;
            $store_product_id = null;
            $mock_id = null;

            if(qpson_product_type_is_sku($product_id)){
                $sku_prodict_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
            }

            if (qpson_product_can_add_to_cart($product_id) || qpson_product_finished_product($product_id)) {
                $product_instance_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_INSTANCE_ID, true);
                $store_product_id = get_post_meta($product_id, Qpson_Meta::STORE_PRODUCT_ID, true);
                $qpson_sku_product_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_SKU_PRODUCT_ID, true);
                $property_model_id = get_post_meta($product_id, Qpson_Meta::QPSON_PROPERTY_MODEL_ID, true);
                if(qpson_product_type_is_sku($product_id) && qpson_product_finished_product($product_id)){
                    $qpson_sku_product_id = get_post_meta($product_id, Qpson_Meta::PRODUCT_ID, true);
                }
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
            if (isset($_REQUEST['propertyModelId']) && $_REQUEST['propertyModelId'] !== '') {
                $property_model_id = sanitize_text_field($_REQUEST['propertyModelId']);
            }
            if (isset($_REQUEST['mockId']) && $_REQUEST['mockId'] !== ''){
                $product_mock_id = sanitize_text_field($_REQUEST['mockId']);
            }
?>
            <input autocomplete="off" type="hidden" value="1" name="is_qpson_product" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($product_instance_id); ?>" name="productInstanceId" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($thumbnail) ?>" name="thumbnail" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($qpson_sku_product_id) ?>" name="skuProductId" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($property_model_id) ?>" name="propertyModelId" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($store_product_id) ?>" name="storeProductId" />
            <input autocomplete="off" type="hidden" value="<?php echo esc_attr($product_mock_id) ?>" name="mockId" />
            <input autocomplete="off" type="hidden" value="" name="ProductKeyValue" />

<?php
        }
    }
}

return new Qpson_Wc_Product();
