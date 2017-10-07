<?php
defined( 'ABSPATH' ) or die( '' );


function prthSendResponse($resp) {
    http_response_code($resp['status']);
    if (isset($resp['error'])) {
        wp_send_json(array('error' => $resp['error']));
    } else if (isset($resp['content'])) {
        wp_send_json($resp['content']);
    } else {
        wp_send_json(0);
    }
}

function prthEnsureEditablePostIdHtml($post_id, $tag=null) {
    if (empty($post_id) || !is_numeric($post_id)) {
        http_response_code(400);
        echo '<span class="error">'.sprintf(__("Unrecognizable post id %s. Please contact support.", "prth"), $post_id).'</span>';
        wp_die();
    }
    if (!get_post($post_id)) {
        http_response_code(404);
        echo '<span class="error">'.__("Post not found.", "prth").'</span>';
        wp_die();
    }
    $restr = prthGetEditRestrictions($post_id, $tag);
    if (!prthRestrictionAllowed($restr)) {
        http_response_code(403);
        echo '<span class="error">'.__("You are not allowed to edit this post.", "prth").'</span>';
        wp_die();
    }
    return $restr;
}

function prthEnsureJsonHtml($string) {
    $data = json_decode($string, true);
    if ($data === null) {
        http_response_code(400);
        echo '<span class="error">'.__("Invalid  json. Please contact support.", "prth").'</span>'
           .'<br/><code>'.esc_html($string).'</code>';
        wp_die();
    }
    return $data;
}

function prthEnsureJson($string) {
    $data = json_decode($string, true);
    if ($data === null) {
        prthSendResponse(array("status" => 400, "error" => sprintf(__("Invalid  json.  Please contact support. %s"), $string)));
    }
    return $data;
}


// CONTENT
add_action( 'wp_ajax_prth_get_the_content', 'prth_get_the_content' );
function prth_get_the_content() {
    $post_id = $_GET['post'];
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post_id, $tag);

    if (!prthRestrictionAllowed($restr, "post_content")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }

    $content = get_post_field('post_content', $post_id, 'edit');
    if ( has_filter( 'the_content', 'wpautop' )) {
        $content = shortcode_unautop(wpautop($content));
    }
    prthSendResponse(array('content' => $content, 'status' => 200));
}

add_action( 'wp_ajax_prth_set_the_content', 'prth_set_the_content' );
function prth_set_the_content() {
    $post = json_decode(stripslashes($_POST['post']), true);
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post['ID'], $tag);

    if (!prthRestrictionAllowed($restr, "post_content")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }

    $post_id = wp_update_post($post);
    if (is_wp_error($post_id)) {
        prthSendResponse(array('status' => 500, 'error' => implode(", ", $post_id->get_error_messages())));
    }
    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    $content = '';
    while ( have_posts() ) : the_post();
        $content = apply_filters('the_content', get_the_content());;
    endwhile;
    wp_reset_query();
    prthSendResponse(array('status'=>200, 'content'=>$content));
}


// TITLE
add_action( 'wp_ajax_prth_get_the_title', 'prth_get_the_title' );
function prth_get_the_title() {
    $post_id = $_GET['post'];
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post_id, $tag);

    if (!prthRestrictionAllowed($restr, "post_title")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }
    $title = get_post_field('post_title', $post_id, 'edit');
    if ( has_filter( 'the_title', 'trim' )) {
        $title = trim($title);
    }
    prthSendResponse(array('content' => $title, 'status' => 200));
}

add_action( 'wp_ajax_prth_set_the_title', 'prth_set_the_title' );
function prth_set_the_title() {
    $post = json_decode(stripslashes($_POST['post']), true);
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post['ID'], $tag);

    if (!prthRestrictionAllowed($restr, "post_title")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }
    $post_id = wp_update_post($post);
    if (is_wp_error($post_id)) {
        prthSendResponse(array('status' => 500, 'error' => implode(", ", $post_id->get_error_messages())));
    }
    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    $title = '';
    while ( have_posts() ) : the_post();
        $title = get_the_title();
    endwhile;
    wp_reset_query();
    prthSendResponse(array('status'=>200, 'content'=>$title));
}


