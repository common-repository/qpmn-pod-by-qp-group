<?php

defined('ABSPATH') || exit;

class QPSON_REST_File_V1_Controller extends WP_REST_Controller {

    protected $namespace = 'wc/v3';
    protected $rest_base = 'qpson';

    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/file', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'upload_file'),
                'show_in_index' => false,
                'args' => array(
                    'src' => array(
                        'required' => true,
                        'type' => 'string',
                        'description' => 'file url',
                    ),
                    'productId' => array(
                        'required' => false,
                        'type' => 'integer',
                        'description' => 'product id'
                    )
                ),
            )
        ));
    }


    public function upload_file(WP_REST_Request $request) {
        if (!isset($request['src']) || !$request['src']) {
            return new WP_Error("src is required");
        }

        $product_id = 0;
        if (isset($request['product_id'])) {
            $product_id = $request['product_id'];
        }

        $upload = wc_rest_upload_image_from_url(esc_url_raw($request['src']));
        if (is_wp_error($upload)) {
            throw new WC_REST_Exception('woocommerce_product_image_upload_error', $upload->get_error_message(), 400);
        }

        $attachment_id = wc_rest_set_uploaded_image_as_attachment($upload, $product_id);
        return array(
            'success' => true,
            'id' => $attachment_id
        );
    }

}