<?php
defined( 'ABSPATH' ) or die( '' );

add_action( 'wp_head', 'ipfHead' );
function ipfHead() {
    if (ipfShouldLoad()) {
        ?>
        <script>
            adminURL = "<?php echo admin_url('admin-ajax.php'); ?>";
            ipf = {};
            ipf.editablePosts = {};
            ipf.postRestrictions = {};
        </script>
        <?php
    }
}

function ipfPrintRestrictNavMenuSettings() {
    ?>
    <script type="text/javascript">
        jQuery( '#menu-to-edit').on( 'click', 'a.item-edit', function() {
            var settings  = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' );
            var css_class = settings.find( '.edit-menu-item-classes' );

            if( css_class.val().match("^ipf-") ) {
                css_class.attr( 'readonly', 'readonly' );
//                settings.find( '.field-url' ).css( 'display', 'none' );
                settings.find( '.field-url input' ).attr( 'readonly', 'readonly' );
            }
        });
    </script>
    <?php
}

add_filter( 'the_content', 'ipfMakeContentEditablable', 300);
function ipfMakeContentEditablable($content) {
    global $wp_current_filter;
    $restr = isset(get_post()->ipfRestrictions) ? get_post()->ipfRestrictions : ipfGetEditRestrictions();
    if (!in_array('get_the_excerpt', (array) $wp_current_filter)
        && !in_array('the_excerpt', (array) $wp_current_filter)
        && ipfRestrictionAllowed($restr)) {
        $id = get_the_ID();
        $editId = "edit-content-$id";
        ob_start();
        ?>
        <div class="editContentContainer editContainer" id="edit-container-<?php echo $id;?>">
            <?php if ( ipfRestrictionAllowed($restr, "post_content") ): ?>
                <button onclick='ipf.makeContentEditable(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfEdit"><?php esc_html_e("Edit Here", "ipf");?></button>
                <button onclick='ipf.saveContent(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfSave"><?php esc_html_e("Save", "ipf");?></button>
                <button onclick='ipf.cancelContentEdit(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfCancel"><?php esc_html_e("Cancel", "ipf");?></button>
            <?php endif; ?>
            <?php if ( ipfRestrictionAllowed($restr, "thumbnail") ): ?>
                <button class="ipfAddImage" title="<?php esc_attr_e("Add Image", "ipf");?>"> <?php esc_html_e("Add Image", "ipf");?></button>
            <?php endif;?>
            <?php if ( ipfRestrictionGet($restr, "showPostSettingsUi", true)): ?>
                <button onclick='ipf.showEditPostPopup(<?php echo $id;?>)' class="ipfProperties"><?php esc_html_e("Settings", "ipf");?></button>
            <?php endif;?>
        </div>
        <?php
        $editControls = ob_get_clean();
        $restrJson = json_encode($restr);
        $content = "<script>
                window.ipf.editablePosts['$id'] = true;
                window.ipf.postRestrictions['$id'] = $restrJson;
            </script>"
            . $editControls."<div id='$editId'>".$content.'</div>';
    }
    return $content;
}

add_action('tribe_events_before_the_content', 'ipfMarkEventEditable');
function ipfMarkEventEditable() {
    $id = get_the_ID();
    $restr = isset(get_post()->ipfRestrictions) ? get_post()->ipfRestrictions : ipfGetEditRestrictions();
    if (ipfRestrictionAllowed($restr)) {
        echo "<script>window.ipf.editablePosts['$id'] = true;</script>";
    }
}

