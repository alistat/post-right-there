=== Post Right There ===
Contributors: alistat
Tags: front, inline, inplace, post, page, create, edit, manage, rich, tinymce, front-end-editor
Requires at least: 3.0.1
Tested up to: 4.8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin to create, edit inline and manage posts, pages and custom posts from the front end.

== Description ==

## WordPress plugin to create, edit inline and manage any post type from the front end.



### Main features include:

*   **Any post type** (can be disabled for specific post types)
*   **Edit post content** directly in the front using the built in wysiwyg editor for maximum compatibility with the admin panel
*   **Edit post info** (title, status etc) in pop up dialog
*   "New Post" and "New Page" **menu entries** that show pop up dialog to create new posts
*   Selecting images in the front using the **standard media library** of the admin panel
*   **Quick edit** image, title and excerpt of posts from archive pages
*   **HTML editor** with syntax highlighting
*   Programmatic: Easily support any **custom meta field**
*   Programmatic: **Restrictions system** for fine grained control over permissions





The plugin provides built in support for setting and editing: title, content in wysiwyg editor, post image, url slug, post status, author, parent page, categories and, if SEO Ultimate is installed, search engine title/description and social media title/description and image.





### Supported plugins

Post Right There collaborates with the following plugins:

*   **SEO Ultimate** to let you manage social media & search engine settings
*   **WP Statistics** to let you view visits count of your posts
*   **Photo Gallery** to let you add photo galleries from the front
*   **Tribe Events Calendar** (partially) to let you edit the basic info of your events





### Custom Meta Fields

Apart from the built in fields mentioned above, it is easy to add support for any custom meta field using the custom filters:<span class="filterWrap">`ipf_edit_boxes``ipf_add_boxes``ipf_edit_post``ipf_add_post_before_create`</span> to specify extra fields that can be edited from the front, and to specify fields that are present during the creation of a new post from the front.
For examples see file`built-in-edit-boxes.php`





### Restriction System

The plugins also features a flexible restrictions system that allows to grant user rights to edit or create posts of specific types according to custom criteria. The restrictions system also provides the ability to disallow editing of individual fields or allow these fields to take specific values. For example you may want to allow authors to only publish (custom) posts of specific categories according to their roles.





Currently available in English and Greek.

_Icon pack by icons8.com_



== Installation ==

1. Upload the plugin folder, named `post-right-there`, to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)



== Screenshots ==

1. Edit post attributes
2. Edit post content
3. Select images from media library
4. Create a new post from pop-up
5. Quick edit title
6. Quick edit excerpt of post in an archive page
7. Image remove confirmation
8. Disable plugin for specific post types
9. HTML editor



