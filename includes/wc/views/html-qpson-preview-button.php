<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$qpson_token = Qpson::get_apikey();

$builder_url = QPSON_BUILDER_URL;

$preview_title = "Preview";
$post = get_post();
$post_id = $post->ID;

$builder_preview_url = QPSON_BUILDER_URL . "?accessToken=$qpson_token&tokenType=Basic&isPreview=true&themes=qpmn";
$button_preview_text = __('Preview', 'qpmn-pod-by-qp-group');
if ($product_instance_id) {
    $builder_preview_url = $builder_preview_url . "&productInstanceId=$product_instance_id";
}
if(get_post_meta($post_id, 'qpson_store_product_id', true)){
    $qpson_store_product_id = get_post_meta($post_id, 'qpson_store_product_id', true);
    $builder_preview_url = $builder_preview_url . "&storeProductId=" . $qpson_store_product_id;
}else{
    $qpson_store_product_id="";
}

?>

<button id="qpson-preview-buttton" type="button" style="margin-right:4px;margin-left:4px;margin-bottom:4px" class="single_add_to_cart_button button alt" value="<?php  echo esc_url( $builder_preview_url ) ?>" onclick="openQpsonModal(event,'<?php echo esc_html($button_preview_text) ?>')"><?php echo esc_html($button_preview_text) ?></button>
