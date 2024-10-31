<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$post = get_post();
$post_id = $post->ID;
$product_type_is_configurable = qpson_product_type_is_configurable($post_id);
$product_type_is_sku = qpson_product_type_is_sku($post_id);
$qpson_store_product_id = get_post_meta($post_id, 'qpson_store_product_id', true);
$qpson_token = Qpson::get_apikey();
$qpson_finished_product_data = get_site_url();

if($product_type_is_configurable){
    $qpson_publishProfileIds="";
    $qpson_publishProfileCodes="";
    $qpson_attributeVersion="";
    $is_attribute_price="";
    $property_model_id="";
    $qpson_product_id="";
    
    if(get_post_meta($post_id, 'qpson_publishProfileIds', true)){
        $qpson_publishProfileIds_list = get_post_meta($post_id, 'qpson_publishProfileIds', true);
        $qpson_publishProfileId = implode(',',json_decode($qpson_publishProfileIds_list,true));
        $qpson_publishProfileIds ="&qpson_publishProfileIds=" . $qpson_publishProfileId;
    }
    if(get_post_meta($post_id, 'qpson_publishProfileCodes', true)){
        $qpson_publishProfileCodes_list = get_post_meta($post_id, 'qpson_publishProfileCodes', true);
        $qpson_publishProfileCode = implode(',',json_decode($qpson_publishProfileCodes_list,true));
        $qpson_publishProfileCodes ="&qpson_publishProfileCodes=" . $qpson_publishProfileCode;
    }
    if(get_post_meta($post_id, 'qpson_attributeVersion', true)){
        $qpson_attributeVersion ="&qpson_attributeVersion=" . get_post_meta($post_id, 'qpson_attributeVersion', true);
    }
    if($attribute_price = qpson_product_is_attribute_price($post_id)){
        $is_attribute_price ="&is_attribute_price=true";
    }
    if(get_post_meta($post_id, 'qpson_propertyModelId', true)){
        $property_model_id ="&property_model_id=" . get_post_meta($post_id, 'qpson_propertyModelId', true);
    }
    if(get_post_meta($post_id, 'qpson_id', true)){
        $qpson_product_id ="&qpsonProductId=" . get_post_meta($post_id, 'qpson_id', true);
    }
    $currency_symbol = get_woocommerce_currency_symbol() ;
    $qpson_wc_currency_symbol = '&currencySymbol=' . $currency_symbol ;
    $currency_code = get_woocommerce_currency();
    $qpson_wc_currency_code = '&currencyCode=' . $currency_code ;

    $qpson_finished_product_data = $qpson_finished_product_data . "?productType=configurable&is_finished_product=true&storeProductId=$qpson_store_product_id&accessToken=$qpson_token&tokenType=Basic&qpson_server=" . QPSON_SERVER . $qpson_publishProfileIds . $qpson_publishProfileCodes . $qpson_attributeVersion . $is_attribute_price . $qpson_wc_currency_symbol . $qpson_wc_currency_code . $property_model_id . $qpson_product_id;
}

if($product_type_is_sku){
    $qpson_finished_product_data = $qpson_finished_product_data . "?productType=sku&is_finished_product=true";
}

?>

<input disabled style="display:none;" id="qpson-finished-product" value="<?php  echo esc_url( $qpson_finished_product_data ) ?>" ></input>