// EXCERPT
add_action( 'wp_ajax_prth_get_the_excerpt', 'prth_get_the_excerpt' );
function prth_get_the_excerpt() {
    $post_id = $_GET['post'];
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post_id, $tag);

    if (!prthRestrictionAllowed($restr, "post_excerpt")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }

    $excerpt = '';
    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    while ( have_posts() ) : the_post();
        $excerpt = get_the_excerpt();
    endwhile;
    wp_reset_query();
    if ( has_filter( 'the_excerpt', 'wpautop' )) {
        $excerpt = shortcode_unautop(wpautop($excerpt));
    }
    prthSendResponse(array('content' => $excerpt, 'status' => 200));
}

add_action( 'wp_ajax_prth_set_the_excerpt', 'prth_set_the_excerpt' );
function prth_set_the_excerpt() {
    $post = json_decode(stripslashes($_POST['post']), true);
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post['ID'], $tag);

    if (!prthRestrictionAllowed($restr, "post_excerpt")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }
    $post_id = wp_update_post($post);
    if (is_wp_error($post_id)) {
        prthSendResponse(array('status' => 500, 'error' => implode(", ", $post_id->get_error_messages())));
    }
    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    $excerpt = '';
    while ( have_posts() ) : the_post();
        $excerpt = get_the_excerpt();
    endwhile;
    wp_reset_query();
    prthSendResponse(array('status'=>200, 'content'=>$excerpt));
}


// THUMBNAIL
add_action( 'wp_ajax_prth_set_the_thumbnail', 'prth_set_the_thumbnail' );
function prth_set_the_thumbnail() {
    $post = json_decode(stripslashes($_POST['post']), true);
    $tag = stripslashes($_GET['tag']);
    if (empty($post['ID']) || empty($post['thumb']) || !is_numeric($post['ID']) || !is_numeric($post['thumb'])) {
        prthSendResponse(array('status' => 400, 'error' => __('No post ID or thumb id. Please contact support.', "prth")));
    }

    $restr = prthGetEditRestrictions($post['ID'], $tag);
    if (!prthRestrictionAllowed($restr, "thumbnail")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }
    if (set_post_thumbnail($post['ID'], $post['thumb'])) {
        prthSendResponse(array('status'=>200, 'content'=>wp_get_attachment_image($post['thumb'], 'post-thumbnail')));
    } else {
        prthSendResponse(array('status' => 500, 'error' => __("Could not set post thumbnail. Please contact support.", "prth")));
    }
}

add_action( 'wp_ajax_prth_remove_the_thumbnail', 'prth_remove_the_thumbnail' );
function prth_remove_the_thumbnail() {
    $post_id = $_GET['post'];
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetEditRestrictions($post_id, $tag);

    if (!prthRestrictionAllowed($restr, "thumbnail")) {
        prthSendResponse(array('status' => 403, 'error' => __('Not Allowed', 'prth')));
    }
    if ($_GET['remove'] == "true") {
        $thumbId = get_post_meta($post_id, '_thumbnail_id', true);
        if (!prthCanDeleteAttachment($thumbId)) {
            prthSendResponse(array('status' => 403, 'error' => __("You do not have permission to delete this image completely.", "prth")));
        }
        $result = wp_delete_attachment($thumbId);
    } else {
        $result = delete_post_thumbnail($post_id);
    }
    if ($result) {
        prthSendResponse(array('content' => "ok", 'status' => 200));
    } else {
        prthSendResponse(array('error' => __("Could remove thumbnail", "prth"), 'status' => 500));
    }
}

add_action( 'wp_ajax_prth_can_delete_the_thumbnail', 'prth_can_delete_the_thumbnail' );
function prth_can_delete_the_thumbnail() {
    $post_id = $_GET['post'];
    if (!is_numeric($post_id)) {
        prthSendResponse(array('status' => 400, 'error' => __('Numeric value expected for post. Please contact support.', "prth")));
    }
    $thumbId = get_post_meta($post_id, '_thumbnail_id', true);
    if (!is_numeric($thumbId)) {
        prthSendResponse(array('status' => 200, 'content' => true));
    }
    prthSendResponse(array('status' => 200, 'content' => prthCanDeleteAttachment($thumbId)));
}


