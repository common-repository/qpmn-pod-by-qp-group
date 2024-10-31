<?php
if ( ! defined( 'ABSPATH' ) ) exit;
function qpson_is_order_item(WC_Order_Item $order_item) {
    return $order_item->meta_exists(Qpson_Meta::IS_QPSON_PRODUCT);
}