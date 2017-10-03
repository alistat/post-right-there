(function ($) {
    "use strict";

    if (typeof window.ipfData === "undefined") window.ipfData = {};

    ipfData.getTheContent = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_the_content&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.getTheTitle = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_the_title&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.getTheExcerpt = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_the_excerpt&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.setTheContent = function (post, content, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_content: content}), action: "ipf_set_the_content"},
            dataType: "json",
            success: success,
            error: error
        });
    };


    ipfData.setTheExcerpt = function (post, excerpt, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_excerpt: excerpt}), action: "ipf_set_the_excerpt"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.setTheTitle = function (post, title, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, post_title: title}), action: "ipf_set_the_title"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.setTheThumbnail = function (post, thumb, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {post: JSON.stringify({ID: post, thumb: thumb}), action: "ipf_set_the_thumbnail"},
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.removeTheThumbnail = function (post, deleteThumb, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_remove_the_thumbnail&post=" + post + "&remove=" + deleteThumb,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.canDeleteTheThumbnail = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_can_delete_the_thumbnail&post=" + post,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.getAttachmentImage = function (attachment, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_attachment_image&attachment=" + attachment,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.getUserImages = function (offset, perPage, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_user_images&offset=" + offset+"&perPage="+perPage,
            dataType: "json",
            success: success,
            error: error
        });
    };

    ipfData.getEditBoxes = function (post, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_edit_boxes&post=" + post,
            dataType: "html",
            success: success,
            error: error
        });
    };

    ipfData.getAddBoxes = function (post_type, tag, success, error) {
        $.ajax({
            type: "GET",
            url: adminURL + "?action=ipf_get_add_boxes&post_type=" + post_type+"&tag="+encodeURIComponent(tag),
            dataType: "html",
            success: success,
            error: error
        });
    };

    ipfData.editPost = function (post, boxData, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {boxData: JSON.stringify(boxData), post: post, action: "ipf_edit_post"},
            dataType: "html",
            success: success,
            error: error
        });
    };

    ipfData.addPost = function (postType, boxData, tag, success, error) {
        $.ajax({
            type: "POST",
            url: adminURL,
            data: {boxData: JSON.stringify(boxData), post_type: postType, action: "ipf_add_post", tag: tag},
            dataType: "json",
            success: success,
            error: error
        });
    };

})(jQuery);