// ATTACHMENT
add_action( 'wp_ajax_prth_get_attachment_image', 'prth_get_attachment_image' );
function prth_get_attachment_image() {
    $image = wp_get_attachment_image($_GET['attachment'], 'large');
    if (empty($image)) {
        prthSendResponse(array('status' => 404, 'error' => __('Attachment not found', "prth")));
    }
    prthSendResponse(array('content' => $image, 'status' => 200));
}

add_action( 'wp_ajax_prth_get_user_images', 'prth_get_user_images');
function prth_get_user_images() {
    $offset = is_numeric($_GET['offset']) ? intval($_GET['offset']) : 0;
    $perPage = is_numeric($_GET['perPage']) ? intval($_GET['perPage']) : 10;
    $args = array(
        'post_type'   => 'attachment',
        'author' => get_current_user_id(),
        'posts_per_page'   => $perPage,
        'offset'           => $offset,
//        'post_status' => 'any',
        'post_mime_type' => 'image'
    );

    $attachments = get_posts( $args );
    $result = array();
    foreach ($attachments as $img) {
        $result[] = array(
            "ID" => $img->ID,
            "id" => $img->ID,
            "post_date" => $img->post_date,
            "post_title" => $img->post_title,
            "url" => wp_get_attachment_url($img->ID)
        );
    }

    prthSendResponse(array('content' => $result, 'status' => 200));
}



// POST SETTINGS
add_action( 'wp_ajax_prth_get_edit_boxes', 'prth_get_edit_boxes');
function prth_get_edit_boxes() {
    header('Content-Type: text/html');
    $tag = stripslashes($_GET['tag']);
    $post_id = $_GET['post'];
    $restr = prthEnsureEditablePostIdHtml($post_id, $tag);

    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    while ( have_posts() ) : the_post();
        prthRenderEditBoxes(apply_filters('prth_edit_boxes', array(), $restr, $tag));
    endwhile;
    wp_reset_query();
    wp_die();
}

add_action( 'wp_ajax_prth_get_add_boxes', 'prth_get_add_boxes');
function prth_get_add_boxes() {
    header('Content-Type: text/html');
    $post_type = $_GET['post_type'];
    $tag = stripslashes($_GET['tag']);
    $restr = prthGetCreateRestrictions($post_type, $tag);

    if (!$restr['allow']) {
        http_response_code(403);
        wp_die();
    }

    prthRenderEditBoxes(apply_filters('prth_add_boxes', array(), $restr, $tag, $post_type));
    wp_die();
}

add_action( 'wp_ajax_prth_edit_post', 'prth_edit_post');
function prth_edit_post() {
    header('Content-Type: text/html');
    $post_id = $_POST['post'];
    $restr = prthEnsureEditablePostIdHtml($_POST['post']);
    $tag = stripslashes($_GET['tag']);
    $boxData = prthEnsureJsonHtml(stripslashes($_POST['boxData']));

    $results =  array(
        "errors" => array(),
        "warnings" => array(),
        "post_data" => array("ID" => $post_id, "meta_input" => array()),
        "post_tax" => array());
    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    while ( have_posts() ) {
        the_post();
        $results = apply_filters('prth_edit_post', $results, $boxData, $restr, $tag);
    }
    wp_reset_query();

    wp_update_post($results["post_data"]);
    if (!empty($results["post_tax"])) {
        foreach ($results["post_tax"] as $tax => $terms) {
            wp_set_post_terms($post_id, $terms, $tax, false);
        }
    }

    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    while ( have_posts() ) {
        the_post();
        prthRenderEditBoxes(apply_filters('prth_edit_boxes', array(), $restr, $tag));
        $errors = $results["errors"];
        if (!empty($errors)) {
            echo '<div class="prthError">' . count($errors) . __(" errors were noticed ", "prth") . implode(", ", $errors) . '</div>';
        }
        $warnings = $results["warnings"];
        if (!empty($warnings)) {
            echo '<div class="prthWarning">' . count($warnings) . __(" warnings were noticed ", "prth") . implode(", ", $warnings) . '</div>';
        }
    }
    wp_reset_query();

    wp_die();
}


