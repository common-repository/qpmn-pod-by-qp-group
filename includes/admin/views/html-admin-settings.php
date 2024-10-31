<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h2><?php esc_html_e('Settings', 'qpmn-pod-by-qp-group') ?></h2>
    <h2 class="nav-tab-wrapper">
        <a href="?page=qpson&tab=settings" class="nav-tab nav-tab-active">Settings</a>
    </h2>
    <form action="options.php" method="post">
        <?php
        settings_fields('qpson_integration_options');
        do_settings_sections('qpmn-pod-by-qp-group');
        ?>
        <h2><?php esc_html_e('QPMN Shipping', 'qpmn-pod-by-qp-group') ?></h2>
        <p><?php esc_html_e('To enable/disable QPMN shipping for your store go to', 'qpmn-pod-by-qp-group') ?> <a href="admin.php?page=wc-settings&tab=shipping&section=qpmn_shipping">Qpmn Shipping Setting</a></p>
        <?php
        submit_button($text = __('Save', 'qpmn-pod-by-qp-group'), $type = 'primary');
        ?>
    </form>
</div>