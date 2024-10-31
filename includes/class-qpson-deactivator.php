<?php
defined('ABSPATH') || exit;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    qpson
 * @subpackage qpson/includes
 * @author     smart
 */
class Qpson_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate($network_deactivating) {
        if (is_multisite() && $network_deactivating) {
            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                self::disable_products();
                restore_current_blog();
            }
        } else {
            self::disable_products();
        }
    }

    public static function disable_products() {
        if (defined('QPSON_DISABLE_PRODUCT_WHEN_DEACTIVATE') && !constant('QPSON_DISABLE_PRODUCT_WHEN_DEACTIVATE')) {
            return;
        }
        $args = array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => Qpson_Meta::IS_QPSON_PRODUCT,
                    'value' => 1,
                ),
            ),
        );

        $qp_product_query = new WP_Query($args);
        $qp_products = $qp_product_query->get_posts();

        foreach ($qp_products as $p) {
            $tmp = (array)$p;
            $tmp['post_status'] = 'draft';
            wp_update_post($tmp);
        }
    }

}
