<?php

if (!defined('ABSPATH')) {
    exit;
}

class Qpson_Shipping_Method extends WC_Shipping_Method {
    const WOO_TRUE = 'yes';
    const WOO_FALSE = 'no';

    const DEFAULT_ENABLED = self::WOO_TRUE;
    const DEFAULT_OVERRIDE = self::WOO_FALSE;

    private $shipping_enabled;
    private $shipping_override;

    private $logger;

    public function __construct() {
        parent::__construct();

        $this->id = 'qpmn_shipping';
        $this->method_title = __('QPMN Shipping', 'qpmn-pod-by-qp-group');
        $this->method_description = __('Calculate live shipping rates based on actual QPMN shipping costs.', 'qpmn-pod-by-qp-group');
        $this->title = __('QPMN Shipping', 'qpmn-pod-by-qp-group');
        $this->init();
        $this->shipping_enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : self::DEFAULT_ENABLED;
        $this->shipping_override = isset($this->settings['override_defaults']) ? $this->settings['override_defaults'] : self::DEFAULT_OVERRIDE;
        $this->logger = QPSON()->get_logger();
    }


    public function add_shipping_details(WC_Shipping_Rate $method, $index) {
        if (isset($method->get_meta_data()['qpsonShippingMethod'])) {
            $meta = $method->get_meta_data();
            $receiveDateFrom = $meta['receiveDateFrom'];
            $receiveDateTo = $meta['receiveDateTo'];
            $minDay = $meta['minDay'];
            $maxDay = $meta['maxDay'];
            $produceMinDay = $meta['produceMinDay'];
            $produceMaxDay = $meta['produceMaxDay'];

            include __DIR__ . '/html-qpson-shipping-method-description.php';
        }
    }


//    public function get_post_data() {
//        if ( ! empty( $this->data ) && is_array( $this->data ) ) {
//            return $this->data;
//        }
//        $_POST[$this->get_field_key('enabled')] = self::DEFAULT_ENABLED;
//        return $_POST; // WPCS: CSRF ok, input var ok.
//    }


    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'=>array(
                'title' => __('Enabled', 'qpmn-pod-by-qp-group'),
                'type' => 'checkbox',
                'label' => __('Enable QPMN Shipping Method', 'qpmn-pod-by-qp-group'),
                'default' => self::DEFAULT_ENABLED
//                ,'disabled'=>true
            ),
            'override_defaults' => array(
                'title' => __('Override', 'qpmn-pod-by-qp-group'),
                'type' => 'checkbox',
                'label' => __('Override standard WooCommerce shipping rates', 'qpmn-pod-by-qp-group'),
                'default' => self::DEFAULT_OVERRIDE,
            )
        );
    }

    function init() {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

        add_action('woocommerce_load_shipping_methods', array($this, 'load_shipping_methods'));

        add_filter('woocommerce_cart_shipping_packages', array($this, 'split_qpson_shipping_packages'));

        add_action('woocommerce_after_shipping_rate', array($this, 'add_shipping_details'), 10, 2);
    }

    function load_shipping_methods($package) {
        if (!$package) {
            WC()->shipping()->register_shipping_method($this);
            return;
        }
        if (self::WOO_FALSE === $this->enabled) {
            return;
        }
        if (isset($package['managed_by_qpson']) && true === $package['managed_by_qpson']) {
            if (self::WOO_TRUE === $this->shipping_override) {
                WC()->shipping()->unregister_shipping_methods();
            }
            WC()->shipping()->register_shipping_method($this);
        }
    }

    /**
     * split qpson package
     * @param $packages
     * @return array|mixed
     */
    public function split_qpson_shipping_packages($packages = array()) {
        if ($this->shipping_enabled !== self::WOO_TRUE) {
            return $packages;
        }

        if (count($packages) == 0) return $packages;

        $sample_package = $packages[0];
        $qpson_package = array(
            'managed_by_qpson' => true,
            'qpson_shipping_rates' => array(
                'express' => array(
                    'cost' => 2000
                ),
                'standard' => array(
                    'cost' => 1000
                )
            ),
            'contents' => array(),
            'user' => $sample_package['user'],
            'destination' => $sample_package['destination'],
            'contents_cost' => 0,
            'cart_subtotal' => $sample_package['cart_subtotal'],
            'applied_coupons' => $sample_package['applied_coupons']
        );

        $remove_package_keys = array();

        foreach ($packages as $package_key => $package) {
            // Collect skus and quantity
            $qpson_content_keys = array();
            foreach ($package['contents'] as $item_key => $item) {
                if (isset($item[Qpson_Meta::CART_ROOT])) {
                    $qpson_content_keys[] = $item_key;
                }
            }
            foreach ($qpson_content_keys as $item_key) {
                $item = $package['contents'][$item_key];
                $qpson_package['contents'][] = $item;
                unset($packages[$package_key]['contents'][$item_key]);
                $package['contents_cost'] = $package['contents_cost'] - $item['line_total'];
                $qpson_package['contents_cost'] = $package['contents_cost'] + $item['line_total'];
            }

            if (count($packages[$package_key]['contents']) == 0) {
                $remove_package_keys[] = $package_key;
            }
        }

        //remove empty package
        foreach ($remove_package_keys as $package_key) {
            array_splice($packages, $package_key, 1);
        }

        //add qpson package
        if (count($qpson_package['contents']) > 0) {
            $packages[] = $qpson_package;
        }

        return $packages;
    }

    public function calculate_shipping($package = array()) {
        try {
            $qpson_shipping_method = $this->load_qpson_shipping_method($package);
        } catch (Exception $e) {
            return;
        }
        if ($qpson_shipping_method && is_array($qpson_shipping_method) && count($qpson_shipping_method) > 0) {
            $rates = $this->generate_rates($qpson_shipping_method);
            foreach ($rates as $rate) {
                $rate['package'] = $package;
                $this->add_rate($rate);
            }
        }
    }


    public function generate_rates($qpson_shipping_methods) {

        $productionTime = $qpson_shipping_methods['productionTime'];
        $shippingMethods = $qpson_shipping_methods['shippingMethods'];

        $rates = array();
        foreach ($shippingMethods as $shippingMethod) {

            $rate = array(
                'id' => $this->id . $shippingMethod['code'],
                'label' => $shippingMethod['code'],
                'cost' => $shippingMethod['cost'],
                'calc_tax' => 'per-order',
                'meta_data' => array(
                    'qpsonShippingMethod' => true,
                    'receiveDateFrom' => date('Y-m-d', strtotime($shippingMethod['receiveDateFrom'])),
                    'receiveDateTo' => date('Y-m-d', strtotime($shippingMethod['receiveDateTo'])),
                    'minDay' => $shippingMethod['minDay'],
                    'maxDay' => $shippingMethod['maxDay'],
                    'produceMinDay' => $productionTime['minDay'],
                    'produceMaxDay' => $productionTime['maxDay']
                )
            );
            $rates[] = $rate;
        }
        return $rates;
    }

    public function load_qpson_shipping_method($package = array()) {

        if (count($package) == 0)
            return array();
        $qpson_client = Qpson::get_client();

        $destination = $package['destination'];
        $contents = $package['contents'];
        $products = array();
        foreach ($contents as $content) {
            $qpsonSkuProductId = null;
            if (isset($content['qpson_data']) && isset($content['qpson_data']['qpson_sku_product_id'])) {
                $qpsonSkuProductId = $content['qpson_data']['qpson_sku_product_id'];
            }
            $product = array(
                'qty' => $content['quantity'],
                'product' => $content['qpson_data']['qpson_store_product_id'],
                'qpsonSkuProductId' => $qpsonSkuProductId
            );
            $products[] = $product;
        }
        $request_body = array(
            'address' => array(
                'country' => $destination['country'],
                'state' => isset($destination['state']) && !empty($destination['state']) ? $destination['state'] : 'ABC',
                'city' => isset($destination['city']) && !empty($destination['city']) ? $destination['city'] : 'ABC',
                'postcode' => $destination['postcode']
            ),
            'products' => $products,
            'locale' => get_locale(),
            'currency' => get_woocommerce_currency(),
            'accuracy' => 2
        );
        $data = $qpson_client->post('api/store/shippingMethods', $request_body,array(),'qpmn-pod-shipping_rest_api_request');
        return $data;
    }
}
