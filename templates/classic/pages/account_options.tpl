<h2>Options</h2>

<form name="save_options" method="POST" class="mt-10">
    <label for="tag_blacklist" class="small"><b>{$lang["tag_blacklist"]}</b></label><br>
    <textarea name="tag_blacklist" id="tag_blacklist" cols="60" rows="5" tabindex="1"
        onkeyup="tag_search(this)">{$user["tag_blacklist"]}</textarea><br>
    <p class="p-0 m-0 small">{$lang["seperate_tags_with_spaces"]}</p>
    <p class="p-0 m-0 small">{$lang["supports_wildcard"]}</p>

    <label for="default_rating" class="small"><b>{$lang["default_rating"]}</b></label><br>
    <select name="default_rating" id="default_rating" tabindex="2">
        <option value="all" {if $user["default_rating"] == "all"}selected{/if}>{$lang["all"]}</option>
        <option value="safe" {if $user["default_rating"] == "safe"}selected{/if}>{$lang["safe"]}</option>
        <option value="safequestionable" {if $user["default_rating"] == "safequestionable"}selected{/if}>
            {$lang["safequestionable"]}</option>
        <option value="safeexplicit" {if $user["default_rating"] == "safeexplicit"}selected{/if}>
            {$lang["safeexplicit"]}</option>
        <option value="questionable" {if $user["default_rating"] == "questionable"}selected{/if}>{$lang["questionable"]}
        </option>
        <option value="questionableexplicit" {if $user["default_rating"] == "questionableexplicit"}selected{/if}>
            {$lang["questionableexplicit"]}</option>
        <option value="explicit" {if $user["default_rating"] == "explicit"}selected{/if}>{$lang["explicit"]}</option>
    </select><br>
    <p class="p-0 m-0 small">{$lang["show_only_posts_with_this_rating"]}</p>

    <label class="small"><b>{$lang["original_images"]}</b></label><br>
    <label>
        <input type="checkbox" name="show_message" id="show_message" value="1" tabindex="3"
            {if !isset($smarty["cookies"]["hideOriginalMessage"])}checked{/if}>
        {$lang["show_message"]}
    </label><br>
    <label>
        <input type="checkbox" name="always_show_original" value="1" id="always_show_original" tabindex="4"
            {if isset($smarty["cookies"]["showOriginal"])}checked{/if}>
        {$lang["always_show_original_image"]}
    </label><br>

    <button type="submit" name="save_options" tabindex="5" class="mt-10">{$lang["save"]}</button>
</form>