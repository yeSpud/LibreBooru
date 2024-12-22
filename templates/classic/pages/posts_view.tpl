{if !$post["deleted"]}
    {if !$post["is_approved"]}
        <div class="error_warning">
            {$lang["this_post_is_awaiting_approval"]}
            {if in_array("moderate", $permissions) || in_array("admin", $permissions)}
                {replace s=$lang["approve_or_delete_post"] n=["[approve]", "[delete]"] r=['<a href="javascript:approvePost(\''|cat:$post["post_id"]|cat:'\')">'|cat:$lang["approve"]|cat:'</a>', '<a href="javascript:deletePost(\''|cat:$post["post_id"]|cat:'\')">'|cat:$lang["delete"]|cat:'</a>']}
            {/if}
        </div>
    {/if}
    {if isset($report)}
        <div class="error_warning">
            Post has been reported by <a href="/account.php?a=p&id={$report["user_id"]}"
                target="_blank">{$reporter["username"]}</a> for "{$report["reason"]}".
            <a href="javascript:approveReport('{$report["report_id"]}', '{$post["post_id"]}')">{$lang["approve"]}</a> -
            <a href="javascript:rejectReport('{$report["report_id"]}')">{$lang["reject"]}</a>
        </div>
    {/if}
    {if !$hideOriginalMessage}
        {if isset($post["has_thumbnail"])}
            <div class="error_info" id="original-message">
                {$lang["this_image_has_been_resized"]}
                {if $showOriginal}
                    {replace s=$lang["click_here_to_hide_the_original_image"] n="[here_to_hide]" r='<a href="javascript:toggleOriginal()" id="show-original">'|cat:$lang["here_to_hide"]|cat:'</a>'}
                {else}
                    {replace s=$lang["click_here_to_show_the_original_image"] n="[here_to_show]" r='<a href="javascript:toggleOriginal()" id="show-original">'|cat:$lang["here_to_show"]|cat:'</a>'}
                {/if}
                <a href="javascript:toggleAlwaysOriginal()"
                    id="always-original">{if $showOriginal}{$lang["always_view_cropped"]}{else}{$lang["always_view_original"]}{/if}</a>
                <a href="javascript:toggleHideOriginalMessage()">{$lang["dont_show_this_message_again"]}</a>
            </div>
        {/if}
    {/if}
    {if !empty($links)}
        <div style="margin-bottom: 6px;">
            {foreach from=$links item=sLink key=sKey name=sName}
                <a href="https://{if $sLink["type"] == "fanbox"}{$sLink["handle"]}.fanbox.cc{else}{if $sLink["type"] == "kofi"}ko-fi{else}{$sLink["type"]}{/if}.{if $sLink["type"] == "pixiv"}net/u{else}com{/if}/{$sLink["handle"]}{/if}"
                    target="_blank"><img src="/api/userbar.php?t={$sLink["type"]}&h={$sLink["handle"]}"></a>
            {/foreach}
        </div>
    {/if}

    {if isset($post["is_video"])}
        <video controls loop id="post_video">
            <source src="/uploads/videos/{$post["image_url"]}.{$post["file_extension"]}" type="video/mp4">
            {$lang["your_browser_does_not_support_the_video_tag"]}
        </video>
    {else}
        <img id="post_img"
            src="/uploads/{if $showOriginal}images{else}{if isset($post["has_thumbnail"])}crops{else}images{/if}{/if}/{$post["image_url"]}.{$post["file_extension"]}"
            alt="{if !empty($post["description"])}{$post["description"]} - {/if}{$post["tags"]}" class="post_image">
    {/if}

    <h3 class="m-0">
        <b>{if $canEdit}<a href="#edit-div"
                onclick="toggleEditDiv()">{$lang["edit"]}</a>{else}{$lang["no_permission_to_edit"]}
            {/if}</b>
        |
        <b>{if $canComment}<a
                href="javascript:toggleCommentDiv()">{$lang["comment"]}</a>{else}{$lang["no_permission_to_comment"]}
            {/if}</b>
    </h3>

    {if $canEdit}
        <div id="edit-div" style="display: none;">
            <form method="POST" name="update">
                <label for="source" class="small"><b>{$lang["source"]}</b> ({$lang["optional"]|lower})</label><br>
                <input type="text" name="source" id="source" value="{$post["source"]}" autocomplete="off" tabindex="1"><br>

                <label for="tags" class="small"><b>{$lang["tags"]}</b>
                    ({replace s=$lang["at_least_x"]|lower n="[count]" r=$config["upload_min_tags"]})</label><br>
                <textarea name="tags" id="tags" required cols="60" rows="5" onkeyup="tag_search(this)"
                    tabindex="2">{$post["tags"]}</textarea><br>
                <p class="p-0 m-0 small">{$lang["seperate_tags_with_spaces"]}</p>
                <p class="p-0 m-0 small">{$lang["you_can_use_prefixes"]}</p>

                <label for="rating" class="small"><b>{$lang["rating"]}</b></label><br>
                <select name="rating" id="rating" required tabindex="3">
                    <option value="safe" {if $post["rating"] == "safe"}selected{/if}>{$lang["safe"]}</option>
                    <option value="questionable" {if $post["rating"] == "questionable"}selected{/if}>{$lang["questionable"]}
                    </option>
                    <option value="explicit" {if $post["rating"] == "explicit"}selected{/if}>{$lang["explicit"]}</option>
                </select><br>

                <button type="submit" name="update" tabindex="4" class="mt-10">{$lang["edit"]}</button>
            </form>
        </div>
    {/if}

    {if $canComment}
        <div id="comment-div" style="display:none" class="mt-10">
            <form method="POST" name="comment">
                <textarea name="content" required cols="60" rows="3" tabindex="1"></textarea><br>

                <button type="submit" name="comment" tabindex="2" class="mt-10">{$lang["comment"]}</button>
            </form>
        </div>
    {/if}

    {if !empty($comments)}
        <p class="m-0 p-0 mb-10">{$commentsTotal} {$lang["comments"]}</p>
        {foreach from=$comments item=item key=key name=name}
            <p class="m-0 p-0" id="{$item["comment_id"]}" {if $item["deleted"]}style="color:red" {/if}>
                <b>
                    <a href="/account.php?a=p&id={$item["user_id"]}">{$item["username"]}</a>
                    <a href="#{$item["comment_id"]}"><span style="color:grey;">#{$item["comment_id"]}</span></a>
                    {replace s=$lang["at_timestamp"] n="[timestamp]" r=$item["timestamp"]} |
                    {$lang["score"]}: <span id="commentScore{$item["comment_id"]}">{$item["score"]}</span>
                    (<a href="javascript:voteComment('{$item["comment_id"]}', 'up');">{$lang["up"]|lower}</a> /
                    <a href="javascript:voteComment('{$item["comment_id"]}', 'down');">{$lang["down"]|lower}</a> /
                    <a href="javascript:voteComment('{$item["comment_id"]}', 'remove');">{$lang["remove"]|lower}</a>)
                    {if in_array("report", $permissions)}
                        |
                        {if $item["reportedStatus"] == "none"}
                            <a href="javascript:reportComment('{$item["comment_id"]}')">{$lang["report_to_moderation"]}</a>
                        {elseif $item["reportedStatus"] == "reported"}
                            {$lang["flagged_for_deletion"]}
                        {elseif $item["reportedStatus"] == "approved" && (in_array("moderate", $permissions) || in_array("admin", $permissions))}
                            <a href="/admin.php?a=r&t=c&s=all&f={$item["comment_id"]}">{$lang["view_report"]}</a>
                        {else}
                            {$lang["report_was_denied"]}
                        {/if}
                    {else}
                        {$lang["no_permission_to_report"]}
                    {/if}
                    {* Do I really need this? Idk... I'm too lazy :P ~5ynchro *}
                    {* {if isset($user["user_id"]) && $user["user_id"] == $item["user_id"] || in_array("moderate", $permissions) || in_array("admin", $permissions)}
                        |
                        <a href="javascript:editComment('{$item["comment_id"]}')">{$lang["edit"]}</a> /
                        <a href="javascript:deleteComment('{$item["comment_id"]}')">{$lang["delete"]}</a>
                    {/if} *}
                </b>
            </p>
            {$item["content"]}
            {if !$smarty.foreach.name.last}
                <hr>
            {/if}
        {/foreach}
    {/if}
{else}
    <div class="error" id="original-message">
        {replace s=$lang["this_post_has_been_deleted_reason"] n="[reason]" r=$post["deleted_message"]}.
    </div>
{/if}