add_filter( 'the_excerpt', 'ipfMakeExcerptEditablable', 300);
function ipfMakeExcerptEditablable($excerpt) {
    global $wp_current_filter;
    $restr = isset(get_post()->ipfRestrictions) ? get_post()->ipfRestrictions : ipfGetEditRestrictions();
    if (!in_array('get_the_excerpt', (array) $wp_current_filter)
        && ipfRestrictionAllowed($restr)) {
        $id = get_the_ID();
        $editId = "edit-excerpt-$id";
        ob_start();
        ?>
        <div class="editExcerptContainer editContainer" id="edit-excerpt-container-<?php echo $id;?>">
            <button onclick='ipf.makeExcerptEditable(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfEdit" title="<?php esc_attr_e("Edit Excerpt", "ipf");?>"></button>
            <button onclick='ipf.saveExcerpt(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfSave" title="<?php esc_attr_e("Save", "ipf");?>"></button>
            <button onclick='ipf.cancelExcerptEdit(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="ipfCancel" title="<?php esc_attr_e("Cancel", "ipf");?>"></button>
        </div>
        <div class="editExcerptDecor"></div>
        <?php
        $editControls = ob_get_clean();
        $excerpt = "<script>window.ipf.editablePosts['$id'] = true;</script>".'<div style="position:relative">'.$editControls
            ."<div id='$editId' class='excerptEditArea'>".$excerpt.'</div></div>';
    }
    return $excerpt;
}

add_filter( 'post_thumbnail_html', 'ipfEmptyImageHolder', 10, 3 );
function ipfEmptyImageHolder($html, $postId, $thumbId) {
    if (!$thumbId) {
        return '<div class="post-thumbnail featured-image"><img class="ipfNoImage" src=""></div>';
    }
    return $html;
}

function ipfRenderEditBoxes($editBoxGroups) {
    foreach ($editBoxGroups as $groupName => $group) {
        ?>
        <div class="ipfEditBoxGroup <?php echo $groupName;?>" data-setup="<?php echo esc_attr($group['setupJs']);?>" data-group-name="<?php echo esc_attr($groupName);?>">
            <h4 class="ipfEditBoxGroupHeader"><?php echo $group['title'];?></h4>
            <?php foreach ($group['boxes'] as $boxName => $box): ?>
                <div class="ipfEditBox <?php echo esc_attr($boxName);?>" data-setup="<?php echo esc_attr($box['setupJs']);?>"
                 data-get-value="<?php echo esc_attr($box['getValueJs']);?>" data-validate="<?php echo esc_attr($box['validateJs']);?>"
                 data-box-name="<?php echo esc_attr($boxName);?>" >
                    <?php echo $box['html'];?>
                 </div>
            <?php endforeach;?>
        </div>
        <?php
    }
}

add_action( 'wp_footer', 'ipfFoot' );
function ipfFoot() {
    if (ipfShouldLoad()) {
        ?>
        <div style="display: none;">
            <?php wp_editor('', "ipfDummyEditor"); ?>
        </div>
        <a style="display: none;" href="https://icons8.com">Icon pack by Icons8</a>

        <script>
            ipf.tinyMceExternalPlugins = <?php echo json_encode(apply_filters("mce_external_plugins", array())); ?>;
            ipf.tinyMceExtraButtons = <?php echo "'".implode(" ", apply_filters("mce_buttons", array()))."'"; ?>;
            window.ajaxurl = window.adminURL = "<?php echo admin_url('admin-ajax.php'); ?>";
            // support photo gallery plugin
            if (typeof bwg_admin_ajax === "undefined") {
                window.bwg_admin_ajax = adminURL+"?action=BWGShortcode";
                window.bwg_plugin_url = "<?php echo plugins_url('photo-gallery'); ?>";
            }
            ipf.authorUrl = "?post_type=any&author=<?php esc_attr_e(get_the_author_meta('login', get_current_user_id())); ?>";
            var myPostsNavs = document.getElementsByClassName('ipf-myPostsNav');
            for (var i = 0; i < myPostsNavs.length; i++) {
                myPostsNavs[i].querySelector('a').href = ipf.authorUrl;
            }

            var createNavs = document.getElementsByClassName('ipf-createNav');
            for (i = 0; i < createNavs.length; i++) {
                var a = createNavs[i].querySelector('a');
                var obj = JSON.parse(decodeURIComponent(a.hash.substring(1)));
                a.href = 'javascript:void(0);';
                (function(theObj) {
                    a.onclick = function () {
                        var tag = typeof theObj.tag === "undefined" ? null : theObj.tag;
                        ipf.showCreatePostPopup(theObj.postType, theObj.dialogTitle, tag);
                    };
                })(obj);
            }
        </script>
        <?php
    }
}