<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('Qpson_Admin_Orders', false)) :

    class Qpson_Admin_Settings {

        public static function output() {
            include dirname(__FILE__) . '/views/html-admin-settings.php';
        }
    }
endif;