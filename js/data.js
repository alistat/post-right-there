(function ($) {
    "use strict";

    if (typeof window.prthData === "undefined") window.prthData = {};
    var adminURL = prth.ajaxurl;

    prthData.getTheContent = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_the_content&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.getTheTitle = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_the_title&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.getTheExcerpt = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_the_excerpt&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.setTheContent = function (post, content, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_content: content}), action: "prth_set_the_content"},
            dataType: "json",
            success: success,
            error: error
        });
    };


    prthData.setTheExcerpt = function (post, excerpt, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_excerpt: excerpt}), action: "prth_set_the_excerpt"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.setTheTitle = function (post, title, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_title: title}), action: "prth_set_the_title"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.setTheThumbnail = function (post, thumb, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, thumb: thumb}), action: "prth_set_the_thumbnail"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.removeTheThumbnail = function (post, deleteThumb, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_remove_the_thumbnail&post=" + post + "&remove=" + deleteThumb,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.canDeleteTheThumbnail = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_can_delete_the_thumbnail&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.getAttachmentImage = function (attachment, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_attachment_image&attachment=" + attachment,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.getUserImages = function (offset, perPage, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_user_images&offset=" + offset+"&perPage="+perPage,
            dataType: "json",
            success: success,
            error: error
        });
    };

    prthData.getEditBoxes = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_edit_boxes&post=" + post,
            dataType: "html",
            success: success,
            error: error
        });
    };

    prthData.getAddBoxes = function (post_type, tag, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=prth_get_add_boxes&post_type=" + post_type+"&tag="+encodeURIComponent(tag),
            dataType: "html",
            success: success,
            error: error
        });
    };

    prthData.editPost = function (post, boxData, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {boxData: JSON.stringify(boxData), post: post, action: "prth_edit_post"},
            dataType: "html",
            success: success,
            error: error
        });
    };

    prthData.addPost = function (postType, boxData, tag, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {boxData: JSON.stringify(boxData), post_type: postType, action: "prth_add_post", tag: tag},
            dataType: "json",
            success: success,
            error: error
        });
    };

})(jQuery);