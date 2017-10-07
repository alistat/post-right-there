<?php defined( 'ABSPATH' ) or die( '' ); ?>

<style>#prth_nav_link {display: unset;}</style>
<div id="posttype-prth" class="posttypediv">
    <div id="tabs-panel-prth" class="tabs-panel tabs-panel-active">
        <ul id ="prth-checklist" class="categorychecklist form-no-clear">
            <?php foreach (apply_filters("prth_create_post_types", array()) as $menuPostType): ?>
                <li>
                    <label class="menu-item-title">
                        <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"><?php echo esc_attr($menuPostType["menuLabel"]); ?>
                    </label>
                    <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
                    <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php echo esc_attr($menuPostType["menuLabel"]); ?>">
                    <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]"
                           value="#<?php echo esc_attr(rawurlencode(json_encode($menuPostType))); ?>">
                    <input type="hidden" class="menu-item-classes" readonly name="menu-item[-1][menu-item-classes]" value="prth-createNav">
                </li>
            <?php endforeach; ?>

            <li>
                <label class="menu-item-title">
                    <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="-1"> <?php _e("My Posts", "prth"); ?>
                </label>
                <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
                <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php _e("My Posts", "prth"); ?>">
                <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="javascript:void(0);">
                <input type="hidden" class="menu-item-classes" readonly name="menu-item[-1][menu-item-classes]" value="prth-myPostsNav">
            </li>
        </ul>
    </div>
    <p class="button-controls">
        <span class="list-controls">
            <a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-prth" class="select-all"><?php _e("Select All"); ?></a>
        </span>
        <span class="add-to-menu">
            <input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-prth">
            <span class="spinner"></span>
        </span>
    </p>
</div>
