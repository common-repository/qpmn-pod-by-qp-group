<?php


use Monolog\Logger;
use Qpp\Qpson\RestApi\Server;

defined('ABSPATH') || exit;

final class Qpson {

    public $version = '1.5.2';

    protected static $_instance = null;

    private $logger = null;

    public function __construct() {
        $this->define_constants();
        $this->load_plugin_textdomain();
        $this->includes();
        $this->init_hooks();

        $this->logger = new Logger('qpmn_pod');
        $this->logger->pushHandler(Qpson_Log_Handler::getInstance());
    }

    private function is_request($type) {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    private function define_constants() {
        include_once __DIR__ . '/qpson-server-config.php';
        define('QPSON_VERSION', $this->version);
        define('QPSON_COMPOSING_PREVIEW_URL', QPSON_FILE_SERVER . 'composingPreview/');
    }

    public function includes() {
        include_once __DIR__ . '/class-qpson-activator.php';
        include_once __DIR__ . '/class-qpson-deactivator.php';
        include_once __DIR__ . '/class-qpson-meta.php';
        include_once __DIR__ . '/log-handlers/class-qpson-log-handler.php';
        include_once __DIR__ . '/qpson-product-functions.php';
        include_once __DIR__ . '/qpson-order-functions.php';
        include_once __DIR__ . '/class-qpson-client.php';

        if ($this->is_request('admin')) {
            include_once __DIR__ . '/admin/class-qpson-admin.php';
        }
        if ($this->is_request('frontend')) {
            include_once __DIR__ . '/wc/class-qpson-wc.php';
        }
    }

    public function init_hooks() {
        register_activation_hook(QPSON_PLUGIN_FILE, array('Qpson_Activator', 'activate'));
        register_deactivation_hook(QPSON_PLUGIN_FILE, array('Qpson_Deactivator', 'deactivate'));
        add_action('init', array($this, 'load_rest_api'));
        add_action('woocommerce_shipping_init', array($this, 'qpson_shipping_method_init'));
    }

    public function qpson_shipping_method_init() {
        if (!class_exists('Qpson_Shipping_Method')) {
            include_once __DIR__ . '/class-qpson-shipping-method.php';
            new Qpson_Shipping_Method();
        }
    }

    public function plugin_url() {
        return untrailingslashit(plugins_url('/', QPSON_PLUGIN_FILE));
    }

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('qpmn-pod-by-qp-group', false, plugin_basename(QPSON_PLUGIN_DIR) . '/languages');
    }

    public function load_rest_api() {
        Server::instance()->init();
    }

    public static function get_client($disable_ssl = false) {
        return new Qpson_Client($disable_ssl);
    }

    public static function get_apikey() {
        return get_option('qpson_store_apikey');
    }

    public function get_logger():Logger {
        return $this->logger;
    }


}