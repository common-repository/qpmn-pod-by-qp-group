<?php
/**
 * Plugin Name: QPMN POD by QP Group
 * Plugin URI: https://www.qpmarketnetwork.com/app/e-shopper/woocommerce-plugin/
 * Description: QPMN is backed by Q P Group, a publicly listed company which has more than 35 years’ experience as a leading printing company with special focus on drop shipping and Next-generation Print-On-Demand (POD) services.
 * version: 1.5.2
 * Author: QP Group
 * Author URI: https://www.qpmarketnetwork.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: qpmn-pod-by-qp-group
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

require plugin_dir_path(__FILE__) . 'vendor/autoload.php';

if (!defined('QPSON_PLUGIN_DIR')) {
    define('QPSON_PLUGIN_DIR', __DIR__);
}

if ( ! defined( 'QPSON_PLUGIN_FILE' ) ) {
    define( 'QPSON_PLUGIN_FILE', __FILE__ );
}

if (!class_exists('Qpson', false)) {
    include_once  __DIR__ .'/includes/class-qpson.php';
}

function QPSON() {
    return Qpson::instance();
}
$GLOBALS['qpson'] = QPSON();
