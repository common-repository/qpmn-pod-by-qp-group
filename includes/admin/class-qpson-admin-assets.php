<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Admin_Assets {

    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }


    public function admin_scripts() {

        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $version = constant('QPSON_VERSION');

        wp_register_script('qpson_admin_preview_image', QPSON()->plugin_url() . '/assets/js/preview-image.js', array('jquery'), $version);
        if ($this->is_order_meta_box_screen($screen_id)) {
            wp_enqueue_script('qpson_admin_preview_image');
        }
    }

    private function is_order_meta_box_screen($screen_id) {
        if(function_exists('wc_get_order_types')) {
            return false;
        }
        $screen_id = str_replace('edit-', '', $screen_id);

        $types_with_metaboxes_screen_ids = array_filter(
            array_map(
                'wc_get_page_screen_id',
                wc_get_order_types('order-meta-boxes')
            )
        );
        return in_array($screen_id||[], $types_with_metaboxes_screen_ids, true);
    }

}

return new Qpson_Admin_Assets();