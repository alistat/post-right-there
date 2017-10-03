<?php
defined( 'ABSPATH' ) or die( '' );


// Default create post types
add_filter('ipf_create_post_types', 'ipfDefaultCreatePostTypes');
function ipfDefaultCreatePostTypes($arr) {
    $arr[] = array("menuLabel" => __("New Post", "ipf"), "postType" => "post", "dialogTitle" => __("Create Post", "ipf"));
    $arr[] = array("menuLabel" => __("New Page", "ipf"), "postType" => "page", "dialogTitle" => __("Create Page", "ipf"));
    return $arr;
}


// Statistics
add_filter("ipf_edit_boxes", "ipfWpStatisticsEditBox", 5, 2);
function ipfWpStatisticsEditBox($arr, $restr) {
    if (function_exists("wp_statistics_pages") && ipfRestrictionAllowed($restr, 'custom.wp_statistics')) {
        $hits = wp_statistics_pages("total", get_permalink(), get_the_ID());
        $arr["hits"] = array(
            "title" => __("Page Views", "ipf"),
            "boxes" => array("hitsval" => array(
                "html" => "<span class='ipfHits'>$hits</span>"
            ))
        );
    }
    return $arr;
}

// title
add_filter("ipf_edit_boxes", "ipfTitleEditBoxes", 10, 2);
function ipfTitleEditBoxes($arr, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_title')) {
        return $arr;
    }

    $arr["title"] = array(
        "title" => __("Title"),
        "boxes" => array("titleval" => array(
            "html" => '<input type="text" value="'.esc_attr(get_post_field('post_title')).'"/>',
            "validateJs" => 'ipf.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("ipf_add_boxes", "ipfTitleAddBoxes", 10, 2);
function ipfTitleAddBoxes($arr, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_title')) {
        return $arr;
    }

    $arr["title"] = array(
        "title" => __("Title"),
        "boxes" => array("titleval" => array(
            "html" => '<input type="text" placeholder="'.__('The Title', 'ipf').'"/>',
            "validateJs" => 'ipf.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("ipf_edit_post", "ipfTitleEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfTitleEdit", 10, 3);
function ipfTitleEdit($result, $boxData, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_title')) {
        return $result;
    }

    if (!empty($boxData['title']) && !empty($boxData['title']['titleval']) ) {
        $result["post_data"]["post_title"] = $boxData['title']['titleval'];
    }
    return $result;
}

// slug
add_filter("ipf_edit_boxes", "ipfSlugEditBoxes", 10, 2);
function ipfSlugEditBoxes($arr, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_name')) {
        return $arr;
    }

    $arr["slug"] = array(
        "title" => __("Slug"),
        "boxes" => array("slugval" => array(
            "html" => '<input type="text" value="'.esc_attr(get_post_field('post_name')).'" placeholder="'.__('the-slug', 'ipf').'" />',
            "validateJs" => 'ipf.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("ipf_add_boxes", "ipfSlugAddBoxes", 10, 2);
function ipfSlugAddBoxes($arr, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_name')) {
        return $arr;
    }

    $arr["slug"] = array(
        "title" => __("Slug"),
        "boxes" => array("slugval" => array(
            "html" => '<input type="text" placeholder="'.__('the-slug', 'ipf').'"/>',
            "validateJs" => 'ipf.validateNotEmpty'
        ))
    );
    return $arr;
}

add_filter("ipf_edit_post", "ipfSlugEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfSlugEdit", 10, 3);
function ipfSlugEdit($result, $boxData, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_name')) {
        return $result;
    }

    if (!empty($boxData['slug']) && !empty($boxData['slug']['slugval']) ) {
        $result["post_data"]["post_name"] = $boxData['slug']['slugval'];
    }
    return $result;
}

// category
add_filter("ipf_edit_boxes", "ipfCategoryEditBoxes", 10, 2);
function ipfCategoryEditBoxes($arr, $restr) {
    $restr = ipfRestrictionGet($restr, 'taxonomy.category');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
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

add_filter("ipf_add_boxes", "ipfCategoryAddBoxes", 10, 4);
function ipfCategoryAddBoxes($arr, $restr, $tag, $post_type) {
    $restr = ipfRestrictionGet($restr, 'taxonomy.category');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
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

add_filter("ipf_edit_post", "ipfCategoryEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfCategoryEdit", 10, 3);
function ipfCategoryEdit($result, $boxData, $restr) {
    $restr = ipfRestrictionGet($restr, 'taxonomy.category');

    if (!ipfRestrictionAllowed($restr)) {
        return $result;
    }

    $forcedValue = ipfRestrictionGet($restr, 'force_value');
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
add_filter("ipf_edit_boxes", "ipfPostStatusEditBoxes", 10, 2);
function ipfPostStatusEditBoxes($arr, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_status');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    $statuses = get_post_statuses();
    if (!get_post_status() == "publish" && !ipfCanPublish()) {
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

add_filter("ipf_add_boxes", "ipfPostStatusAddBoxes", 10, 4);
function ipfPostStatusAddBoxes($arr, $restr, $tag, $postType) {
    $restr = ipfRestrictionGet($restr, 'post_status');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
        return $arr;
    }

    $statuses = get_post_statuses();
    if (!get_post_status() == "publish" && !ipfCanPublishType($postType)) {
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

add_filter("ipf_edit_post", "ipfPostStatusEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfPostStatusEdit", 10, 3);
function ipfPostStatusEdit($result, $boxData, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_status');

    if (!ipfRestrictionAllowed($restr)) {
        return $result;
    }

    $forcedValue = ipfRestrictionGet($restr, 'force_value');
    if (isset($forcedValue)) {
        $result["post_data"]["post_status"] = $forcedValue;
        return $result;
    }

    if (!empty($boxData['post_status']) && !empty($boxData['post_status']['post_statusval']) ) {
        if ($boxData['post_status']['post_statusval'] == "publish") {
            if (isset($result["post_data"]["ID"]) && !ipfCanPublish() || !isset($result["post_data"]["ID"]) && !ipfCanPublishType($result["post_data"]["post_type"])) {
                $result["warnigs"][] = __("You do not have permission to publish this post", "ipf");
            }
        }
        $result["post_data"]["post_status"] = $boxData['post_status']['post_statusval'];
    }
    return $result;
}

// author
add_filter("ipf_edit_boxes", "ipfAuthorEditBoxes", 10, 3);
function ipfAuthorEditBoxes($arr, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_author');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
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

add_filter("ipf_edit_post", "ipfAuthorEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfAuthorEdit", 10, 3);
function ipfAuthorEdit($result, $boxData, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_status');

    if (!ipfRestrictionAllowed($restr)) {
        return $result;
    }

    if (!empty($boxData['author']) && !empty($boxData['author']['authorval']) ) {
        $result["post_data"]["post_author"] = $boxData['author']['authorval'];
    }
    return $result;
}

// parent page
add_filter("ipf_edit_boxes", "ipfParentPageEditBoxes", 10, 2);
function ipfParentPageEditBoxes($arr, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_parent');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
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

add_filter("ipf_add_boxes", "ipfParentPageAddBoxes", 10, 4);
function ipfParentPageAddBoxes($arr, $post_type, $tag, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_parent');

    if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
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
            "title" => __("Parent Page", "ipf"),
            "boxes" => array("parentpageval" => array(
                "html" => $html
            ))
        );
    }

    return $arr;
}

add_filter("ipf_edit_post", "ipfParentPageEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfParentPageEdit", 10, 3);
function ipfParentPageEdit($result, $boxData, $restr) {
    $restr = ipfRestrictionGet($restr, 'post_parent');

    if (!ipfRestrictionAllowed($restr)) {
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
//add_filter("ipf_edit_boxes", "ipfContentEditBoxes", 10, 2);
//function ipfContentEditBoxes($arr, $restr) {
//    if (!ipfRestrictionAllowed($restr, 'post_content')) {
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

add_filter("ipf_add_boxes", "ipfContentAddBoxes", 50, 2);
function ipfContentAddBoxes($arr, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_content')) {
        return $arr;
    }

    $arr["content"] = array(
        "title" => __("Content"),
        "boxes" => array("contentval" => array(
            "html" => '<textarea placeholder="'.__('Write some content. You will be able to change it later.', 'ipf').'"/>',
            "validateJs" => 'ipf.validateNotEmpty'
        ))
    );
    return $arr;
}

//add_filter("ipf_edit_post", "ipfContentEdit", 10, 3);
add_filter("ipf_add_post_before_create", "ipfContentEdit", 50, 3);
function ipfContentEdit($result, $boxData, $restr) {
    if (!ipfRestrictionAllowed($restr, 'post_content')) {
        return $result;
    }

    if (!empty($boxData['content']) && !empty($boxData['content']['contentval']) ) {
        $result["post_data"]["post_content"] = $boxData['content']['contentval'];
    }
    return $result;
}


// seo ultimate
function ipfSeoUltimateHtml() {
    $id = str_replace(".", "", "id".microtime(true)."-".random_int(0, 1000));
    ob_start();
    ?>
    <div class="ipfTabs">
        <ul>
            <li><a href="#search-eng-<?php echo $id;?>"><?php esc_html_e("Search Engine Settings", "ipf"); ?></a></li>
            <li><a href="#social-med-<?php echo $id;?>"><?php esc_html_e("Social Media Settings", "ipf"); ?></a></li>
        </ul>
        <div id="search-eng-<?php echo $id;?>" >
            <label><?php esc_html_e("Search Engine Title", "ipf"); ?></label>
            <input vf-model="atom:_su_title" type="text" title="<?php esc_attr_e("Search Engine Title", "ipf"); ?>"
                   style="width:100%; margin-bottom:0.5em;">
            <label><?php esc_html_e("Search Engine Description", "ipf"); ?></label>
            <textarea vf-model="atom:_su_description" title="<?php esc_attr_e("Search Engine Description", "ipf"); ?>" style="width:100%"></textarea>
        </div>
        <div id="social-med-<?php echo $id;?>" >
            <label><?php esc_html_e("Social Media Title", "ipf"); ?></label>
            <input vf-model="atom:_su_og_title" type="text" title="<?php esc_attr_e("Social Media Title", "ipf"); ?>"
                   style="width:100%; margin-bottom:0.5em;">
            <label><?php esc_html_e("Social Media Description", "ipf"); ?></label>
            <textarea vf-model="atom:_su_og_description" title="<?php esc_attr_e("Social Media Description", "ipf"); ?>" style="width:100%; margin-bottom:0.5em;"></textarea>
            <label><?php esc_html_e("Social Media Image", "ipf"); ?></label>
            <div>
                <input class="ipfSocMediaImgInput" vf-model="atom:_su_og_image" type="url" title="<?php esc_attr_e("Social Media Image", "ipf"); ?>"
                       style="width:20em;">
                <button
                    onclick="ipf.openFileFrame(null, 'image', false, function (att) {jQuery('#social-med-<?php echo $id;?> .ipfSocMediaImgInput').val(att.url);});"
                    type="button"><?php esc_html_e("Select Social Media Image", "ipf"); ?></button>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_filter("ipf_edit_boxes", "ipfSeoUltimateEditBoxes", 90, 2);
function ipfSeoUltimateEditBoxes($arr, $restr) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( is_plugin_active( 'seo-ultimate/seo-ultimate.php' ) ) {
        $restr = ipfRestrictionGet($restr, 'custom.seo_ultimate');

        if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
            return $arr;
        }

        $su_title = get_post_meta(get_the_ID(), "_su_title", true);
        $su_description = get_post_meta(get_the_ID(), "_su_description", true);
        $su_og_title = get_post_meta(get_the_ID(), "_su_og_title", true);
        $su_og_description = get_post_meta(get_the_ID(), "_su_og_description", true);
        $su_og_image = get_post_meta(get_the_ID(), "_su_og_image", true);
        $data = esc_attr(json_encode(array("_su_title" => $su_title, "_su_description" => $su_description,
            "_su_og_title" => $su_og_title, "_su_og_description" => $su_og_description, "_su_og_image" => $su_og_image)));

        $html = ipfSeoUltimateHtml();

        $arr["seoUltimate"] = array(
            "title" => __("SEO Ultimate", "ipf"),
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

add_filter("ipf_add_boxes", "ipfSeoUltimateAddBoxes", 90, 2);
function ipfSeoUltimateAddBoxes($arr, $restr) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if ( is_plugin_active( 'seo-ultimate/seo-ultimate.php' ) ) {
        $restr = ipfRestrictionGet($restr, 'custom.seo_ultimate');

        if (!ipfRestrictionAllowed($restr) || !ipfRestrictionGet($restr, 'show_ui', true)) {
            return $arr;
        }

        $html = ipfSeoUltimateHtml();
        $arr["seoUltimate"] = array(
            "title" => __("SEO Ultimate", "ipf"),
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

add_filter("ipf_edit_post", "ipfSeoUltimateEdit", 90, 3);
add_filter("ipf_add_post_before_create", "ipfSeoUltimateEdit", 10, 3);
function ipfSeoUltimateEdit($result, $boxData, $restr) {
    $restr = ipfRestrictionGet($restr, 'custom.seo_ultimate');

    if (!ipfRestrictionAllowed($restr)) {
        return $result;
    }

    if (ipfRestrictionGet($restr, "from_post_data", false)) {
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
add_filter('ipf_add_post_after_create', 'ipfCreateConfirm', 10, 1);
function ipfCreateConfirm($result) {
    $result['actionsJs'][] = 'ipf.showCreatePostConfirm';
    return $result;
}
