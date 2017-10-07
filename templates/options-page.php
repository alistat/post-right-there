<style>
    label {
        font-weight: bold;
        font-size: 1.1em;
        margin-right: 1em;
    }
</style>

<h1 style="margin: 1.5em 0 1em;">Post Right There Settings</h1>

<div style="margin: 0 0 1.5em 0.5em;padding: 0.2em 1.5em;border-left: 0.3em solid darkgray;">
    <span style="font-style: normal;opacity: 0.7;">For more information visit
        <a style="font-style: italic;" href="https://alistat.eu/wordpress/post-right-there" target="_blank">alistat.eu/wordpress/post-right-there</a>
    </span><br/>
    <span style="font-style: normal;opacity: 0.7;">Icon pack by
        <a href="https://icons8.com" target="_blank" style="font-style: italic;">Icons8</a>
    </span>
</div>


<div id="prthSettings" style="margin: 0.5em 0 2em;">
    <label for="postTypes">
        Disable Rich Front Post for post types
    </label>
    <select id="postTypes" data-placeholder="Post types to exclude" multiple style="min-width: 20em;" vf-model="atom:postTypes">
        <?php
            foreach (get_post_types(array(), "objects") as $post_type) {
                echo "<option value='{$post_type->name}'>".esc_html($post_type->labels->singular_name)."</option>";
            }
        ?>
    </select>
</div>
<button type="button" onclick="save(this);">Save</button>


<script>
    <?php $settings= json_encode(prthGetSettings());  ?>
    var settings = <?php echo $settings ?>;
    jQuery("#postTypes").val(settings.postTypes).chosen();

    function save(target) {
        target.setAttribute("disabled", "disabled");
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {settings: JSON.stringify(vf.getModel(document.getElementById('prthSettings'))), action:"prth_save_settings"},
            success: function () {target.removeAttribute("disabled");},
            error: function (xhr, ign, status) {alert("Something went wrong "+status+" "+xhr.responseText); target.removeAttribute("disabled")}
        });
    }
</script>