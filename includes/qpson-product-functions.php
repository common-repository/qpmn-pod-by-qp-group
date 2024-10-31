<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Determine whether it is a QPSON product.
function qpson_is_product($product_id) {
    $meta = get_post_meta($product_id, Qpson_Meta::IS_QPSON_PRODUCT);
    return $meta && $meta[0];
}

// Determine whether this product already has a design.
function qpson_product_has_design_product($product_id) {
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_INSTANCE_ID, true);
}

// Determine whether the product can be added to the cart.
function qpson_product_can_add_to_cart($product_id) {
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_CAN_ADD_TO_CART, true) == 'true';
}

// Determine whether design is allowed for this product.
function qpson_product_allow_design($product_id) {
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_ALLOW_DESIGN, true) == 'true';
}

// Get the product price of the QPSON product.
function qpson_get_product_price($product_id, $quantity) {
    $data = Qpson::get_client()->get("api/storeProducts/$product_id/$quantity/price");
    return floatval($data['result']);
}

// Get the instance ID of the QPSON product.
function qpson_get_product_instance_id($product_id) {
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_INSTANCE_ID, true);
}

// Get the product price list. 
function qpson_get_product_price_list(Wc_Product $product) {
    $product_id = get_post_meta($product->get_id(), Qpson_Meta::PRODUCT_ID, true);
    try {
        $data = Qpson::get_client()->get("api/products/$product_id/pricing/list");
        return $data;
    } catch (QpsonException $e) {
        return array();
    }

}

function qpson_get_cart_product_price($product_id,$cart_item) {
    $qpson_store_product_id = get_post_meta($product_id, Qpson_Meta::STORE_PRODUCT_ID, true);
    $qpson_property_model_id = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PROPERTY_MODEL_ID];
    $currency_code = get_woocommerce_currency();
    // $quantity = $cart_item['quantity'];
    $quantity = 1;
    if($qpson_store_product_id && $qpson_property_model_id ){
        $request_params=  array(
            'qpson_store_product_id' => $qpson_store_product_id,
            'qpson_property_model_id' => $qpson_property_model_id,
            'quantity' => 1,
            'currencyCode' => $currency_code
        );
        try {
            $data = Qpson::get_client()->get("api/v2/storeProduct/$qpson_store_product_id/$qpson_property_model_id/$quantity/pricing",$request_params,'qpmn-product-price_rest_api_request');
            $currency_symbol = get_woocommerce_currency_symbol();
            $price = $data['result'];
            // $data = 30.15;
            $data = html_entity_decode($currency_symbol) . $price;
            return $data;
        } catch (QpsonException $e) {
            return array();
        }
    }

}

function qpson_get_cart_product_price_two($product_id,$qpson_property_model_id) {
    $qpson_store_product_id = get_post_meta($product_id, Qpson_Meta::STORE_PRODUCT_ID, true);
    // $quantity = $cart_item['quantity'];
    $currency_code = get_woocommerce_currency();
    $quantity = 1;
    $request_params=  array(
        'qpson_store_product_id' => $qpson_store_product_id,
        'qpson_property_model_id' => $qpson_property_model_id,
        'quantity' => 1,
        'currencyCode' => $currency_code
    );
    try {
        $data = Qpson::get_client()->get("api/v2/storeProduct/$qpson_store_product_id/$qpson_property_model_id/$quantity/pricing",$request_params,'qpmn-product-price_rest_api_request');
        $currency_symbol = get_woocommerce_currency_symbol();
        $price = $data['result'];
        // $data = 30.15;
        $data = html_entity_decode($currency_symbol) . $price;
        return $data;
    } catch (QpsonException $e) {
        return array();
    }

}

// Determine whether the QPSON product can be added directly to the cart.
function qpson_produt_can_add_to_cart_direct($product_id) {
    return qpson_product_can_add_to_cart($product_id) && !qpson_product_allow_design($product_id);
}

function qpson_product_is_fix_value($product_id){
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_PRICE_STRATEGY, true) == 'price_fix';
}

function qpson_product_is_table_value($product_id){
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_PRICE_STRATEGY, true) == 'price_table';
}

function qpson_product_is_attribute_price($product_id){
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_PRICE_STRATEGY, true) == 'attributePrice';
}

function qpson_get_product_price_list_from_meta($product_id){
    $price_list_str =  get_post_meta($product_id, Qpson_Meta::PRODUCT_PRICE_TABLE, true);
    return json_decode($price_list_str,true);
}

function qpson_get_product_table_value($quantity,$product_id){
    $price_table = qpson_get_product_price_list_from_meta($product_id);
    $currency_symbol = get_woocommerce_currency_symbol();
    foreach ($price_table as $price_item) {
        $to = $price_item['to'];
        $from = $price_item['from'];
        if($quantity>=$from && $quantity<=$to){
            if(isset($price_item['value'])){
                $result = html_entity_decode($currency_symbol) . $price_item['value'];
            }else{

                $result = $price_item['result'];
            }
        }
    }
    return $result;
}


function qpson_product_type_is_sku($wc_product_id) {
    return get_post_meta($wc_product_id, Qpson_Meta::QPSON_STORE_PRODUCT_TYPE, true) == 'sku';
}

function qpson_product_type_is_configurable($wc_product_id) {
    return get_post_meta($wc_product_id, Qpson_Meta::QPSON_STORE_PRODUCT_TYPE, true) == 'configurable';
}

function qpson_product_finished_product($product_id) {
    return get_post_meta($product_id, Qpson_Meta::IS_QPSON_FINISHED_PRODUCT, true) == true ;
}

function qpson_get_mock_info($mock_id){
    $request_params=  array(
        'mock_id' => $mock_id
    );
    try {
        $data = Qpson::get_client()->get("api/mockupImages/$mock_id/status",$request_params,'qpmn-product-mock_info_api_request');
        return $data;
    } catch (QpsonException $e) {
        return array();
    }
}

function qpson_get_sku_moq($sku_prodict_id,$versioned_attribute_id){
    $request_data = new stdClass();
    if (!empty($versioned_attribute_id)) {
        $request_data->versionedAttributeId = $versioned_attribute_id;
    }
    $request_params=  array(
        'sku_prodict_id' => $sku_prodict_id
    );
    try {
        $data = Qpson::get_client()->post("api/products/$sku_prodict_id/moq",$request_data,$request_params,'qpmn-product-sku_moq_api_request');
        if($data>1){
            return $data;
        }else{
            return null;
        }
    } catch (QpsonException $e) {
        return array();
    }
}
function qpson_get_configurable_moq($sku_prodict_id,$attribute_values,$versioned_attribute_id){
    
    $request_params=  array(
        'sku_prodict_id' => $sku_prodict_id
    );
    $request_data = new stdClass();
    $request_data ->attributeValues = $attribute_values;
    if (!empty($versioned_attribute_id)) {
        $request_data->versionedAttributeId = $versioned_attribute_id;
    }
    try {
        $data = Qpson::get_client()->post("api/products/$sku_prodict_id/moq",$request_data,$request_params,'qpmn-product-configurable_moq_api_request');
        if($data>1){
            return $data;
        }else{
            return null;
        }
    } catch (QpsonException $e) {
        return array();
    }
}

function qpson_product_must_design($product_id) {
    return get_post_meta($product_id, Qpson_Meta::PRODUCT_MUST_DESIGN, true) == 'true';
}

function qpson_get_batch_moq($data_list){
    try {
        $data = Qpson::get_client()->post("api/products/moq",$data_list,array(),'qpmn-product-configurable_moq_api_request');
        return $data;
    } catch (QpsonException $e) {
        return array();
    }
}