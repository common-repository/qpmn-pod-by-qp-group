<?php

if (!defined('ABSPATH')) {
    exit;
}

class Qpson_Meta {

    // Product Meta
    const IS_QPSON_PRODUCT = 'is_qpson_product';
    const PRODUCT_ID = 'qpson_id';
    const STORE_PRODUCT_ID = 'qpson_store_product_id';
    const PRODUCT_CAN_ADD_TO_CART  = 'qpson_can_add_to_cart';
    const PRODUCT_PREVIEW_IMAGE = 'qpson_preview_image';
    const PRODUCT_ALLOW_DESIGN = 'qpson_allow_design';
    const PRODUCT_INSTANCE_ID = 'qpson_product_instance_id';
    const QPSON_STORE_PRODUCT_TYPE = 'qpson_store_product_type';
    // price_table //price_fix
    const PRODUCT_PRICE_STRATEGY = 'qpson_price_strategy';
    const PRODUCT_PRICE_TABLE = 'qpson_price_table';
    // Product Attribute
    const QPSON_PUBLISHPROFILEIDS = 'qpson_publishProfileIds';
    const QPSON_PUBLISHPROFILECODES = 'qpson_publishProfileCodes';
    const QPSON_ATTRIBUTEVERSION = 'qpson_attributeVersion';
    const QPSON_PROPERTY_MODEL_ID = 'qpson_propertyModelId';

    const PRODUCT_SKU_PRODUCT_ID = 'qpson_sku_product_id';
    const IS_QPSON_FINISHED_PRODUCT = 'is_qpson_finished_product';
    const QPSON_MOQ = 'qpson_moq';
    const PRODUCT_MUST_DESIGN = 'qpson_must_design';
    




    // Order Meta
    const IS_QPSON_ORDER = 'is_qpson_order';
    const ORDER_PRODUCT_INSTANCE_ID = 'qpson_product_instance_id';
    const ORDER_THUMBNAIL = 'qpson_thumbnail';
    const ORDER_QPSON_ID = 'qpson_order_id';
    const ORDER_QPSON_ORDER_NUMBER = 'qpson_order_number';
    const ORDER_QPSON_ORDER_ITEM_ID = 'qpson_order_item_id';
    const ORDER_STORE_PRODUCT_ID = 'qpson_store_product_id';
    const ORDER_IS_FINISHED_PRODUCT = 'is_qpson_finished_product';
    const ORDER_SKU_PRODUCT_ID = 'qpson_sku_product_id';
    const ORDER_ATTRIBUTEVERSION = 'qpson_attributeVersion';
    const ORDER_PROPERTY_MODEL_ID = 'qpson_propertyModelId';
    const ORDER_MOCK_ID = 'mock_id';





    // Cart Meta
    const CART_ROOT = 'qpson_data';
    const CART_IS_QPSON_PRODUCT = 'is_qpson_product';
    const CART_PRODUCT_ID = 'qpson_product_id';
    const CART_ALLOW_EDIT = 'qpson_allow_edit';
    const CART_STORE_PRODUCT_ID = 'qpson_store_product_id';
    const CART_PRODUCT_INSTANCE_ID = 'qpson_product_instance_id';
    const CART_THUMBNAIL = 'qpson_thumbnail';
    const CART_SKU_PRODUCT_ID = 'qpson_sku_product_id';
    const CART_PROPERTY_MODEL_ID = 'qpson_property_model_id';
    const CART_IS_FINISHED_PRODUCT = 'is_qpson_finished_product';
    const CART_MOCK_ID = 'mock_id';
    const CART_PRODUCT_MOQ= 'qpson_product_moq';
    const CART_ATTRIBUTEVERSION = 'qpson_attributeVersion';
    const CART_PRODUCT_KEY_VALUE = 'qpson_product_key_value';
    


    

    // Shipping Meta
    const SHIPPING_TRACKING_NO = 'qpson_tracking_no';
    const SHIPPING_TRACKING_PROVIDER = 'qpson_tracking_provider';
    const SHIPPING_TRACKING='qpson_shipping_tracking';

    const LOGGER_TABLE_NAME = 'qpmn_pod_logs';

}