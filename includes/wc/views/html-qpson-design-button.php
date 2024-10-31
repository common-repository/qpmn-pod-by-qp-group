<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$post = get_post();
$post_id = $post->ID;
$qpson_product_id = get_post_meta($post_id, 'qpson_id', true);
$qpson_token = Qpson::get_apikey();
$locale = get_locale();
$page_url = wc_get_product($post_id)->get_permalink();

$builder_url = QPSON_BUILDER_URL;
$wc_client_id=get_current_user_id();
if($wc_client_id > 0){
    $wc_user=wp_get_current_user();
    $wc_user_name=$wc_user->data && $wc_user->data->user_login ? $wc_user_name_string = '&wcUserName=' . $wc_user->data->user_login : $wc_user_name_string = "";
}else{
    $wc_user_name_string = "";
}

$button_text = __('Design', 'qpmn-pod-by-qp-group');
if ($product_instance_id) {
    $builder_url = $builder_url . "?productInstanceId=$product_instance_id";
    $button_text = __('Edit', 'qpmn-pod-by-qp-group');
} else {
    $builder_url = $builder_url . "?productId=$qpson_product_id";
}

$builder_url = wp_nonce_url($builder_url . "&accessToken=$qpson_token&tokenType=Basic&language=$locale&redirectUrl=$page_url&themes=qpmn&wcProductId=$post_id&wcClientId=$wc_client_id" . $wc_user_name_string, 'builder-nonces' );
?>
<a style="margin-right:4px line-height:1.75;" class="button alt wp-element-button" href="<?php  echo esc_url( $builder_url ) ?> "><?php echo esc_html( $button_text )  ?></a>
