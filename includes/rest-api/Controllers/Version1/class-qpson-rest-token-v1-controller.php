<?php

defined('ABSPATH') || exit;

class QPSON_REST_Token_V1_Controller extends WP_REST_Controller {

    protected $namespace = 'wc/v3';

    protected $rest_base = 'qpson';

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/token', array(
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'set_qpson_store_token'),
                'permission_callback' => array($this, 'set_token_permissions_check'),
                'show_in_index' => false,
                'args' => array(
                    'apiKey' => array(
                        'required' => false,
                        'type' => 'string',
                        'description' => 'qpson store token',
                    ),
                    'storeId' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'store id',
                    ),
                ),
            )
        ));


        register_rest_route($this->namespace, '/' . $this->rest_base . '/validate', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'validate'),
                'permission_callback' => array($this, 'validate_permissions_check'),
                'show_in_index' => false,
                'args' => array(),
            )
        ));

    }

    public function validate() {
        $key = get_option('qpson_store_apikey');
        if(!$key)
            $key = '';
        $response = rest_ensure_response(array('apiKey' => $key));
        $response->set_status(200);
        return $response;
    }

    public function validate_permissions_check($request) {
        if (wc_rest_check_post_permissions('product') && wc_rest_check_post_permissions('product', 'create')) {
            return true;
        }
        return new WP_Error('woocommerce_rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce'), array('status' => rest_authorization_required_code()));
    }


    public function set_qpson_store_token(WP_REST_Request $request) {
        $error = false;
        $body = json_decode($request->get_body(), true);
        $api_key = $body['apiKey'];
        $store_id = $body['storeId'];
        $store_id = intval($store_id);

        if (!is_string($api_key) || strlen($api_key) == 0 || $store_id == 0) {
            $error = 'Failed to update access data';
        }

        update_option('qpson_store_apikey', $api_key);
        update_option('qpson_store_id', $store_id);
        if (!$error) {
            return array(
                'success' => true
            );
        }
        return array(
            'error' => $error,
        );
    }

    public function set_token_permissions_check($request) {
        if (wc_rest_check_post_permissions('product') && wc_rest_check_post_permissions('product', 'create')) {
            return true;
        }
        return new WP_Error('woocommerce_rest_cannot_view', __('Sorry, you cannot list resources.', 'woocommerce'), array('status' => rest_authorization_required_code()));
    }

}