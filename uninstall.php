<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

if (is_multisite()) {
    foreach (get_sites(['fields' => 'ids']) as $blog_id) {
        switch_to_blog($blog_id);
        qpson_clear_data();
        restore_current_blog();
    }
} else {
    qpson_clear_data();
}


function qpson_clear_data() {
    delete_option('qpson_store_apikey');
    delete_option('qpson_enable_tax_calculate');
    qpson_drop_tables();
}

function qpson_drop_tables() {
    global $wpdb;
    include_once __DIR__ . '/includes/class-qpson-meta.php';
    $table_name = $wpdb->prefix . Qpson_Meta::LOGGER_TABLE_NAME;
    $wpdb->query(
        $wpdb->prepare(
           "DROP TABLE IF EXISTS %s",
           $table_name
        )
     );

}