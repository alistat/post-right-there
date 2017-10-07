<?php
defined( 'ABSPATH' ) or die( '' );

add_action( 'wp_head', 'prthHead' );
function prthHead() {
    if (prthShouldLoad()) {
        ?>
        <script>
            adminURL = "<?php echo admin_url('admin-ajax.php'); ?>";
            prth = {};
            prth.editablePosts = {};
            prth.postRestrictions = {};
        </script>
        <?php
    }
}

function prthPrintRestrictNavMenuSettings() {
    ?>
    <script type="text/javascript">
        jQuery( '#menu-to-edit').on( 'click', 'a.item-edit', function() {
            var settings  = jQuery(this).closest( '.menu-item-bar' ).next( '.menu-item-settings' );
            var css_class = settings.find( '.edit-menu-item-classes' );

            if( css_class.val().match("^prth-") ) {
                css_class.attr( 'readonly', 'readonly' );
//                settings.find( '.field-url' ).css( 'display', 'none' );
                settings.find( '.field-url input' ).attr( 'readonly', 'readonly' );
            }
        });
    </script>
    <?php
}

add_filter( 'the_content', 'prthMakeContentEditablable', 300);
function prthMakeContentEditablable($content) {
    global $wp_current_filter;
    $restr = isset(get_post()->prthRestrictions) ? get_post()->prthRestrictions : prthGetEditRestrictions();
    if (!in_array('get_the_excerpt', (array) $wp_current_filter)
        && !in_array('the_excerpt', (array) $wp_current_filter)
        && prthRestrictionAllowed($restr)) {
        $id = get_the_ID();
        $editId = "edit-content-$id";
        ob_start();
        ?>
        <div class="editContentContainer prthEditContainer" id="edit-container-<?php echo $id;?>">
            <?php if ( prthRestrictionAllowed($restr, "post_content") ): ?>
                <button onclick='prth.makeContentEditable(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthEdit"><?php esc_html_e("Edit Here", "prth");?></button>
                <button onclick='prth.saveContent(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthSave"><?php esc_html_e("Save", "prth");?></button>
                <button onclick='prth.cancelContentEdit(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthCancel"><?php esc_html_e("Cancel", "prth");?></button>
            <?php endif; ?>
            <?php if ( prthRestrictionAllowed($restr, "thumbnail") ): ?>
                <button class="prthAddImage" title="<?php esc_attr_e("Add Image", "prth");?>"> <?php esc_html_e("Add Image", "prth");?></button>
            <?php endif;?>
            <?php if ( prthRestrictionGet($restr, "showPostSettingsUi", true)): ?>
                <button onclick='prth.showEditPostPopup(<?php echo $id;?>)' class="prthProperties"><?php esc_html_e("Settings", "prth");?></button>
            <?php endif;?>
        </div>
        <?php
        $editControls = ob_get_clean();
        $restrJson = json_encode($restr);
        $content = "<script>
                window.prth.editablePosts['$id'] = true;
                window.prth.postRestrictions['$id'] = $restrJson;
            </script>"
            . $editControls."<div id='$editId'>".$content.'</div>';
    }
    return $content;
}

add_action('tribe_events_before_the_content', 'prthMarkEventEditable');
function prthMarkEventEditable() {
    $id = get_the_ID();
    $restr = isset(get_post()->prthRestrictions) ? get_post()->prthRestrictions : prthGetEditRestrictions();
    if (prthRestrictionAllowed($restr)) {
        echo "<script>window.prth.editablePosts['$id'] = true;</script>";
    }
}

