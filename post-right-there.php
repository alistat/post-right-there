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

define('IPF', plugin_dir_path(__FILE__));

add_action( 'init', 'ipfLoadTextdomain' );
function ipfLoadTextdomain() {
    load_plugin_textdomain( 'ipf', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}


// ---------- Enqueue the stuff ----------
add_action( 'wp_enqueue_scripts', 'ipfEnqueueScripts' );
function ipfEnqueueScripts() {
    if (ipfShouldLoad()) {
        wp_enqueue_script('ipfData', plugins_url('/js/data.js', __FILE__), array('jquery'), "0.4.1", true);
        wp_enqueue_script('ipfEditor', plugins_url('/js/editor.js', __FILE__), array('jquery'), "0.4.1", true);
        wp_enqueue_script('ipf', plugins_url('/js/ipf.js', __FILE__), array('jquery', 'ipfEditor'), "0.4.1", true);
        wp_enqueue_style('ipf-css', plugins_url('/css/ipf.css', __FILE__), array(), "0.4.1");

        // vf
        wp_enqueue_script( 'vf', plugins_url( '/js/lib/vf.js', __FILE__ ), array( 'jquery' ), null, true);

        // jquery
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('jquery-ui-base', "//code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css");

        // tinymce editor
//        wp_enqueue_script('tinymce', "//cdn.tinymce.com/4/tinymce.min.js", array(), null, true);

        // codemirror
        wp_enqueue_script('codemirror', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/codemirror.min.js", array(), "5.19.0", true);
        wp_enqueue_style('codemirror-style', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/codemirror.min.css");
        wp_enqueue_script('codemirror-htmlmixed', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/mode/htmlmixed/htmlmixed.min.js", array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-javascript', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/mode/javascript/javascript.min.js", array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-css', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/mode/css/css.min.js", array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-xml', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/mode/xml/xml.min.js", array('codemirror'), "5.19.0", true);
//        wp_enqueue_script('codemirror-dialog', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/addon/dialog/dialog.min.js", array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-matchbrackets', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/addon/edit/matchbrackets.min.js", array('codemirror'), "5.19.0", true);
        wp_enqueue_script('codemirror-active-line', "//cdnjs.cloudflare.com/ajax/libs/codemirror/5.19.0/addon/selection/active-line.min.js", array('codemirror'), "5.19.0", true);

        wp_enqueue_media();

        // chosen plugin for multiselect dropdowns
        wp_enqueue_script('chosen.jquery', plugins_url('/js/lib/chosen.jquery.min.js', __FILE__), array('jquery'), null, true);
        wp_enqueue_style('chosen-style', plugins_url('/css/lib/chosen.min.css', __FILE__));

        // toastr for notifications
        wp_enqueue_script('toastr', "//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js", array('jquery', 'ipf'), "2.1.3", true);
        wp_enqueue_style('toastr-style', "//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css", array(), "2.1.3");

        $lang = ipfLangJs();
        wp_localize_script('ipf', "ipfLang", $lang);
        wp_localize_script('ipfEditor', "ipfLang", $lang);
    }
}

function ipfSettingsEnqueue($hook) {
    if ( 'settings_page_ipf_settings' != $hook ) {
        return;
    }

    wp_enqueue_script( 'vf', plugins_url( '/js/lib/vf.js', __FILE__ ), array( 'jquery' ), null, false);
    wp_enqueue_script('chosen.jquery', plugins_url('/js/lib/chosen.jquery.min.js', __FILE__), array('jquery'), null, false);
    wp_enqueue_style('chosen-style', plugins_url('/css/lib/chosen.min.css', __FILE__));
}
add_action( 'admin_enqueue_scripts', 'ipfSettingsEnqueue' );


function ipfGetSettings() {
    return get_option("ipf_settings", array("postTypes" => array()));
}

function ipfSetSettings($settings) {
    update_option('ipf_settings', $settings);
}


function ipfShouldLoad() {
    return apply_filters("ipf_should_load", is_user_logged_in() && ((current_user_can('edit_posts') || current_user_can('edit_pages')
            || current_user_can('edit_others_posts')) || current_user_can('edit_others_pages')));
    // ya nevah know what 's goin' on
}


function ipfGetEditRestrictions($post = null, $tag=null) {
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

    return apply_filters("ipf_get_edit_restrictions", $restrictions, $postId, $tag);
}


function ipfCanEdit($post = null, $tag = null) {
    $restrictions = ipfGetEditRestrictions($post, $tag);
    return $restrictions['allow'];
}

function ipfNoEditExcludedTypes($restrictions, $postId) {
    if (!$restrictions['allow']) return $restrictions;
    $settings = ipfGetSettings();
    $restrictions['allow'] = !in_array(get_post_type($postId), $settings["postTypes"]);
    return $restrictions;
}
add_filter("ipf_get_edit_restrictions", "ipfNoEditExcludedTypes", 10, 2);

function ipfCanPublish($post = null, $tag = null) {
    $restrictions = ipfGetEditRestrictions($post, $tag);
    return empty($restrictions['post_status'])
        || ($restrictions['post_status']['allow']
            && (empty($restrictions['post_status']['values']) || in_array("publish", $restrictions['post_status']['values'])) );
}

function ipfGetCreateRestrictions($postType, $tag) {
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

    return apply_filters("ipf_get_create_restrictions", $restrictions, $postType, $tag);
}

function ipfCanCreateType($postType, $tag=null) {
    $restrictions = ipfGetCreateRestrictions($postType, $tag);
    return $restrictions['allow'];
}

function ipfCanPublishType($postType, $tag=null) {
    $restrictions = ipfGetCreateRestrictions($postType, $tag);
    return empty($restrictions['post_status'])
    || ($restrictions['post_status']['allow']
        && (empty($restrictions['post_status']['values']) || in_array("publish", $restrictions['post_status']['values'])) );
}

function ipfCanDeleteAttachment($attachmentId) {
    if (!is_numeric($attachmentId)) {
        return false;
    }
    return apply_filters('ipf_can_delete_attachment', current_user_can('delete_post', $attachmentId), $attachmentId);
}

function ipfCanChangeSettings() {
    return apply_filters("ipf_can_change_options", current_user_can("manage_options"));
}

function ipfRestrictionAllowed($restrictions, $restriction=null) {
    $restr = ipfRestrictionGet($restrictions, $restriction);
    return !isset($restr['allow']) || $restr['allow'];
}

function ipfRestrictionGet($restrictions, $restriction=null, $default=null) {
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

add_action( 'the_post', 'ipfFillPostRestrictions'); // called in the loop for each post to set extra post data
function ipfFillPostRestrictions($post) {
    if (ipfShouldLoad()) {
        $post->ipfRestrictions = ipfGetEditRestrictions($post);
    }
}

include( IPF . 'admin.php');
include( IPF . 'template-functions.php');
include( IPF . 'controller.php');
include( IPF . 'built-in-edit-boxes.php');
include( IPF . 'lang/langjs.php');