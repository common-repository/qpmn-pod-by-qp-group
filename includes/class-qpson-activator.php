<?php
defined('ABSPATH') || exit;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    qpson
 * @subpackage qpson/includes
 * @author     smart
 */
class Qpson_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate($network_wide) {
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {

            deactivate_plugins(QPSON_PLUGIN_FILE); // Deactivate plugin
            wp_die("Sorry, but you can't run this plugin, it requires PHP 7.4.0 or higher.");

        }

        if (!self::woo_check()) {
            deactivate_plugins(QPSON_PLUGIN_FILE); // Deactivate plugin
            wp_die("Sorry, but you can't run this plugin, it requires WooCommerce installed.");
        }

        if (is_multisite() && $network_wide) {

            foreach (get_sites(['fields' => 'ids']) as $blog_id) {
                switch_to_blog($blog_id);
                self::db_check();
                self::add_options();
                restore_current_blog();
            }

        } else {
            self::db_check();
            self::add_options();
        }

    }

    public static function woo_check() {
        $requiredPlugin = 'woocommerce/woocommerce.php';

        if (is_multisite()) {
            if (is_plugin_active_for_network($requiredPlugin)) {
                $result = true;
            } else {
                $result = is_plugin_active($requiredPlugin);
            }
        } else {
            $result = is_plugin_active($requiredPlugin);
        }

        return $result;
    }

    public static function db_check() {

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta(self::get_table_schema());
    }


    static function get_table_schema() {
        global $wpdb;
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $tableName = $wpdb->prefix . Qpson_Meta::LOGGER_TABLE_NAME;

        $tables = "CREATE TABLE IF NOT EXISTS " . sanitize_text_field($tableName) . " (
			id BIGINT UNSIGNED NOT NULL auto_increment,
			log TEXT,
			context TEXT, 
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL ,
            PRIMARY KEY (id)
		) $collate;";

        return $tables;
    }


    public static function add_options() {
        add_option('qpson_store_apikey', '');
        add_option('qpson_enable_tax_calculate', false);
    }


}
