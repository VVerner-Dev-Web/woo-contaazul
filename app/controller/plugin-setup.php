<?php defined('ABSPATH') || exit;

add_action('admin_enqueue_scripts', function(){
    $ver = uniqid('vvca-');

    wp_enqueue_style('vvca-admin', plugins_url('/app/assets/css/admin.css', VVCA_FILE), [], $ver);
    wp_enqueue_script('vvca-admin', plugins_url('/app/assets/js/admin.js', VVCA_FILE), ['jquery'], $ver);
    wp_localize_script('vvca-app', 'vvca_data', [
        'ajaxUrl'        => admin_url('admin-ajax.php')
    ]);
});

add_action('admin_menu', function () {
    add_menu_page(
        'Conta Azul',
        'Conta Azul',
        'manage_options',
        'conta-azul-settings',
        'getContaAzulAdminPage',
        plugins_url('/app/assets/icons/menu-icon.png', VVCA_FILE)
    );
});