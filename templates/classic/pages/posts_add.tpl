<h2>{$lang["upload"]}</h2>

<h2 style="margin-top: 30px;">{$lang["guidelines"]}</h2>
{if !isset($guidelines) || empty($guidelines)}
    <p>{$lang["there_are_no_guidelines_avaliable"]}</p>
{else}
    <p>{$lang["before_uploading_please_read_the_guidelines"]}</p>
    {$guidelines}
{/if}

<form method="POST" name="upload" enctype="multipart/form-data">
    <label for="file" class="small"><b>{$lang["file"]}</b> ({$lang["supports"]|lower}: {$file_string}) ({$lang["max"]}
        {$max_size})</label><br>
    <input type="file" name="file" id="file" required tabindex="1"><br>

    <label for="source" class="small"><b>{$lang["source"]}</b> ({$lang["optional"]|lower})</label><br>
    <input type="text" name="source" id="source" value="" autocomplete="off" tabindex="2"><br>

    <label for="tags" class="small"><b>{$lang["tags"]}</b>
        ({replace s=$lang["at_least_x"]|lower n="[count]" r=$config["upload_min_tags"]})</label><br>
    <textarea name="tags" id="tags" required cols="60" rows="5" onkeyup="tag_search(this)" tabindex="3"></textarea><br>
    <p class="p-0 m-0 small">{$lang["seperate_tags_with_spaces"]}</p>
    <p class="p-0 m-0 small">{$lang["you_can_use_prefixes"]}</p>

    <label for="rating" class="small"><b>{$lang["rating"]}</b></label><br>
    <select name="rating" id="rating" required tabindex="4">
        <option value="safe">{$lang["safe"]}</option>
        <option value="questionable" selected>{$lang["questionable"]}</option>
        <option value="explicit">{$lang["explicit"]}</option>
    </select><br>

    <p class="small m-0 mt-10"><b>{$lang["if_your_post_is_not_listed_right_away"]}</b></p>

    <button type="submit" name="upload" tabindex="5">{$lang["upload"]}</button>
</form>