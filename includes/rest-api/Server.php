<?php

namespace Qpp\Qpson\RestApi;

defined('ABSPATH') || exit;


class Server {

    protected static $_instance;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected $controllers = array();

    public function init() {
        add_action('rest_api_init', array($this, 'register_rest_routes'), 10);
    }

    public function register_rest_routes() {
        foreach ($this->get_rest_namespaces() as $namespace => $controllers) {
            foreach ($controllers as $controller_name => $controller_class) {
                $this->controllers[$namespace][$controller_name] = new $controller_class();
                $this->controllers[$namespace][$controller_name]->register_routes();
            }
        }
    }

    protected function get_rest_namespaces() {
        return array(
            'qpson/v1' => $this->get_v1_controllers()
        );
    }

    protected function get_v1_controllers() {
        return array(
            'token' => 'QPSON_REST_Token_V1_Controller',
            'file' => 'QPSON_REST_File_V1_Controller'
        );
    }

    public static function get_path() {
        return dirname(__DIR__);
    }
}
