(function ($) {
    "use strict";

    if (typeof window.ipf === "undefined") window.ipf = {};
    var lang = ipf.lang = ipfLang;


    document.onreadystatechange = function () {
        if (document.readyState == "interactive") { // when document is ready
            setUpPostEditability();
            $('.ipfCreate').click(function (e) {
                e.preventDefault();
                var postType = e.target.getAttribute("data-post-type");
                var dialogTitle = e.target.getAttribute("data-dialog-title");
                showCreatePostPopup(postType, dialogTitle);
                return false;
            });
        }
    };


    function setUpPostEditability() {
        var posts = document.querySelectorAll(".post:not(body), body.events-single #tribe-events, body:not(.events-single) .type-tribe_events, .page:not(body)");

        for (var i= 0; i < posts.length; i++) {
            var post = posts[i];
            var postQ = $(post);
            var id = postQ.closest(".post, body.events-single, .type-tribe_events, .page").attr('class').match(/post(?:id)?-(\d+)/)[1];
            if (!ipf.editablePosts[id]) continue;
            var restr = ipf.postRestrictions[id];


            var body = $(document.body);
            if (!(body.hasClass('postid-'+id) || body.hasClass('page-'+id)) || postQ.parents('#content, .single-tribe_events').length ==0) {
                postQ.addClass('ipfAutoHideControls');
            }

            console.log(restr);
            if (restrictionAllowed(restr, 'post_title')) {
                var titleElem = post.querySelector('.entry-title, .tribe-events-single-event-title, .tribe-events-list-event-title .tribe-event-url');
                setUpTitleEditability(titleElem, id);
            }

            if (restrictionAllowed(restr, 'thumbnail')) {
                var imageElem = post.querySelector('.wp-post-image, .ipfNoImage');
                if (imageElem === null) {
                    imageElem = $('<div class="post-thumbnail featured-image"><img class="ipfNoImage" src=""><span class="ipfRefreshThumb">' +lang.refreshPageImage
                        +'</span></div>')[0];
                    postQ.prepend(imageElem);
                    imageElem = imageElem.firstChild;
                }
                setUpImageEditability(imageElem, id, post);
            }

            if (restrictionAllowed(restr, 'post_excerpt')) {
                setTimeout(function (post2) {fixExcerptEditAppearance(post2);}, 1500, post); /*wait all javascript*/
            }
        }
    }


    // CONTENT
    function makeContentEditable(elem, postId) {
        elem.style.opacity = 0.5;
        $("#edit-container-"+postId).addClass('editing');
        ipfData.getTheContent(postId, function (content) {
            ipfEditor.makeContentEditor(elem, postId, content);
            elem.style.opacity = null;
        }, function () {});
    }

    function saveContent(elem, postId) {
        elem.style.opacity = 0.5;
        ipfData.setTheContent(postId, fixYouTubeEmbed(ipfEditor.getContent(elem)), function (content) {
            ipfEditor.destroyEditor(elem);
            $(elem).closest('.entry-content, .tribe-events-content').html(content);
            jQuery( document.body ).trigger( 'post-load' );
        }, function () {});
    }

    function cancelContentEdit(elem, postId) {
        $("#edit-container-"+postId).removeClass('editing');
        ipfEditor.cancelEdit(elem);
    }

    function fixYouTubeEmbed(innerHTML) {
        return innerHTML.replace(/(?:^|<(p)>|<br>|<br\/>)(?:\s*|<br>|<br\/>)*((?:http(?:s)?:\/\/)(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:watch\?.*?\bv=|embed\/|v\/)|ytimg\.com\/vi\/)([-a-zA-Z0-9=%_]+)(?:&[^\s<>]*)?)(?:\s*|<br>|<br\/>)*(?:$|<\/\1>|<br>|<br\/>)/mg,
            '\n$2\n');
    }


    // TITLE
    function setUpTitleEditability(titleElem, id) {
        var editId = 'edit-title-'+id;
        titleElem = $(titleElem);
        titleElem.wrap('<div style="position:relative"></div>');
        titleElem.attr('id', editId);
        titleElem.before(
            '<div class="editTitleContainer editContainer" id="edit-title-container-'+id+'">' +
            '<button onclick=\'ipf.makeTitleEditable(document.getElementById("'+editId+'"), '+id+')\' class="ipfEdit" title="'+lang.editTitle+'"></button>' +
            '<button onclick=\'ipf.saveTitle(document.getElementById("'+editId+'"), '+id+')\' class="ipfSave"  title="'+lang.save+'"></button>' +
            '<button onclick=\'ipf.cancelTitleEdit(document.getElementById("'+editId+'"), '+id+')\' class="ipfCancel"  title="'+lang.cancel+'"></button>' +
            '</div>');
    }

    function makeTitleEditable(elem, postId) {
        elem.style.opacity = 0.5;
        $("#edit-title-container-"+postId).addClass('editing');
        ipfData.getTheTitle(postId, function (title) {
            ipfEditor.makeTitleEditor(elem, postId, title);
            elem.style.opacity = null;
        }, function () {});
    }

    function saveTitle(elem, postId) {
        elem.style.opacity = 0.5;
        ipfData.setTheTitle(postId, $(elem).text(), function (title) {
            $(elem).html(title);
            elem.style.opacity = 1;
            $("#edit-title-container-"+postId).removeClass('editing');
            ipfEditor.destroyEditor(elem);
        }, function () {});
    }

    function cancelTitleEdit(elem, postId) {
        $("#edit-title-container-"+postId).removeClass('editing');
        ipfEditor.cancelEdit(elem);
    }


    // EXCERPT
    function makeExcerptEditable(elem, postId) {
        elem.style.opacity = 0.5;
        $("#edit-excerpt-container-"+postId).addClass('editing');
        ipfData.getTheExcerpt(postId, function (excerpt) {
            ipfEditor.makeExcerptEditor(elem, postId, excerpt);
            elem.style.opacity = null;

            // ensure appearance remains correct
            var postQ = $(elem).closest('.post-'+postId+', .postid-'+postId);
            var excerptEditAreaQ = postQ.find('.excerptEditArea');
            var buttonsQ = postQ.find('.editExcerptContainer button');
            var editExcerptDecorQ = postQ.find('.editExcerptDecor');
            var editExcerptContainerQ = postQ.find('.editExcerptContainer');
            ipfEditor.addEditorListener(elem, 'change', function () {
                fixExcerptEditAppearanceFast(excerptEditAreaQ, editExcerptDecorQ,
                    editExcerptContainerQ, buttonsQ);
            });
        }, function () {});
    }

    function saveExcerpt(elem, postId) {
        elem.style.opacity = 0.5;
        ipfData.setTheExcerpt(postId, $(elem).text(), function (excerpt) {
            $(elem).html(excerpt);
            elem.style.opacity = 1;
            $("#edit-excerpt-container-"+postId).removeClass('editing');
            ipfEditor.destroyEditor(elem);
            fixExcerptEditAppearance($(elem).closest('.post-'+postId+', .postid-'+postId)[0]);
            jQuery( document.body ).trigger( 'post-load' );
        }, function () {});
    }

    function cancelExcerptEdit(elem, postId) {
        $("#edit-excerpt-container-"+postId).removeClass('editing');
        ipfEditor.cancelEdit(elem);
        fixExcerptEditAppearance($(elem).closest('.post-'+postId+', .postid-'+postId)[0]);
    }

    function fixExcerptEditAppearance(postElem) {
        postElem = $(postElem);
        var height = postElem.find('.excerptEditArea').height();
        postElem.find('.editExcerptDecor, .editExcerptContainer').css('height', height+"px");
        var buttons = postElem.find('.editExcerptContainer button');
        buttons.css('margin-top', (height*0.3-buttons.height()/2)+"px");
    }

    function fixExcerptEditAppearanceFast(excerptEditAreaQ, editExcerptDecorQ,
                                          editExcerptContainerQ, buttonsQ) {
        var height = excerptEditAreaQ.height();
        editExcerptDecorQ.add(editExcerptContainerQ).css('height', height+"px");
        buttonsQ.css('margin-top', (height*0.3-buttonsQ.height()/2)+"px");
    }


    // FEATURED IMAGE
    function setUpImageEditability(imageElem, id, postElem) {
        if (!imageElem.id) {
            imageElem.id = 'edit-image-'+id;
        }
        var editId = imageElem.id;
        imageElem = $(imageElem);
        imageElem.parent().css("position", "relative").css("margin", "0");
        var controls = $('<div class="editImageContainer editContainer" id="edit-image-container-'+id+'">' +
            '<button class="ipfEdit" title="'+lang.changeImage+'"></button>' +
            '<button class="ipfRemove"  title="'+lang.removeImage+'"></button>' +
            '</div>');
        imageElem.after(controls);
        $(postElem).find(".ipfAddImage").add(controls.find('.ipfEdit')).click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            ipf.changeImage(document.getElementById(editId), id, postElem);
        });
        controls.find('.ipfRemove').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            ipf.removeImage(document.getElementById(editId), id, postElem);
        });
    }

    function changeImage(elem, postId, postElem) {
        var restr = ipf.postRestrictions[postId];
        var applyChange = function(attachment) {
            elem.style.opacity = 0.5;
            ipfData.setTheThumbnail(postId, attachment.id, function (resp) {
                elem = $(elem);
                resp = $(resp);
                resp.addClass('wp-post-image');
                resp.attr("id", elem.attr("id"));
                $(elem).replaceWith(resp);
                if (typeof postElem !== "undefined") {
                    $(postElem).addClass("has-post-thumbnail");
                }
            }, function (xhr, ign, status) { showErrorResponseDialog(xhr.responseText, status); });
        };

        if (restrictionGet(restr, "thumbnail.load_media", true)) {
            openFileFrame(postId, 'image', false, applyChange);
        } else {
            showUserUploadedImageDialog(postId, false, restrictionGet(restr, "thumbnail.can_upload", true), applyChange);
        }
    }

    function removeImage(elem, id, postElem) {
        var dialog;
        var actions = [
            {
                text: lang.removePostImage,
                click: function () {
                    ipfData.removeTheThumbnail(id, false,
                        function () {
                            elem.src = elem.srcset = "";
                            $(elem).removeClass("wp-post-image").addClass("ipfNoImage");
                            if (typeof postElem !== "undefined") {
                                $(postElem).removeClass("has-post-thumbnail");
                            }
                            $(dialog).dialog("close");
                        },
                        function() {
                            $(dialog).dialog("close");
                            showErrorResponseDialog(xhr.responseText, status);
                        });
                }
            }, {
                text: lang.removeDeleteImage,
                class: "ipfImageCompletelyDelete",
                disabled: true,
                click: function () {
                    ipfData.removeTheThumbnail(id, true,
                        function () {
                            elem.src = elem.srcset = "";
                            $(elem).removeClass("wp-post-image").addClass("ipfNoImage");
                            if (typeof postElem !== "undefined") {
                                $(postElem).removeClass("has-post-thumbnail");
                            }
                            $(dialog).dialog("close");
                        },
                        function (xhr, ign, status) {
                            $(dialog).dialog("close");
                            showErrorResponseDialog(xhr.responseText, status);
                        });
                }
            }
        ];
        dialog = getSmallModalDialog(actions,
            lang.confirmation, "<img class='ipfDeleteImgConfirmImg' src='"+elem.src+"'>" +
            "<span  class='ipfDeleteImgConfirm'>"+lang.removeImageConfirm+"</span>", null, true,
            "Close", true, function () {
                var uiDialog = $(this).closest(".ui-dialog");
                uiDialog.find(".ui-dialog-buttonset").find('button').after('<br/>');
                ipfData.canDeleteTheThumbnail(id, function (resp) {
                    if (resp) {
                        uiDialog.find(".ipfImageCompletelyDelete").button("option", "disabled", false);
                    }
                });
            });
    }

    var IMAGES_PER_LOAD = 15;
    function showUserUploadedImageDialog(postId, multiple, allowUpload, onSelect) {
        var unique = ++autoIncrement;
        var dialog = jQuery('<div class="ipfSelectCustomImages"></div>');
        var imagesWrap = jQuery('<div class="ipfImagesWrap"></div>');
        dialog.data("offset", 0);
        dialog.append(imagesWrap);

        var loadMore = function() {
            ipfData.getUserImages(dialog.data("offset"), IMAGES_PER_LOAD, function (images) {
                    if (images.length === 0) {
                        $("ipfLoadMore"+unique).attr("disabled", "disabled");
                        return;
                    }
                    for (var i=0; i < images.length; i++) {
                        var image = images[i];
                        var imgDiv = jQuery("<div></div>").addClass("ipfImgDiv").attr("data-id", image.id);
                        imgDiv.append("<img src='"+image.url+"'alt='"+image.post_title+"'/><div class='ipfImageTitle'>"+image.post_title+"</div>");
                        imgDiv.click(imgDiv, function(e) {
                            imagesWrap.children(".ipfImgDiv").removeClass("selected");
                            $(e.data).addClass("selected");
                        });
                        imgDiv.data("image", image);
                        imagesWrap.append(imgDiv);
                    }
                    dialog.data("offset", dialog.data("offset")+images.length);
                },
                function (xhr, ign, status) {
                    $(dialog).dialog("close");
                    showErrorResponseDialog(xhr.responseText, status);
                }
            );
        };

        var actions = [
            {
                text: "Select",
                click: function () {
                    if (typeof onSelect !== "undefined") {
                        onSelect(imagesWrap.children(".selected").data("image"));
                    }
                    $( this ).dialog( "close" );
                }
            },
            {
                id: "ipfLoadMore"+unique,
                text: "Load More",
                click:  function() {loadMore();}
            },
            {text: "Close", click:  function() {$( this ).dialog( "close" );}}
        ];
        var width, winWidth = $( window ).width();
        if (winWidth < 820) width = winWidth*0.9;
        else width = 800;

        dialog.dialog({
            width: width,  height: $( window ).height() * 0.9,
            dialogClass: "ipfEditDialog ipfSelectImageDialog", modal: true,
            open: function(event, ui) {
                jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                $('.ui-widget-overlay').click(function() { dialog.dialog("close"); });
                loadMore();
            },
            buttons: actions, title: "Select Image", closeOnEscape: true
        });
    }

    // FILE FRAME MANAGEMENT
    var file_frame, onFileFrameSelect, fileFrameType, fileFrameMultiple;
    function openFileFrame(postId, type, multiple, onSelect) {
        onFileFrameSelect = onSelect;
        if (!file_frame || type != fileFrameType || multiple != fileFrameMultiple) {
            wp.media.model.settings.post.id = postId;
            file_frame = wp.media.frames.file_frame = wp.media({
                title: lang.selectUploadFile,
                button: {
                    text: lang.select
                },
                library : {
                    type : type
                },
                multiple: multiple
            });
            fileFrameType = type;
            fileFrameMultiple = multiple;
            file_frame.on( 'select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                onFileFrameSelect(attachment, postId);
            });
        } else {
            // console.log(file_frame.uploader.uploader);
            file_frame.uploader.uploader.param( 'post_id', postId );
        }

        // console.log("el");
        // console.log(file_frame.$el);
        // console.log(file_frame.$el.parent()[0]);
        // console.log(file_frame.$el[0]);
        // file_frame.$el.css('display', 'none');
        // file_frame.close();
        file_frame.open();
    }


    // POST SETTINGS
    function setupEditBoxes(postIdOrType, elem) {
        // elem.innerHTML = html;
        var groups = elem.getElementsByClassName('ipfEditBoxGroup');
        for (var i=0; i < groups.length; i++) {
            try {
                var group = groups[i];
                var setupGroup = group.getAttribute('data-setup');
                if (!setupGroup.match(/^\s*$/)) {
                    eval(setupGroup).call(group, postIdOrType, elem);
                }
                var boxes = group.getElementsByClassName('ipfEditBox');
                for (var j=0; j < boxes.length; j++) {
                    try {
                        var box = boxes[j];
                        var setupBox = box.getAttribute('data-setup');
                        if (!setupBox.match(/^\s*$/)) {
                            var boxElem = box.querySelector("*");
                            eval(setupBox).call(boxElem, boxElem, postIdOrType, group);
                        }

                    } catch (e) {
                        console.error("Error setting up edit box "+box.getAttribute("data-box-name")+" "+e+"\n"+e.stack);
                    }
                }
            } catch (e) {
                console.error("Error setting up edit group "+group.getAttribute("data-group-name")+"\n"+e.stack);
            }
        }
    }

    function validateEditBoxes(postIdOrType, elem) {
        var groups = elem.getElementsByClassName('ipfEditBoxGroup');
        for (var i=0; i < groups.length; i++) {
            var group = groups[i];
            var boxes = group.getElementsByClassName('ipfEditBox');
            for (var j=0; j < boxes.length; j++) {
                try {
                    var box = boxes[j];
                    var isValid;
                    var validateBox = box.getAttribute('data-validate');
                    var boxElem = box.querySelector("*");
                    if (!validateBox.match(/^\s*$/)) {
                        isValid = eval(validateBox).call(boxElem, boxElem,  postIdOrType, group);
                    } else {
                        isValid = boxElem.checkValidity();
                    }
                    if (!isValid) return false;
                } catch (e) {
                    console.error("Error validating edit box "+box.getAttribute("data-box-name")+" "+e+"\n"+e.stack);
                }
            }
        }
        return true;
    }

    function getEditBoxValues(postIdOrType, elem) {
        var values = {};
        var groups = elem.getElementsByClassName('ipfEditBoxGroup');
        for (var i=0; i < groups.length; i++) {
            var group = groups[i];
            var groupValues = values[group.getAttribute("data-group-name")] = {};
            var boxes = group.getElementsByClassName('ipfEditBox');
            for (var j=0; j < boxes.length; j++) {
                try {
                    var box = boxes[j];
                    var value;
                    var getValue = box.getAttribute('data-get-value');
                    var boxElem = box.querySelector("*");
                    if (!getValue.match(/^\s*$/)) {
                        value = eval(getValue).call(boxElem, boxElem, postIdOrType, group);
                    } else {
                        value = $(boxElem).val();
                    }
                    groupValues[box.getAttribute("data-box-name")] = value;
                } catch (e) {
                    console.error("Error getting value from edit box "+box.getAttribute("data-box-name")+" "+e+"\n"+e.stack);
                }
            }
        }
        return values;
    }

    function showEditPostPopup(postId) {
        var dialog = null;
        ipfData.getEditBoxes(postId, function (html) {
            var actions = [
                {
                    text: lang.save,
                    click: function () {
                        if (validateEditBoxes(postId, dialog[0])) {
                            ipfData.editPost(postId, getEditBoxValues(postId, dialog[0]),
                                function (editBoxesHtml) {
                                    dialog[0].innerHTML = editBoxesHtml;
                                    setupEditBoxes(postId, dialog[0]);
                                    toastr.options = {
                                        "closeButton": false, "newestOnTop": true, "progressBar": false,
                                        "positionClass": "toast-top-center", "preventDuplicates": false,
                                        "showDuration": "300", "hideDuration": "1000", "timeOut": "1700",
                                        "showEasing": "swing", "showMethod": "fadeIn", "hideMethod": "fadeOut"
                                    };
                                    //noinspection JSUnresolvedVariable
                                    toastr.success(" ", "Saved!");
                                },
                                function (xhr, ign, status) { showErrorResponseDialog(xhr.responseText, status); });
                        }
                    }
                }, {text: "Close", click:  function() {$( this ).dialog( "close" );}}
            ];
            var width, winWidth = $( window ).width();
            if (winWidth < 820) width = winWidth*0.9;
            else width = 800;
            dialog = jQuery("<div>"+html+"</div>");
            dialog.dialog({
                width: width,  height: $( window ).height() * 0.9,
                dialogClass: "ipfEditDialog", modal: true,
                open: function(event, ui) {
                    jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                    $('.ui-widget-overlay').click(function() { dialog.dialog("close"); });
                },
                buttons: actions, title: lang.postAttributes, closeOnEscape: true
            });

            setupEditBoxes(postId, dialog[0]);
        }, function (xhr, ign, status) { showErrorResponseDialog(xhr.responseText, status); });
    }

    function showCreatePostPopup(postType, dialogTitle, tag) {
        var dialog = null;
        ipfData.getAddBoxes(postType, tag, function (html) {
            var actions = [
                {
                    text: "Create",
                    click: function () {
                        if (validateEditBoxes(postType, dialog[0])) {
                            ipfData.addPost(postType, getEditBoxValues(postType, dialog[0]), tag,
                                function (result) {
                                    if (typeof result.actionsJs !== "undefined") {
                                        for (var i = 0; i < result.actionsJs.length; i++) {
                                            var action = result.actionsJs[i];
                                            try {
                                                if (!action.match(/^\s*$/)) {
                                                    eval(action).call(result, dialog);
                                                }
                                            } catch (e) {
                                                console.error("Error applying post create action "+action+" "+e+"\n"+e.stack);
                                            }
                                        }
                                    }

                                },
                                function (xhr, ign, status) { showErrorResponseDialog(xhr.responseText, status); });
                        }
                    }
                }, {text: "Close", click:  function() {$( this ).dialog( "close" );}}
            ];
            var width, winWidth = $( window ).width();
            if (winWidth < 820) width = winWidth*0.9;
            else width = 800;
            dialog = jQuery("<div>"+html+"</div>");
            dialog.dialog({
                width: width,  height: $( window ).height() * 0.9,
                dialogClass: "ipfEditDialog", modal: true,
                open: function(event, ui) {
                    jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                    $('.ui-widget-overlay').click(function() { dialog.dialog("close"); });
                },
                buttons: actions, title: dialogTitle, closeOnEscape: true
            });

            // dialog = getSmallModalDialog(actions, "Ιδιότητες", html, null, true, "Κλείσιμο", true);

            setupEditBoxes(postType, dialog[0]);
        }, function (xhr, ign, status) { showErrorResponseDialog(xhr.responseText, status); });
    }

    function showCreatePostConfirm(dialog) {
        var theThis = this;
        dialog.dialog("close");
        getSmallModalDialog(
            [{text: lang.view, click: function(){window.location=theThis.URL}}],
            lang.createdSucesfully,
            lang.link+': <a href="'+this.URL+'" tabIndex="-1" target="_blank">'+this.URL+'</a><br/>'+lang.status+': <strong>'+this.post_data.post_status+'</strong>',
            null, true, lang.close);
    }


    // ipf functions

    function restrictionAllowed(restrictions, restriction) {
        var restr = restrictionGet(restrictions, restriction, {});
        return typeof restr['allow'] === "undefined" || restr['allow'];
    }

    function restrictionGet(restrictions, restriction, defaultValue) {
        if (typeof restriction === "undefined") {
            return restrictions;
        }

        if (typeof defaultValue === "undefined") {
            defaultValue = null;
        }

        if (!jQuery.isArray(restriction)) {
            restriction = restriction.split('.');
        }

        var obj = restrictions;
        var len = restriction.length;
        for (var i = 0; i < len-1; i++) {
            if (typeof obj[restriction[i]] === "undefined") return defaultValue;
            obj = obj[restriction[i]];
        }


        return typeof obj[restriction[len-1]] !== "undefined" ? obj[restriction[len-1]] : defaultValue;
    }

    function validateNotEmpty() {
        var valid= !this.value.match(/^\s*$/);
        if (!valid) {
            this.setCustomValidity("Required field");
            if (typeof this.reportValidity === "function") {
                this.reportValidity();
            }
        }

        return valid;
    }

    var SMALL_DIALOG_TEMPL = $('<div class="smallDialog"><div class="content"></div><div class="error"></div></div>');
    // creates and optionally shows a small modal dialog
    function getSmallModalDialog(buttonActions, title, content, errorContent, open, closeText, closeOnEscape, onOpen) {
        if (typeof content == "undefined") content = "";
        if (typeof errorContent == "undefined") errorContent = "";
        if (typeof closeOnEscape == "undefined") closeOnEscape = true;
        closeOnEscape = !!closeOnEscape;
        var smallDialog = SMALL_DIALOG_TEMPL.clone();
        smallDialog.find('.content').html(content);
        smallDialog.find('.error').html(errorContent);
        if (typeof closeText != "undefined") {
            if (buttonActions instanceof Array) {
                buttonActions.push({
                    text: closeText, click: function () {$(this).dialog("close")}
                })
            } else {
                buttonActions[closeText] = function () {$(this).dialog("close");}
            }
        }
        return smallDialog.dialog({
            width: Math.min(500, $( window ).width()), autoOpen: typeof(open) === "undefined" || open,
            modal: true, closeOnEscape: closeOnEscape, dialogClass: "smallDialog",
            open: function(event, ui) {
                jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                if (closeOnEscape) {
                    $('.ui-widget-overlay').click(function() { smallDialog.dialog("close"); });
                }
                if (typeof onOpen !== "undefined") {
                    onOpen.call(this, event, ui);
                }
            },
            buttons: buttonActions,
            title: title
        });
    }

    function showErrorResponseDialog(error, status, content, title) {
        if (typeof title == "undefined") title = lang.somethingHappened;
        var resp = false;
        try {
            resp = JSON.parse(error);
            if (typeof resp.error != "undefined") resp = resp.error;
        } catch (e) {
            resp = error
        }
        getSmallModalDialog(
            {},
            title,
            content,
            (resp? resp+"<br/>" : "")+"<strong>"+lang.status+":</strong> "+status, true, lang.close);
    }

    ipf.makeContentEditable = makeContentEditable;
    ipf.saveContent =  saveContent;
    ipf.cancelContentEdit =  cancelContentEdit;
    ipf.makeTitleEditable =  makeTitleEditable;
    ipf.saveTitle =  saveTitle;
    ipf.cancelTitleEdit =  cancelTitleEdit;
    ipf.changeImage =  changeImage;
    ipf.removeImage =  removeImage;
    ipf.makeExcerptEditable =  makeExcerptEditable;
    ipf.saveExcerpt =  saveExcerpt;
    ipf.cancelExcerptEdit =  cancelExcerptEdit;
    ipf.showEditPostPopup = showEditPostPopup;
    ipf.openFileFrame =  openFileFrame;
    ipf.getSmallModalDialog = getSmallModalDialog;
    ipf.showCreatePostPopup = showCreatePostPopup;
    ipf.showCreatePostConfirm = showCreatePostConfirm;
    ipf.restrictionAllowed = restrictionAllowed;
    ipf.restrictionGet = restrictionGet;
    ipf.validateNotEmpty = validateNotEmpty;

})(jQuery);