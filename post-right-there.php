<?php
/*
Plugin Name: Post Right There
Plugin URI: https://alistat.eu/wordpress/post-right-there
Description: Create, edit inline and manage posts, pages and custom posts from the front end.
Author: Stathis Aliprandis
Author URI: https://alistat.eu
Version: 0.0
License: GPL2

Post Right There is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Rich In Place Front Post is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Rich In Place Front Post. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/
defined( 'ABSPATH' ) or die( '' );

define('PRTH', plugin_dir_path(__FILE__));

add_action( 'init', 'prthLoadTextdomain' );
function prthLoadTextdomain() {
    load_plugin_textdomain( 'prth', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

function prthFilePath($path) {
    return plugins_url($path, __FILE__);
}

// ---------- Enqueue the stuff ----------
add_action( 'wp_enqueue_scripts', 'prthEnqueueScripts' );
function prthEnqueueScripts() {
    if (prthShouldLoad()) {
        wp_enqueue_script('prthData', prthFilePath('/js/data.js'), array('jquery'), "0.4.1", true);
        wp_enqueue_script('prthEditor', prthFilePath('/js/editor.js'), array('jquery'), "0.4.1", true);
        wp_enqueue_script('prth', prthFilePath('/js/prth.js'), array('jquery', 'prthEditor'), "0.4.1", true);
        wp_enqueue_style('prth-css', prthFilePath('/css/prth.css'), array(), "0.4.1");

        // vf
        wp_enqueue_script( 'prth-vf', prthFilePath( '/js/lib/vf.js'), array( 'jquery' ), null, true);

        // jquery
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('jquery-ui-base', "//code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css");

        // codemirror
        wp_enqueue_script('codemirror', prthFilePath("/js/lib/codemirror/codemirror.min.js"), array(), "5.19.0", true);
        wp_enqueue_style('codemirror-style', prthFilePath("/css/lib/codemirror.min.css"));
        wp_enqueue_script('codemirror-htmlmixed', prthFilePath("/js/lib/codemirror/htmlmixed.min.js"), array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-javascript', prthFilePath("/js/lib/codemirror/javascript.min.js"), array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-css', prthFilePath("/js/lib/codemirror/css.min.js"), array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-xml', prthFilePath("/js/lib/codemirror/xml.min.js"), array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-matchbrackets', prthFilePath("/js/lib/codemirror/matchbrackets.min.js"), array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-active-line', prthFilePath("/js/lib/codemirror/active-line.min.js"), array('codemirror'), "5.19.0", true);

        wp_enqueue_media();

        // chosen plugin for multiselect dropdowns
        wp_enqueue_script('chosen.jquery', prthFilePath('/js/lib/chosen.jquery.min.js'), array('jquery'), null, true);
        wp_enqueue_style('chosen-style', prthFilePath('/css/lib/chosen.min.css'));

        // toastr for notifications
        wp_enqueue_script('toastr', prthFilePath("/js/lib/toastr.min.js"), array('jquery', 'prth'), "2.1.3", true);
        wp_enqueue_style('toastr-style', prthFilePath("/css/lib/toastr.min.css"), array(), "2.1.3");

        $lang = prthLangJs();
        wp_localize_script('prth', "prthLang", $lang);
        wp_localize_script('prthEditor', "prthLang", $lang);
    }
}

function prthSettingsEnqueue($hook) {
    if ( 'settings_page_prth_settings' != $hook ) {
        return;
    }

    wp_enqueue_script( 'prth-vf', prthFilePath( '/js/lib/vf.js'), array( 'jquery' ), null, false);
    wp_enqueue_script('chosen.jquery', prthFilePath('/js/lib/chosen.jquery.min.js'), array('jquery'), null, false);
    wp_enqueue_style('chosen-style', prthFilePath('/css/lib/chosen.min.css'));
}
add_action( 'admin_enqueue_scripts', 'prthSettingsEnqueue' );


function prthGetSettings() {
    return get_option("prth_settings", array("postTypes" => array()));
}

function prthSetSettings($settings) {
    update_option('prth_settings', $settings);
}


function prthShouldLoad() {
    return apply_filters("prth_should_load", is_user_logged_in() && ((current_user_can('edit_posts') || current_user_can('edit_pages')
            || current_user_can('edit_others_posts')) || current_user_can('edit_others_pages')));
    // ya nevah know what 's goin' on
}


function prthGetEditRestrictions($post = null, $tag=null) {
    if (is_numeric($post)) {
        $postId = intval($post);
    } else if (is_array($post)) {
        $postId = $post['ID'];
    } else if ($post instanceof WP_Post) {
        $postId = $post->ID;
    } else if (empty($post)) {
        $postId = get_the_ID();
    }
    if (empty($postId)) {
        return new WP_Error(-1, "No post could be determined", $post);
    }

    $canEdit = current_user_can('edit_post', $postId);
    $canPublish = current_user_can('publish_post', $postId);

    $restrictions = array(
        'allow' => $canEdit,
        'showPostSettingsUi' => $canEdit,
        'taxonomy' => array(),
        'meta' => array(),
        'custom' => array(),
        'post_title' => array('allow' => $canEdit),
        'post_name' => array('allow' => $canEdit),
        'post_content' => array('allow' => $canEdit),
        'post_excerpt' => array('allow' => $canEdit),
        'post_status' => array(
            'allow' => $canEdit,
            'values' => null,
            'not_values' => $canPublish ? null : array('publish'),
        ),
        'thumbnail' => array(
            'allow' => $canEdit,
            'load_media' => $canEdit,
            'load_user_images' => true,
            'load_custom_images' => null,
            'can_upload' => true
        ),
    );

    return apply_filters("prth_get_edit_restrictions", $restrictions, $postId, $tag);
}


function prthCanEdit($post = null, $tag = null) {
    $restrictions = prthGetEditRestrictions($post, $tag);
    return $restrictions['allow'];
}

function prthNoEditExcludedTypes($restrictions, $postId) {
    if (!$restrictions['allow']) return $restrictions;
    $settings = prthGetSettings();
    $restrictions['allow'] = !in_array(get_post_type($postId), $settings["postTypes"]);
    return $restrictions;
}
add_filter("prth_get_edit_restrictions", "prthNoEditExcludedTypes", 10, 2);

function prthCanPublish($post = null, $tag = null) {
    $restrictions = prthGetEditRestrictions($post, $tag);
    return empty($restrictions['post_status'])
        || ($restrictions['post_status']['allow']
            && (empty($restrictions['post_status']['values']) || in_array("publish", $restrictions['post_status']['values'])) );
}

function prthGetCreateRestrictions($postType, $tag) {
    $canCreate = current_user_can(get_post_type_object($postType)->cap->create_posts);
    $canPublish = current_user_can(get_post_type_object($postType)->cap->publish_posts);

    $restrictions = array(
        'allow' => $canCreate,
        'taxonomy' => array(),
        'meta' => array(),
        'custom' => array(),
        'post_title' => array('allow' => $canCreate),
        'post_name' => array('allow' => $canCreate),
        'post_content' => array('allow' => $canCreate),
        'post_excerpt' => array('allow' => $canCreate),
        'post_status' => array(
            'allow' => $canCreate,
            'values' => null,
            'not_values' => $canPublish ? null : array('publish'),
        ),
        'thumbnail' => array(
            'allow' => $canCreate,
            'load_media' => $canCreate,
            'load_user_images' => true,
            'load_custom_images' => null,
            'can_upload' => true
        ),
    );

    return apply_filters("prth_get_create_restrictions", $restrictions, $postType, $tag);
}

function prthCanCreateType($postType, $tag=null) {
    $restrictions = prthGetCreateRestrictions($postType, $tag);
    return $restrictions['allow'];
}

function prthCanPublishType($postType, $tag=null) {
    $restrictions = prthGetCreateRestrictions($postType, $tag);
    return empty($restrictions['post_status'])
    || ($restrictions['post_status']['allow']
        && (empty($restrictions['post_status']['values']) || in_array("publish", $restrictions['post_status']['values'])) );
}

function prthCanDeleteAttachment($attachmentId) {
    if (!is_numeric($attachmentId)) {
        return false;
    }
    return apply_filters('prth_can_delete_attachment', current_user_can('delete_post', $attachmentId), $attachmentId);
}

function prthCanChangeSettings() {
    return apply_filters("prth_can_change_options", current_user_can("manage_options"));
}

function prthRestrictionAllowed($restrictions, $restriction=null) {
    $restr = prthRestrictionGet($restrictions, $restriction);
    return !isset($restr['allow']) || $restr['allow'];
}

function prthRestrictionGet($restrictions, $restriction=null, $default=null) {
    if (empty($restriction)) {
        return $restrictions;
    }

    if (!is_array($restriction)) {
        $restriction = explode('.', $restriction);
    }

    $arr = $restrictions;
    $len = count($restriction);
    for ($i = 0; $i < $len-1; $i++) {
        if (!isset($arr[$restriction[$i]])) return $default;
        $arr = &$arr[$restriction[$i]];
    }

    return isset($arr[$restriction[$len-1]]) ? $arr[$restriction[$len-1]] : $default;
}

add_action( 'the_post', 'prthFillPostRestrictions'); // called in the loop for each post to set extra post data
function prthFillPostRestrictions($post) {
    if (prthShouldLoad()) {
        $post->prthRestrictions = prthGetEditRestrictions($post);
    }
}

include( PRTH . 'admin.php');
include( PRTH . 'template-functions.php');
include( PRTH . 'controller.php');
include( PRTH . 'built-in-edit-boxes.php');
include( PRTH . 'lang/langjs.php');