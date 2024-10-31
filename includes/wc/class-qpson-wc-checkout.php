<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Wc_Checkout
{

    public function __construct()
    {
        add_action('woocommerce_cart_item_name', array($this, 'add_thumbnail'), 10, 3);
    }

    // Add the product preview image and preview button to the checkout page.
    function add_thumbnail($name, $cart_item, $cart_item_key)
    {
        if (is_checkout() && isset($cart_item[Qpson_Meta::CART_ROOT])) {
            $img_id = $cart_item['data']->get_image_id();
            $init_img_url = wp_get_attachment_image_url($img_id);
            if($cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_IS_FINISHED_PRODUCT] == true){
                $img = '<img src="' . $init_img_url . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail qpson-preview-img" alt="" loading="lazy" width="50" height="50">';
                $name =  "<div style='display: inline-block; word-wrap: break-word; max-width: 320px; margin-left:12px;float:left;margin-bottom:12px; '>$name</div>";
                return '<div style="margin-left:12px;margin-top:12px;">' . $img . '</div>' . $name;

            }else{
                $meta = $cart_item[Qpson_Meta::CART_ROOT];
                $imgUrl = $meta[Qpson_Meta::CART_THUMBNAIL];
                $mock_id = $meta[Qpson_Meta::CART_MOCK_ID];
                if (!empty($mock_id)) {
                    $img = '<img src="' . $init_img_url . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail  qpson-preview-img-mock" data-src="' .$imgUrl. '" alt="" loading="lazy" srcset="' . $init_img_url . ' 324w,' . $init_img_url . ' 510w" data-mockid="'. $mock_id .'" sizes="(max-width: 50px) 100vw, 50px" width="50" height="50">';
                }else{
                    $img = '<img src="' . $init_img_url . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail qpson-preview-img" data-src="' .$imgUrl. '" alt="" loading="lazy" srcset="' . $init_img_url . ' 324w,' . $init_img_url . ' 510w" sizes="(max-width: 50px) 100vw, 50px" width="50" height="50">';
                }
                $product_instance_id = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_PRODUCT_INSTANCE_ID];
                $qpson_store_product_id = $cart_item[Qpson_Meta::CART_ROOT][Qpson_Meta::CART_STORE_PRODUCT_ID];
                $qpson_token = Qpson::get_apikey();
                $locale = get_locale();
                $permalink = QPSON_BUILDER_URL . "?storeProductId=$qpson_store_product_id&productInstanceId=$product_instance_id&isPreview=true&themes=qpmn&accessToken=$qpson_token&tokenType=Basic&language=$locale&redirectUrl=" . get_permalink();
                $previewText = __('Preview', 'qpmn-pod-by-qp-group');
                $name =  "<div style='display: inline-block; word-wrap: break-word; max-width: 320px; margin-left:12px;float:left;margin-bottom:12px; '>$name</div>";
                return '<div style="margin-left:12px;margin-top:12px;" class="checkout-product-name"><div>' . $img . "</div><button type='button' value=\"$permalink\" class=\"order-preview\" style='margin-left:4px'>$previewText</button>" . '</div>' . $name;
            }      
        }
        return $name;
    }
}

return new Qpson_Wc_Checkout();
