<?php
defined( 'ABSPATH' ) or die( '' );

function ipfAddNavMenuMetaBoxes() {
    add_meta_box(
        'ipf_nav_link',
        __('Front Post', "ipf"),
        'ipfNavMenuMetaBox',
        'nav-menus',
        'side',
        'low'
    );
}

function ipfNavMenuMetaBox() {
    include( IPF .'templates/nav-menu-meta-box.php' );
}

add_action('admin_init', 'ipfAddNavMenuMetaBoxes');
add_action( 'admin_print_footer_scripts', 'ipfPrintRestrictNavMenuSettings' );

function ipfOutputOptionsPage() {
    include( IPF .'templates/options-page.php' );
}

add_action( 'admin_menu', 'ipf_settings_page' );
function ipf_settings_page() {
    add_options_page(
        'Rich Front Post Settings',
        'Rich Front Post',
        'manage_options',
        'ipf_settings',
        'ipfOutputOptionsPage'
    );
}