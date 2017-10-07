<?php
defined( 'ABSPATH' ) or die( '' );


// Default create post types
add_filter('prth_create_post_types', 'prthDefaultCreatePostTypes');
function prthDefaultCreatePostTypes($arr) {
    $arr[] = array("menuLabel" => __("New Post", "prth"), "postType" => "post", "dialogTitle" => __("Create Post", "prth"));
    $arr[] = array("menuLabel" => __("New Page", "prth"), "postType" => "page", "dialogTitle" => __("Create Page", "prth"));
    return $arr;
}


// Statistics
add_filter("prth_edit_boxes", "prthWpStatisticsEditBox", 5, 2);
function prthWpStatisticsEditBox($arr, $restr) {
    if (function_exists("wp_statistics_pages") && prthRestrictionAllowed($restr, 'custom.wp_statistics')) {
        $hits = wp_statistics_pages("total", get_permalink(), get_the_ID());
        $arr["hits"] = array(
            "title" => __("Page Views", "prth"),
            "boxes" => array("hitsval" => array(
                "html" => "<span class='prthHits'>$hits</span>"
            ))
        );
    }
    return $arr;
}

// title
add_filter("prth_edit_boxes", "prthTitleEditBoxes", 10, 2);
function prthTitleEditBoxes($arr, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_title')) {
        return $arr;
    }

    $arr["title"] = array(
        "title" => __("Title"),
        "boxes" => array("titleval" => array(
            "html" => '<input type="text" value="'.esc_attr(get_post_field('post_title')).'"/>',
            "validateJs" => 'prth.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("prth_add_boxes", "prthTitleAddBoxes", 10, 2);
function prthTitleAddBoxes($arr, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_title')) {
        return $arr;
    }

    $arr["title"] = array(
        "title" => __("Title"),
        "boxes" => array("titleval" => array(
            "html" => '<input type="text" placeholder="'.__('The Title', 'prth').'"/>',
            "validateJs" => 'prth.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("prth_edit_post", "prthTitleEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthTitleEdit", 10, 3);
function prthTitleEdit($result, $boxData, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_title')) {
        return $result;
    }

    if (!empty($boxData['title']) && !empty($boxData['title']['titleval']) ) {
        $result["post_data"]["post_title"] = $boxData['title']['titleval'];
    }
    return $result;
}

// slug
add_filter("prth_edit_boxes", "prthSlugEditBoxes", 10, 2);
function prthSlugEditBoxes($arr, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_name')) {
        return $arr;
    }

    $arr["slug"] = array(
        "title" => __("Slug"),
        "boxes" => array("slugval" => array(
            "html" => '<input type="text" value="'.esc_attr(get_post_field('post_name')).'" placeholder="'.__('the-slug', 'prth').'" />',
            "validateJs" => 'prth.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("prth_add_boxes", "prthSlugAddBoxes", 10, 2);
function prthSlugAddBoxes($arr, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_name')) {
        return $arr;
    }

    $arr["slug"] = array(
        "title" => __("Slug"),
        "boxes" => array("slugval" => array(
            "html" => '<input type="text" placeholder="'.__('the-slug', 'prth').'"/>',
            "validateJs" => 'prth.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("prth_edit_post", "prthSlugEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthSlugEdit", 10, 3);
function prthSlugEdit($result, $boxData, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_name')) {
        return $result;
    }

    if (!empty($boxData['slug']) && !empty($boxData['slug']['slugval']) ) {
        $result["post_data"]["post_name"] = $boxData['slug']['slugval'];
    }
    return $result;
}

// category
add_filter("prth_edit_boxes", "prthCategoryEditBoxes", 10, 2);
function prthCategoryEditBoxes($arr, $restr) {
    $restr = prthRestrictionGet($restr, 'taxonomy.category');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    if (is_object_in_taxonomy(get_post_type(), 'category')) {
        $cats = wp_get_post_categories(get_the_ID());
        $html = wp_dropdown_categories(array('echo'=>false, 'taxonomy'=>'category',
            'hide_empty'=>false));
        $arr["category"] = array(
            "title" => __("Categories"),
            "boxes" => array("categoryval" => array(
                "html" => $html,
                "setupJs" => "(function() {"
                    ."jQuery(this).attr('multiple', 'multiple')"
                    .".attr('data-placeholder','Select Categories...').css('min-width','15em').val(".esc_attr(json_encode($cats)).").chosen();"
                    ."})"
            ))
        );
    }

    return $arr;
}

add_filter("prth_add_boxes", "prthCategoryAddBoxes", 10, 4);
function prthCategoryAddBoxes($arr, $restr, $tag, $post_type) {
    $restr = prthRestrictionGet($restr, 'taxonomy.category');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    if (is_object_in_taxonomy($post_type, 'category')) {
        $html = wp_dropdown_categories(array('echo'=>false, 'taxonomy'=>'category',
            'hide_empty'=>false));
        $arr["category"] = array(
            "title" => __("Categories"),
            "boxes" => array("categoryval" => array(
                "html" => $html,
                "setupJs" => "(function() {"
                    ."jQuery(this).attr('multiple', 'multiple')"
                    .".attr('data-placeholder','Select Categories...').css('min-width','15em').val([]).chosen();"
                    ."})"
            ))
        );
    }

    return $arr;
}

add_filter("prth_edit_post", "prthCategoryEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthCategoryEdit", 10, 3);
function prthCategoryEdit($result, $boxData, $restr) {
    $restr = prthRestrictionGet($restr, 'taxonomy.category');

    if (!prthRestrictionAllowed($restr)) {
        return $result;
    }

    $forcedValue = prthRestrictionGet($restr, 'force_value');
    if (isset($forcedValue)) {
        $result["post_tax"]["category"] = is_array($forcedValue) ? $forcedValue : array($forcedValue);
        return $result;
    }

    if (!empty($boxData['category']) && is_array($boxData['category']['categoryval']) ) {
        $result["post_tax"]["category"] = array_map(intval, $boxData['category']['categoryval']);
    }
    return $result;
}


// post status
add_filter("prth_edit_boxes", "prthPostStatusEditBoxes", 10, 2);
function prthPostStatusEditBoxes($arr, $restr) {
    $restr = prthRestrictionGet($restr, 'post_status');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    $statuses = get_post_statuses();
    if (!get_post_status() == "publish" && !prthCanPublish()) {
        unset($statuses["publish"]);
    }
    $html = "<select>";
    foreach ($statuses as $name => $label) {
        $html .= '<option value="'.$name.'" '.selected(get_post_status(), $name, false).'>'.$label.'</option>';
    }
    $html .= '</select>';

    $arr["post_status"] = array(
        "title" => __("Status"),
        "boxes" => array("post_statusval" => array(
            "html" => $html
        ))
    );

    return $arr;
}

add_filter("prth_add_boxes", "prthPostStatusAddBoxes", 10, 4);
function prthPostStatusAddBoxes($arr, $restr, $tag, $postType) {
    $restr = prthRestrictionGet($restr, 'post_status');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    $statuses = get_post_statuses();
    if (!get_post_status() == "publish" && !prthCanPublishType($postType)) {
        unset($statuses["publish"]);
    }
    $html = "<select>";
    foreach ($statuses as $name => $label) {
        $html .= '<option value="'.$name.'">'.$label.'</option>';
    }
    $html .= '</select>';

    $arr["post_status"] = array(
        "title" => __("Status"),
        "boxes" => array("post_statusval" => array(
            "html" => $html
        ))
    );

    return $arr;
}

add_filter("prth_edit_post", "prthPostStatusEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthPostStatusEdit", 10, 3);
function prthPostStatusEdit($result, $boxData, $restr) {
    $restr = prthRestrictionGet($restr, 'post_status');

    if (!prthRestrictionAllowed($restr)) {
        return $result;
    }

    $forcedValue = prthRestrictionGet($restr, 'force_value');
    if (isset($forcedValue)) {
        $result["post_data"]["post_status"] = $forcedValue;
        return $result;
    }

    if (!empty($boxData['post_status']) && !empty($boxData['post_status']['post_statusval']) ) {
        if ($boxData['post_status']['post_statusval'] == "publish") {
            if (isset($result["post_data"]["ID"]) && !prthCanPublish() || !isset($result["post_data"]["ID"]) && !prthCanPublishType($result["post_data"]["post_type"])) {
                $result["warnigs"][] = __("You do not have permission to publish this post", "prth");
            }
        }
        $result["post_data"]["post_status"] = $boxData['post_status']['post_statusval'];
    }
    return $result;
}

// author
add_filter("prth_edit_boxes", "prthAuthorEditBoxes", 10, 3);
function prthAuthorEditBoxes($arr, $restr) {
    $restr = prthRestrictionGet($restr, 'post_author');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    $html = wp_dropdown_users(array("selected" => get_the_author_meta("ID"), "echo" => false, 'include_selected' => true));

    $arr["author"] = array(
        "title" => __("Author"),
        "boxes" => array("authorval" => array(
            "html" => $html
        ))
    );

    return $arr;
}

add_filter("prth_edit_post", "prthAuthorEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthAuthorEdit", 10, 3);
function prthAuthorEdit($result, $boxData, $restr) {
    $restr = prthRestrictionGet($restr, 'post_status');

    if (!prthRestrictionAllowed($restr)) {
        return $result;
    }

    if (!empty($boxData['author']) && !empty($boxData['author']['authorval']) ) {
        $result["post_data"]["post_author"] = $boxData['author']['authorval'];
    }
    return $result;
}

// parent page
add_filter("prth_edit_boxes", "prthParentPageEditBoxes", 10, 2);
function prthParentPageEditBoxes($arr, $restr) {
    $restr = prthRestrictionGet($restr, 'post_parent');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    if (get_post_type() == 'page') {
        $html = "<select>";
        $pages = get_pages(array("exclude_tree" => get_the_ID(), "exclude"=>array(get_the_ID())));
        $html .= '<option value="">None</option>';
        foreach ($pages as $page) {
            $html .= '<option value="'.$page->ID.'" '.selected(wp_get_post_parent_id(get_the_ID()), $page->ID, false).'>'.$page->post_title.'</option>';
        }
        $html .= '</select>';
        $arr["parentPage"] = array(
            "title" => __("Parent Page"),
            "boxes" => array("parentpageval" => array(
                "html" => $html
            ))
        );
    }

    return $arr;
}

add_filter("prth_add_boxes", "prthParentPageAddBoxes", 10, 4);
function prthParentPageAddBoxes($arr, $post_type, $tag, $restr) {
    $restr = prthRestrictionGet($restr, 'post_parent');

    if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    if ($post_type == 'page') {
        $html = "<select>";
        $pages = get_pages();
        $html .= '<option value="">None</option>';
        foreach ($pages as $page) {
            $html .= '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
        }
        $html .= '</select>';
        $arr["parentPage"] = array(
            "title" => __("Parent Page", "prth"),
            "boxes" => array("parentpageval" => array(
                "html" => $html
            ))
        );
    }

    return $arr;
}

add_filter("prth_edit_post", "prthParentPageEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthParentPageEdit", 10, 3);
function prthParentPageEdit($result, $boxData, $restr) {
    $restr = prthRestrictionGet($restr, 'post_parent');

    if (!prthRestrictionAllowed($restr)) {
        return $result;
    }

    if (!empty($boxData['parentPage']) && isset($boxData['parentPage']['parentpageval']) ) {
        if ($boxData['parentPage']['parentpageval']) {
            $result["post_data"]["post_parent"] = $boxData['parentPage']['parentpageval'];
        } else {
            $result["post_data"]["post_parent"] = null;
        }
    }
    return $result;
}


// content
//add_filter("prth_edit_boxes", "prthContentEditBoxes", 10, 2);
//function prthContentEditBoxes($arr, $restr) {
//    if (!prthRestrictionAllowed($restr, 'post_content')) {
//        return $arr;
//    }
//
//    $arr["content"] = array(
//        "content" => __("Content"),
//        "boxes" => array("contentval" => array(
//            "html" => '<input type="text" value="'.esc_attr(get_post_field('post_title')).'"/>',
//            "validateJs" => '(function() {return !this.value.match(/^\s*$/);})'
//        ))
//    );
//    return $arr;
//}

add_filter("prth_add_boxes", "prthContentAddBoxes", 50, 2);
function prthContentAddBoxes($arr, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_content')) {
        return $arr;
    }

    $arr["content"] = array(
        "title" => __("Content"),
        "boxes" => array("contentval" => array(
            "html" => '<textarea placeholder="'.__('Write some content. You will be able to change it later.', 'prth').'"/>',
            "validateJs" => 'prth.validateNotEmpty'
        ))
    );
    return $arr;
}

//add_filter("prth_edit_post", "prthContentEdit", 10, 3);
add_filter("prth_add_post_before_create", "prthContentEdit", 50, 3);
function prthContentEdit($result, $boxData, $restr) {
    if (!prthRestrictionAllowed($restr, 'post_content')) {
        return $result;
    }

    if (!empty($boxData['content']) && !empty($boxData['content']['contentval']) ) {
        $result["post_data"]["post_content"] = $boxData['content']['contentval'];
    }
    return $result;
}


// seo ultimate
function prthSeoUltimateHtml() {
    $id = str_replace(".", "", "id".microtime(true)."-".random_int(0, 1000));
    ob_start();
    ?>
    <div class="prthTabs">
        <ul>
            <li><a href="#search-eng-<?php echo $id;?>"><?php esc_html_e("Search Engine Settings", "prth"); ?></a></li>
            <li><a href="#social-med-<?php echo $id;?>"><?php esc_html_e("Social Media Settings", "prth"); ?></a></li>
        </ul>
        <div id="search-eng-<?php echo $id;?>" >
            <label><?php esc_html_e("Search Engine Title", "prth"); ?></label>
            <input vf-model="atom:_su_title" type="text" title="<?php esc_attr_e("Search Engine Title", "prth"); ?>"
                   style="width:100%; margin-bottom:0.5em;">
            <label><?php esc_html_e("Search Engine Description", "prth"); ?></label>
            <textarea vf-model="atom:_su_description" title="<?php esc_attr_e("Search Engine Description", "prth"); ?>" style="width:100%"></textarea>
        </div>
        <div id="social-med-<?php echo $id;?>" >
            <label><?php esc_html_e("Social Media Title", "prth"); ?></label>
            <input vf-model="atom:_su_og_title" type="text" title="<?php esc_attr_e("Social Media Title", "prth"); ?>"
                   style="width:100%; margin-bottom:0.5em;">
            <label><?php esc_html_e("Social Media Description", "prth"); ?></label>
            <textarea vf-model="atom:_su_og_description" title="<?php esc_attr_e("Social Media Description", "prth"); ?>" style="width:100%; margin-bottom:0.5em;"></textarea>
            <label><?php esc_html_e("Social Media Image", "prth"); ?></label>
            <div>
                <input class="prthSocMediaImgInput" vf-model="atom:_su_og_image" type="url" title="<?php esc_attr_e("Social Media Image", "prth"); ?>"
                       style="width:20em;">
                <button
                    onclick="prth.openFileFrame(null, 'image', false, function (att) {jQuery('#social-med-<?php echo $id;?> .prthSocMediaImgInput').val(att.url);});"
                    type="button"><?php esc_html_e("Select Social Media Image", "prth"); ?></button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_filter("prth_edit_boxes", "prthSeoUltimateEditBoxes", 90, 2);
function prthSeoUltimateEditBoxes($arr, $restr) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( is_plugin_active( 'seo-ultimate/seo-ultimate.php' ) ) {
        $restr = prthRestrictionGet($restr, 'custom.seo_ultimate');

        if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
            return $arr;
        }

        $su_title = get_post_meta(get_the_ID(), "_su_title", true);
        $su_description = get_post_meta(get_the_ID(), "_su_description", true);
        $su_og_title = get_post_meta(get_the_ID(), "_su_og_title", true);
        $su_og_description = get_post_meta(get_the_ID(), "_su_og_description", true);
        $su_og_image = get_post_meta(get_the_ID(), "_su_og_image", true);
        $data = esc_attr(json_encode(array("_su_title" => $su_title, "_su_description" => $su_description,
            "_su_og_title" => $su_og_title, "_su_og_description" => $su_og_description, "_su_og_image" => $su_og_image)));

        $html = prthSeoUltimateHtml();

        $arr["seoUltimate"] = array(
            "title" => __("SEO Ultimate", "prth"),
            "boxes" => array("seoultimateval" => array(
                "html" => $html,
                "getValueJs" => "(function () {return vf.getModel(this);})",
                "setupJs" => "(function () {jQuery(this).tabs(); vf.setModel(this, $data);})",
                "validateJs" => "(function () {return this.querySelector('input[type=\"url\"]').checkValidity()})"
            ))
        );
    }

    return $arr;
}

add_filter("prth_add_boxes", "prthSeoUltimateAddBoxes", 90, 2);
function prthSeoUltimateAddBoxes($arr, $restr) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( is_plugin_active( 'seo-ultimate/seo-ultimate.php' ) ) {
        $restr = prthRestrictionGet($restr, 'custom.seo_ultimate');

        if (!prthRestrictionAllowed($restr) || !prthRestrictionGet($restr, 'show_ui', true)) {
            return $arr;
        }

        $html = prthSeoUltimateHtml();
        $arr["seoUltimate"] = array(
            "title" => __("SEO Ultimate", "prth"),
            "boxes" => array("seoultimateval" => array(
                "html" => $html,
                "getValueJs" => "(function () {return vf.getModel(this);})",
                "setupJs" => "(function () {jQuery(this).tabs();})",
                "validateJs" => "(function () {return this.querySelector('input[type=\"url\"]').checkValidity()})"
            ))
        );
    }

    return $arr;
}

add_filter("prth_edit_post", "prthSeoUltimateEdit", 90, 3);
add_filter("prth_add_post_before_create", "prthSeoUltimateEdit", 10, 3);
function prthSeoUltimateEdit($result, $boxData, $restr) {
    $restr = prthRestrictionGet($restr, 'custom.seo_ultimate');

    if (!prthRestrictionAllowed($restr)) {
        return $result;
    }

    if (prthRestrictionGet($restr, "from_post_data", false)) {
        $result["post_data"]["meta_input"]["_su_description"] = $result["post_data"]["meta_input"]["_su_og_description"]
            = isset($result["post_data"]["post_content"]) ? substr($result["post_data"]["meta_input"], 0, 160) : get_the_excerpt();
        $result["post_data"]["meta_input"]['_su_og_image'] = isset($result["post_data"]["meta_input"]['_thumbnail_id'])
            ?  wp_get_attachment_url($result["post_data"]["meta_input"]['_thumbnail_id'])
            : get_the_post_thumbnail_url();
        return $result;
    }

    if (!empty($boxData['seoUltimate']) && isset($boxData['seoUltimate']['seoultimateval']) ) {
        $data = $boxData['seoUltimate']['seoultimateval'];
        $allowedMeta = array("_su_title", "_su_description",
            "_su_og_title", "_su_og_description", "_su_og_image");
        foreach ($allowedMeta as $meta) {
            $result["post_data"]["meta_input"][$meta] = $data[$meta];
        }
    }
    return $result;
}

// show create confirm dialog
add_filter('prth_add_post_after_create', 'prthCreateConfirm', 10, 1);
function prthCreateConfirm($result) {
    $result['actionsJs'][] = 'prth.showCreatePostConfirm';
    return $result;
}
