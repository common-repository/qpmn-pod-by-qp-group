<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$post = get_post();
$post_id = $post->ID;
$qpson_store_product_id = get_post_meta($post_id, 'qpson_store_product_id', true);

$qpson_token = Qpson::get_apikey();
$locale = get_locale();
$page_url = wc_get_product($post_id)->get_permalink();
$product_type_is_configurable = qpson_product_type_is_configurable($post_id);


$builder_url = QPSON_BUILDER_URL;
$wc_client_id=get_current_user_id();
if($wc_client_id > 0){
    $wc_user=wp_get_current_user();
    $wc_user_name=$wc_user->data && $wc_user->data->user_login ? $wc_user_name_string = '&wcUserName=' . $wc_user->data->user_login : $wc_user_name_string = "";
}else{
    $wc_user_name_string = "";
}

$qpson_store_url = md5(get_site_url());

$button_text = __('Design', 'qpmn-pod-by-qp-group');
if($product_type_is_configurable){
    if ($product_instance_id) {
        $builder_url = $builder_url . "?productInstanceId=$product_instance_id&productType=configurable";
        $button_text = __('Edit', 'qpmn-pod-by-qp-group');
    } else {
        $builder_url = $builder_url . "?productType=configurable";
    }
    if ($property_model_id){
        $builder_url = $builder_url . "&propertyModelId=$property_model_id";
    }
}

$builder_url = wp_nonce_url($builder_url . "&storeProductId=$qpson_store_product_id&accessToken=$qpson_token&tokenType=Basic&language=$locale&redirectUrl=$page_url&themes=qpmn&wcClientId=$wc_client_id&qpsonStoreUrl=$qpson_store_url" . $wc_user_name_string, 'builder-nonces' );
?>

<button disabled style="margin-right:4px;" id="qpson-configurable-design-button" class="single_add_to_cart_button button alt" value="<?php  echo esc_url( $builder_url ) ?>" onclick="saveProperyModel(event)"><?php echo esc_html( $button_text )  ?></button>