add_action( 'wp_ajax_prth_add_post', 'prth_add_post');
function prth_add_post() {
    $post_type = $_POST['post_type'];
    $tag = stripslashes($_POST['tag']);
    $restr = prthGetCreateRestrictions($post_type, $tag);

    if (!prthRestrictionAllowed($restr)) {
        prthSendResponse(array('status' => 403, 'error' => __('You do not have permission to create post of type ', 'prth').$post_type));
    }

    $boxData = prthEnsureJson(stripslashes($_POST['boxData']));
    $preCreate =  array(
        "errors" => array(),
        "warnings" => array(),
        "post_data" => array("meta_input" => array(), "post_type" => $post_type),
        "post_tax" => array(),
        'actionsJs' => array());
    $result = apply_filters('prth_add_post_before_create', $preCreate, $boxData, $restr, $tag, $post_type);

    $post_id = wp_insert_post($result["post_data"]);
    if (!empty($results["post_tax"])) {
        foreach ($results["post_tax"] as $tax => $terms) {
            wp_set_post_terms($post_id, $terms, $tax, false);
        }
    }

    query_posts(array('p' => $post_id, 'post_type' => 'any'));
    while ( have_posts() ) {
        the_post();
        $result["URL"] = get_permalink();
        $result = apply_filters('prth_add_post_after_create', $result, $boxData, $restr, $tag);
        $result["post_data"] = get_post();
    }
    wp_reset_query();

    prthSendResponse(array("status" => 200, "content" => $result));
}


// SETTINGS prth_save_settings

add_action( 'wp_ajax_prth_save_settings', 'prth_save_settings');
function prth_save_settings() {
    if (!prthCanChangeSettings()) {
        prthSendResponse(array('status' => 403, 'error' => __('You do not have permission to change settings', 'prth')));
    }
    prthSetSettings(json_decode(stripslashes($_POST['settings']), true));
    prthSendResponse(array("status" => 200, "content" => prthGetSettings()));
}

// -----------------------------------------------------------

if (!function_exists('http_response_code')) {
    function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue'; break;
                case 101: $text = 'Switching Protocols'; break;
                case 200: $text = 'OK'; break;
                case 201: $text = 'Created'; break;
                case 202: $text = 'Accepted'; break;
                case 203: $text = 'Non-Authoritative Information'; break;
                case 204: $text = 'No Content'; break;
                case 205: $text = 'Reset Content'; break;
                case 206: $text = 'Partial Content'; break;
                case 300: $text = 'Multiple Choices'; break;
                case 301: $text = 'Moved Permanently'; break;
                case 302: $text = 'Moved Temporarily'; break;
                case 303: $text = 'See Other'; break;
                case 304: $text = 'Not Modified'; break;
                case 305: $text = 'Use Proxy'; break;
                case 400: $text = 'Bad Request'; break;
                case 401: $text = 'Unauthorized'; break;
                case 402: $text = 'Payment Required'; break;
                case 403: $text = 'Forbidden'; break;
                case 404: $text = 'Not Found'; break;
                case 405: $text = 'Method Not Allowed'; break;
                case 406: $text = 'Not Acceptable'; break;
                case 407: $text = 'Proxy Authentication Required'; break;
                case 408: $text = 'Request Time-out'; break;
                case 409: $text = 'Conflict'; break;
                case 410: $text = 'Gone'; break;
                case 411: $text = 'Length Required'; break;
                case 412: $text = 'Precondition Failed'; break;
                case 413: $text = 'Request Entity Too Large'; break;
                case 414: $text = 'Request-URI Too Large'; break;
                case 415: $text = 'Unsupported Media Type'; break;
                case 422: $text = 'Unprocessable Entity'; break;
                case 500: $text = 'Internal Server Error'; break;
                case 501: $text = 'Not Implemented'; break;
                case 502: $text = 'Bad Gateway'; break;
                case 503: $text = 'Service Unavailable'; break;
                case 504: $text = 'Gateway Time-out'; break;
                case 505: $text = 'HTTP Version not supported'; break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);

            $GLOBALS['http_response_code'] = $code;

        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        }

        return $code;

    }
}