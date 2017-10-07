(function ($) {
    "use strict";
    if (typeof window.prthEditor === "undefined") window.prthEditor = {};
    if (typeof window.prth === "undefined") window.prth = {};
    var lang = prth.lang = prthLang;

    var autoIncrement = 0;

    var tinyMcePlugins = [
        'advlist lists image charmap anchor hr',
        'visualblocks',
        'insertdatetime media table contextmenu paste',
        'textcolor colorpicker emoticons wpembed wordpress wplink wpemoji wpeditimage'
    ];

    var tinyMcePluginsURL = "//cdn.tinymce.com/4/plugins/";

    var tinyMceExternalPlugins = $.extend({}, prth.tinyMceExternalPlugins, {
        'advlist': tinyMcePluginsURL+"advlist/plugin.min.js",
        'anchor': tinyMcePluginsURL+"anchor/plugin.min.js",
        'visualblocks': tinyMcePluginsURL+"visualblocks/plugin.min.js",
        'insertdatetime': tinyMcePluginsURL+"insertdatetime/plugin.min.js",
        'table': tinyMcePluginsURL+"table/plugin.min.js",
        'contextmenu': tinyMcePluginsURL+"contextmenu/plugin.min.js",
        'emoticons': tinyMcePluginsURL+"emoticons/plugin.min.js"
    });

    var tinyMceThemeURL = "//cdn.tinymce.com/4/themes/modern/theme.min.js";
    var tinyMceSkinURL = "//cdn.tinymce.com/4/skins/lightgray";

    var tinyMceMenu = {
        file   : {title : 'File'  , items : 'wpSave wpCancel | newdocument'},
        edit   : {title : 'Edit'  , items : 'undo redo | cut copy paste pastetext | selectall'},
        insert : {title : 'Insert', items : 'wpImage wpFile media link unlink | charmap anchor hr insertdatetime'},
        view   : {title : 'View'  , items : 'visualblocks visualaid'},
        format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
        tools: {title : 'Tools', items: 'prthCode', menuHasIcons: true}
    };
    var tinyMceToolbar1 = 'fontsizeselect styleselect | undo redo | bold italic underline | alignleft aligncenter alignright | outdent indent';
    var tinyMceToolbar2 = 'bullist numlist | forecolor backcolor | link unlink  hr table wpImage prthCode emoticons '+prth.tinyMceExtraButtons;


    function makeContentEditor(elem, postId, newHtml, noFocus) {
        noFocus = !!noFocus;
        if (!elem.id) elem.id = "prthEditor-"+(++autoIncrement);

        elem.oldHTML = elem.innerHTML;
        if (typeof newHtml !== "undefined" && newHtml !== null) elem.innerHTML = newHtml;

        elem.setAttribute("contentEditable", "");
        tinymce.init({
            selector: '#'+elem.id,
            entity_encoding: "raw",
            inline: true,
            plugins: tinyMcePlugins,
            external_plugins: tinyMceExternalPlugins,
            toolbar1: tinyMceToolbar1,
            toolbar2: tinyMceToolbar2,
            fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            image_advtab: true,
            menu: tinyMceMenu,
            convert_urls: false,
            browser_spellcheck : true,
            imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
            extended_valid_elements : "iframe[src|frameborder|style|scrolling|class|width|height|name|align]",
            theme_url: tinyMceThemeURL,
            theme_advanced_resizing: true,
            theme_advanced_resizing_use_cookie : false,
            contentCSS: null,
            setup: function(ed) {
                var restr = prth.postRestrictions[postId];

                if (prth.restrictionGet(restr, "post_content.attach_images", true)) {
                    var insertImageIcon = 'https://maxcdn.icons8.com/iOS7/PNG/25/Photo_Video/add_image_filled-25.png';
                    ed.addButton('wpImage', {
                        title: lang.insertImage,
                        image: insertImageIcon,
                        onclick: function() { onEditorInsertImageClick(postId, elem, ed); }
                    });
                    ed.addMenuItem('wpImage', {
                        text: lang.insertImage,
                        context: 'insert',
                        image: insertImageIcon,
                        onclick: function() { onEditorInsertImageClick(postId, elem, ed); }
                    });
                }

                if (prth.restrictionGet(restr, "post_content.attach_files", true)) {
                    var insertFileIcon = "https://maxcdn.icons8.com/windows8/PNG/26/Files/add_file-26.png";
                    ed.addButton('wpFile', {
                        title: lang.insertFile,
                        image: insertFileIcon,
                        onclick: function () {
                            onEditorInsertFileClick(postId, elem, ed);
                        }
                    });
                    ed.addMenuItem('wpFile', {
                        text: lang.insertFile,
                        context: 'insert',
                        image: insertFileIcon,
                        onclick: function () {
                            onEditorInsertFileClick(postId, elem, ed);
                        }
                    });
                }

                ed.addMenuItem('wpSave', {
                    text: lang.save,
                    context: 'file',
                    image: "https://maxcdn.icons8.com/Android_L/PNG/24/Programming/save-24.png",
                    onclick: function() { prth.saveContent(elem, postId); }
                });
                ed.addMenuItem('wpCancel', {
                    text: lang.cancel,
                    context: 'file',
                    image: "https://maxcdn.icons8.com/Color/PNG/24/Very_Basic/cancel-24.png",
                    onclick: function() { prth.cancelContentEdit(elem, postId); }
                });
                ed.addButton('prthCode', {
                    title: 'Source Code',
                    image: "https://maxcdn.icons8.com/Color/PNG/24/Programming/source_code-24.png",
                    onclick: function() { openSourceDialog(elem); }
                });
                ed.addMenuItem('prthCode', {
                    text: 'Source Code',
                    context: 'tools',
                    image: "https://maxcdn.icons8.com/Color/PNG/24/Programming/source_code-24.png",
                    onclick: function() { openSourceDialog(elem); }
                });
                ed.on('init', function(e) {
                    elem.blur();
                    elem.focus();
                });
            }
        });
        if (!noFocus) elem.focus();
        return tinymce.get(elem.id);
    }

    function makeTitleEditor(elem, postId, newHtml, noFocus) {
        noFocus = !!noFocus;
        if (!elem.id) elem.id = "prthEditor-"+(++autoIncrement);


        elem.oldHTML = elem.innerHTML;
        if (typeof newHtml !== "undefined" && newHtml !== null) elem.innerHTML = newHtml;

        elem.setAttribute("contentEditable", "");
        tinymce.init({
            selector: '#'+elem.id,
            forced_root_block : "",
            entity_encoding: "raw",
            inline: true,
            plugins: tinyMcePlugins,
            external_plugins: tinyMceExternalPlugins,
            toolbar: 'undo redo',
            menubar: false,
            setup: function(ed) {
                ed.on('init', function(e) {
                    elem.blur();
                    elem.focus();
                });
            }
        });
        if (!noFocus) elem.focus();
        return tinymce.get(elem.id);
    }

    function makeExcerptEditor(elem, postId, newHtml, noFocus) {
        return makeTitleEditor(elem, postId, newHtml, noFocus);
    }

    function getContent(elem) {
        return tinymce.get(elem.id).getContent();
    }

    function setContent(elem, content) {
        return tinymce.get(elem.id).setContent(content);
    }

    function getText(elem) {
        $(elem).text();
    }

    function cancelEdit(elem) {
        tinymce.remove('#'+elem.id);
        elem.removeAttribute("contentEditable");
        elem.innerHTML = elem.oldHTML;
    }

    function destroyEditor(elem) {
        tinymce.remove('#'+elem.id);
        elem.removeAttribute("contentEditable");
    }

    function addEditorListener(elem, on, func) {
        tinymce.get(elem.id).on(on, func);
    }

    function onEditorInsertImageClick(postId, elem, ed) {
        prth.openFileFrame(postId, 'image', false, function(attachment) {
            elem.style.opacity = 0.5;
            prthData.getAttachmentImage(attachment.id, function (image) {
                ed.insertContent('<a href="'+attachment.url+'">'+image+'</a>');
                elem.style.opacity = 1;
            }, function () {elem.style.opacity = 1;});
        });
    }

    function onEditorInsertFileClick(postId, elem, ed) {
        prth.openFileFrame(postId, null, false, function(attachment) {
            ed.insertContent('<a href="'+attachment.url+'" title="'+attachment.title+'">'+attachment.filename+'</a>');
        });
    }

    // codemirror source editing
    function openSourceDialog(elem) {
        var content = getContent(elem);

        var dialog = jQuery("<div class='prthSource'></div>");
        console.log(content);
        var codemirrorDoc;
        console.log(codemirrorDoc);
        var actions = {
            "Apply": function () {
                setContent(elem, codemirrorDoc.getValue());
            },
            "Close": function() {$( this ).dialog( "close" );}
        };
        var width, winWidth = $( window ).width();
        if (winWidth < 438) { width = winWidth*0.9; }
        else { width = 458; }
        var position;
        var elemOffset = jQuery(elem).offset();
        if (elemOffset.left > winWidth-(jQuery(elem).width()-elemOffset)) {
            position = {my: "right center", at: "left center-5%", of: elem}
        } else {
            position = {my: "left center", at: "right center-5%", of: elem}
        }
        dialog.dialog({
            width: 458,  height: 504,
            position: position,
            dialogClass: "prthEditDialog prthSourceDialog", modal: true,
            open: function(event, ui) {
                jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();
                $('.ui-widget-overlay').click(function() { dialog.dialog("close"); });
                codemirrorDoc = CodeMirror(dialog[0], {
                    value: content,
                    mode:  "htmlmixed",
                    lineNumbers: true,
                    styleActiveLine: true,
                    matchBrackets: true,
                    lineWrapping: true
                });
            },
            buttons: actions, title: "Source Editor", closeOnEscape: true
        });
    }

    prthEditor.makeContentEditor =  makeContentEditor;
    prthEditor.makeTitleEditor =  makeTitleEditor;
    prthEditor.makeExcerptEditor =  makeExcerptEditor;
    prthEditor.getContent =  getContent;
    prthEditor.getText =  getText;
    prthEditor.destroyEditor =  destroyEditor;
    prthEditor.addEditorListener =  addEditorListener;
    prthEditor.cancelEdit =  cancelEdit;
    prthEditor.openSourceDialog =  openSourceDialog;
})(jQuery);