add_filter( 'the_excerpt', 'prthMakeExcerptEditablable', 300);
function prthMakeExcerptEditablable($excerpt) {
    global $wp_current_filter;
    $restr = isset(get_post()->prthRestrictions) ? get_post()->prthRestrictions : prthGetEditRestrictions();
    if (!in_array('get_the_excerpt', (array) $wp_current_filter)
        && prthRestrictionAllowed($restr)) {
        $id = get_the_ID();
        $editId = "edit-excerpt-$id";
        ob_start();
        ?>
        <div class="prthEditExcerptContainer prthEditContainer" id="edit-excerpt-container-<?php echo $id;?>">
            <button onclick='prth.makeExcerptEditable(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthEdit" title="<?php esc_attr_e("Edit Excerpt", "prth");?>"></button>
            <button onclick='prth.saveExcerpt(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthSave" title="<?php esc_attr_e("Save", "prth");?>"></button>
            <button onclick='prth.cancelExcerptEdit(document.getElementById("<?php echo $editId;?>"), <?php echo $id;?>)' class="prthCancel" title="<?php esc_attr_e("Cancel", "prth");?>"></button>
        </div>
        <div class="prthEditExcerptDecor"></div>
        <?php
        $editControls = ob_get_clean();
        $excerpt = "<script>window.prth.editablePosts['$id'] = true;</script>".'<div style="position:relative">'.$editControls
            ."<div id='$editId' class='excerptEditArea'>".$excerpt.'</div></div>';
    }
    return $excerpt;
}

add_filter( 'post_thumbnail_html', 'prthEmptyImageHolder', 10, 3 );
function prthEmptyImageHolder($html, $postId, $thumbId) {
    if (!$thumbId) {
        return '<div class="post-thumbnail featured-image"><img class="prthNoImage" src=""></div>';
    }
    return $html;
}

function prthRenderEditBoxes($editBoxGroups) {
    foreach ($editBoxGroups as $groupName => $group) {
        ?>
        <div class="prthEditBoxGroup <?php echo $groupName;?>" data-setup="<?php echo esc_attr($group['setupJs']);?>" data-group-name="<?php echo esc_attr($groupName);?>">
            <h4 class="prthEditBoxGroupHeader"><?php echo $group['title'];?></h4>
            <?php foreach ($group['boxes'] as $boxName => $box): ?>
                <div class="prthEditBox <?php echo esc_attr($boxName);?>" data-setup="<?php echo esc_attr($box['setupJs']);?>"
                 data-get-value="<?php echo esc_attr($box['getValueJs']);?>" data-validate="<?php echo esc_attr($box['validateJs']);?>"
                 data-box-name="<?php echo esc_attr($boxName);?>" >
                    <?php echo $box['html'];?>
                 </div>
            <?php endforeach;?>
        </div>
        <?php
    }
}

add_action( 'wp_footer', 'prthFoot' );
function prthFoot() {
    if (prthShouldLoad()) {
        ?>
        <div style="display: none;">
            <?php wp_editor('', "prthDummyEditor"); ?>
        </div>
        <a style="display: none;" href="https://icons8.com">Icon pack by Icons8</a>

        <script>
            prth.tinyMceExternalPlugins = <?php echo json_encode(apply_filters("mce_external_plugins", array())); ?>;
            prth.tinyMceExtraButtons = <?php echo "'".implode(" ", apply_filters("mce_buttons", array()))."'"; ?>;
            prth.ajaxurl = prth.adminURL = "<?php echo admin_url('admin-ajax.php'); ?>";
            // support photo gallery plugin
            if (typeof bwg_admin_ajax === "undefined") {
                window.bwg_admin_ajax = adminURL+"?action=BWGShortcode";
                window.bwg_plugin_url = "<?php echo plugins_url('photo-gallery'); ?>";
            }
            prth.authorUrl = "?post_type=any&author=<?php esc_attr_e(get_the_author_meta('login', get_current_user_id())); ?>";
            var myPostsNavs = document.getElementsByClassName('prth-myPostsNav');
            for (var i = 0; i < myPostsNavs.length; i++) {
                myPostsNavs[i].querySelector('a').href = prth.authorUrl;
            }

            var createNavs = document.getElementsByClassName('prth-createNav');
            for (i = 0; i < createNavs.length; i++) {
                var a = createNavs[i].querySelector('a');
                var obj = JSON.parse(decodeURIComponent(a.hash.substring(1)));
                a.href = 'javascript:void(0);';
                (function(theObj) {
                    a.onclick = function () {
                        var tag = typeof theObj.tag === "undefined" ? null : theObj.tag;
                        prth.showCreatePostPopup(theObj.postType, theObj.dialogTitle, tag);
                    };
                })(obj);
            }
        </script>
        <?php
    }
}