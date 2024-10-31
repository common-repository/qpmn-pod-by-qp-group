<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Qpson_Admin_Menus {


    public function __construct() {

        add_action('admin_menu', array($this, 'admin_menu'), 9);
        add_action('admin_menu', array($this, 'settings_menu'), 10);
        add_action('admin_init', array($this, 'qpson_plugin_settings_init'));

    }

    public function qpson_plugin_settings_init() {
        $args = array(
            'type' => 'string',
            'default' => null
        );
        register_setting('qpson_integration_options', 'qpson_store_apikey', $args);

        add_settings_section('qpson_integration_config', __('Integration Config','qpmn-pod-by-qp-group'), null, 'qpmn-pod-by-qp-group');
        add_settings_field('qpson_store_apikey', __('Store Key', 'qpmn-pod-by-qp-group'), array($this, 'qpson_store_apikey'), 'qpmn-pod-by-qp-group', 'qpson_integration_config');

    }

    public static function qpson_store_apikey() {
        $value = get_option('qpson_store_apikey');
        echo "<input id='qpson_store_apikey' name='qpson_store_apikey' type='text'  value='" . esc_attr( $value ) . "' />";
    }


    public function admin_menu() {
        $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MCA0NSI+PHBhdGggZmlsbD0iI2E3YWFhZCIgZD0iTTEzLjk1LDMwLjMyNWMtMy4wMzMsMC01LjUsMi40NjgtNS41LDUuNXMyLjQ2Nyw1LjUsNS41LDUuNSw1LjUtMi40NjgsNS41LTUuNS0yLjQ2Ny01LjUtNS41LTUuNVpNMTkuNDg1LDcuNDA2YzIuMjk5LS45NTEsNC44MTgtMS40ODEsNy40NjUtMS40ODEsNS43MzUsMCwxMC44NjcsMi40NzYsMTQuNDMxLDYuNDAyLTEuMjIxLC41Ny0yLjIxLDEuNTQ3LTIuNzk2LDIuNzYtMi44NjMtMy4yMTYtNy4wMTctNS4yNjItMTEuNjM1LTUuMjYyLTIuNjQ2LDAtNS4xNDEsLjY3MS03LjMzLDEuODQ2LC4xMTUtLjU5OCwuMTgtMS4yMTUsLjE4LTEuODQ2LDAtLjgzNy0uMTE3LTEuNjQ0LS4zMTUtMi40MTlaTTguMjU1LDMwLjk1OGMtLjUxNy0xLjc1NS0uODA1LTMuNjA5LS44MDUtNS41MzMsMC0yLjA5NiwuMzM3LTQuMTEsLjk0Ni02LjAwMSwuNTM5LC4wOTIsMS4wODksLjE1MSwxLjY1NCwuMTUxLC45MDQsMCwxLjc3Ni0uMTMzLDIuNjA2LS4zNjMtLjgzNiwxLjkwNy0xLjMwNiw0LjAwNy0xLjMwNiw2LjIxMywwLDEuMTEzLC4xMjMsMi4xOTgsLjM0OCwzLjI0Ny0xLjM1NCwuNDI3LTIuNTQsMS4yMzItMy40NDMsMi4yODZabTM4LjAyMS04LjAxNWMuMTAzLC44MTQsLjE3NCwxLjYzOSwuMTc0LDIuNDgxLDAsMTAuNzktOC43MSwxOS41LTE5LjUsMTkuNS0zLjQ1NSwwLTYuNjkyLS45MDEtOS41MDMtMi40NywxLjI0Ny0uNjYsMi4yODMtMS42NjMsMi45ODktMi44ODEsMS45ODcsLjkyMiw0LjE4OSwxLjQ1Miw2LjUxMywxLjQ1Miw4LjU4LDAsMTUuNi03LjAyLDE1LjYtMTUuNiwwLS43MjgtLjA2OC0xLjQ0LS4xNjYtMi4xNDMsLjQ2OSwuMTIxLC45NTksLjE5MywxLjQ2NiwuMTkzLC44NjcsMCwxLjY4Ni0uMTkzLDIuNDI2LS41MzFaTTEwLjA1LDQuNjI1YzIuODYsMCw1LjIsMi4zNCw1LjIsNS4ycy0yLjM0LDUuMi01LjIsNS4yLTUuMi0yLjM0LTUuMi01LjIsMi4zNC01LjIsNS4yLTUuMm0wLTIuNmMtNC4yOSwwLTcuOCwzLjUxLTcuOCw3LjhzMy41MSw3LjgsNy44LDcuOCw3LjgtMy41MSw3LjgtNy44LTMuNTEtNy44LTcuOC03LjhoMFpNNDMuODUsMjIuMjc1Yy0yLjU2MywwLTQuNjQ5LTIuMDg2LTQuNjQ5LTQuNjVzMi4wODYtNC42NSw0LjY0OS00LjY1LDQuNjUsMi4wODYsNC42NSw0LjY1LTIuMDg2LDQuNjUtNC42NSw0LjY1Wm0wLTcuOGMtMS43MzYsMC0zLjE0OSwxLjQxMy0zLjE0OSwzLjE1czEuNDEzLDMuMTUsMy4xNDksMy4xNSwzLjE1LTEuNDEzLDMuMTUtMy4xNS0xLjQxMy0zLjE1LTMuMTUtMy4xNVoiLz48L3N2Zz4=';
        add_menu_page(
            'QPMN',
            'QPMN POD',
            'manage_options',
            'qpmn-pod-by-qp-group',
            null,
            $icon,
            58
        );
    }


    /**
     * Add menu item.
     */
    public function settings_menu() {
        add_submenu_page(
            'qpmn-pod-by-qp-group',
            __('Settings', 'qpmn-pod-by-qp-group'),
            __('Settings', 'qpmn-pod-by-qp-group'),
            'manage_options',
            'qpmn-pod-by-qp-group',
            array($this, 'settings_page')
        );
    }

    public function settings_page() {
        return Qpson_Admin_Settings::output();
    }

}

return new Qpson_Admin_Menus();