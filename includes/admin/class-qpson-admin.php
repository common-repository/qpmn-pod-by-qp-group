<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Qpson_Admin {

	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
	}

	public function includes() {
		include_once __DIR__ . '/class-qpson-admin-menus.php';
		include_once __DIR__ . '/class-qpson-admin-settings.php';
        include_once __DIR__ . '/class-qpson-admin-assets.php';
        include_once __DIR__ . '/wc/class-qpson-admin-wc-order.php';
	}
}

return new Qpson_Admin();
