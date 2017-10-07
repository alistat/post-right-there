<?php
defined( 'ABSPATH' ) or die( '' );

function prthAddNavMenuMetaBoxes() {
    add_meta_box(
        'prth_nav_link',
        __('Post Right There', "prth"),
        'prthNavMenuMetaBox',
        'nav-menus',
        'side',
        'low'
    );
}

function prthNavMenuMetaBox() {
    include( PRTH .'templates/nav-menu-meta-box.php' );
}

add_action('admin_init', 'prthAddNavMenuMetaBoxes');
add_action( 'admin_print_footer_scripts', 'prthPrintRestrictNavMenuSettings' );

function prthOutputOptionsPage() {
    include( PRTH .'templates/options-page.php' );
}

add_action( 'admin_menu', 'prth_settings_page' );
function prth_settings_page() {
    add_options_page(
        'Post Right There Settings',
        'Post Right There',
        'manage_options',
        'prth_settings',
        'prthOutputOptionsPage'
    );
}