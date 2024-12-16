{include file="parts/header.tpl"}

{include file="parts/menu.tpl"}

<div class="content">
    {if $action == "t"}
        {if isset($tag)}
            <form method="POST" name="" action="/extra.php">
                <input type="hidden" name="a" value="t">
                <input type="hidden" name="s" value="{$smarty["get"]["s"] ?? "u"}">
                <input type="hidden" name="o" value="{$smarty["get"]["o"] ?? "d"}">
                <input type="hidden" name="p" value="{$smarty["get"]["p"] ?? 1}">
                <input type="hidden" name="t" value="{$searchTerm}">
                <input type="text" name="n" value="{$tag["tag_name"]}" readonly class="mr-5">
                <input type="submit" name="edit" value="{$lang["edit"]}" class="mr-5">
                <input type="submit" name="cancel" value="{$lang["cancel"]}"><br>
                <select name="c" class="mt-10">
                    <option value="copyright" {if $tag["category"] == "copyright"}selected{/if}>{$lang["copyright"]}</option>
                    <option value="character" {if $tag["category"] == "character"}selected{/if}>{$lang["character"]}</option>
                    <option value="artist" {if $tag["category"] == "artist"}selected{/if}>{$lang["artist"]}</option>
                    <option value="general" {if $tag["category"] == "general"}selected{/if}>{$lang["general"]}</option>
                    <option value="meta" {if $tag["category"] == "meta"}selected{/if}>{$lang["meta"]}</option>
                    <option value="other" {if $tag["category"] == "other"}selected{/if}>{$lang["other"]}</option>
                </select>
                {if in_array("moderate", $permissions) || in_array("admin", $permissions)}
                    <label>
                        <input type="checkbox" name="l" value="1" {if $tag["locked"]}checked{/if}>
                        {$lang["locked"]}
                    </label>
                {/if}
            </form>
        {else}
            <form method="GET" name="search" action="/extra.php">
                <input type="hidden" name="a" value="t">
                <input type="text" name="t" class="mr-5" value="{$searchTerm}" onkeyup="tag_search(this)" autocomplete="off">
                <input type="submit" value="{$lang["search"]}"><br>
                <select name="s" class="mt-5">
                    <option value="n" {if isset($smarty["get"]["s"]) && $smarty["get"]["s"] == "n"}selected{/if}>{$lang["name"]}
                    </option>
                    <option value="u"
                        {if (isset($smarty["get"]["s"]) && $smarty["get"]["s"] == "u") || !isset($smarty["get"]["s"])}selected{/if}>
                        {$lang["updated"]}</option>
                    <option value="c" {if isset($smarty["get"]["s"]) && $smarty["get"]["s"] == "c"}selected{/if}>
                        {$lang["total_count"]}</option>
                </select>
                <label>
                    <input type="radio" name="o" value="a"
                        {if isset($smarty["get"]["o"]) && $smarty["get"]["o"] == "a"}checked{/if}>
                    {$lang["ascending"]}
                </label>
                <label>
                    <input type="radio" name="o" value="d"
                        {if (isset($smarty["get"]["o"]) && $smarty["get"]["o"] == "d") || !isset($smarty["get"]["o"])}checked{/if}>
                    {$lang["descending"]}
            </form>

            <table class="history_table mt-10">
                <tr>
                    <th>{$lang["posts"]}</th>
                    <th>{$lang["name"]}</th>
                    <th>{$lang["category"]}</th>
                </tr>
                {foreach $tags as $tag}
                    <tr>
                        <td>{$tag["post_count"]}</td>
                        <td><a href="/posts.php?a=s&t={$tag["tag_name"]}" class="tag_{$tag["category"]}">{$tag["tag_name"]}</a></td>
                        <td>{$tag["category"]|capitalize}
                            {if in_array("tag", $permissions) && (!$tag["locked"] || ($tag["locked"] && (in_array("moderate", $permissions) || in_array("admin", $permissions))))}(<a
                                href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "d"}&p={$page}&e={$tag["tag_name"]}">{$lang["edit"]|lower}</a>){/if}
                        </td>
                    </tr>
                {/foreach}
            </table>

            <div class="pagination">
                {if $page > 1}
                    <a href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "d"}&p=1">&laquo;
                        {$lang["first"]}</a>
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "d"}&p={$page - 1}">{$lang["previous"]}</a>
                {/if}

                {if $page < $totalPages}
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "d"}&p={$page + 1}">{$lang["next"]}</a>
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "d"}&p={$totalPages}">{$lang["last"]}
                        &raquo;</a>
                {/if}
            </div>
        {/if}
    {elseif $action == "c"}
        <form method="GET" action="/extra.php" class="home_search mb-5">
            <input type="hidden" name="a" value="c">
            <input type="text" name="s" {if isset($searchTerm)}value="{$searchTerm}" {/if} onkeyup="wiki_search(this)"
                autocomplete="off">
            <button type="submit" class="mt-5">{$lang["search"]}</button>
        </form>
        {if empty($comments)}
            <p class="m-0 mt-10">{$lang["nobody_here_but_us_ravens"]}</p>
        {else}
            {foreach from=$comments item=item key=key name=name}
                <div class="comment_gallery">
                    <div class="column">
                        <a href="/posts.php?a=p&id={$item.post_id}">
                            <img src="/uploads/thumbs/{if $item.deleted && (in_array("moderate", $permissions) && in_array("admin", $permissions))}.{/if}{$item.image_url}.{if in_array($item.file_extension, ["mp4", "mkv", "webm"])}jpg{else}{$item.file_extension}{/if}"
                                alt="Post"
                                class="{if !$item.is_approved}post_awaiting{elseif $item.deleted}post_deleted{else}{if $item.file_extension == "gif"}post_gif{/if}{if in_array($item.file_extension, ["mp4", "mkv", "webm"])}post_video{/if}{/if}">
                        </a>
                    </div>
                    <div class="column">
                        <div class="text">
                            <p class="m-0 p-0" id="{$item["comment_id"]}" {if $item["cdeleted"]}style="color:red" {/if}>
                                <b>
                                    <a href="/account.php?a=p&id={$item["user_id"]}" target="_blank">{$item["username"]}</a>
                                    <a href="/posts.php?a=p&id={$item.post_id}#{$item["comment_id"]}"><span
                                            style="color:grey;">#{$item["comment_id"]}</span></a>
                                    on
                                    <a href="/posts.php?a=p&id={$item.post_id}">#{$item.post_id}</a>
                                    {replace s=$lang["at_timestamp"] n="[timestamp]" r=$item["timestamp"]} |
                                    {$lang["score"]}: <span id="commentScore{$item["comment_id"]}">{$item["score"]}</span>
                                    (<a href="javascript:voteComment('{$item["comment_id"]}', 'up');">{$lang["up"]|lower}</a> /
                                    <a href="javascript:voteComment('{$item["comment_id"]}', 'down');">{$lang["down"]|lower}</a> /
                                    <a
                                        href="javascript:voteComment('{$item["comment_id"]}', 'remove');">{$lang["remove"]|lower}</a>)
                                    |
                                    {if in_array("report", $permissions)}
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
                        </div>
                    </div>
                </div>
            {/foreach}
        {/if}

        <div class="pagination">
            {if $page > 1}
                <a href="/extra.php?a=c&s={$searchTerm}&p=1">&laquo; {$lang["first"]}</a>
                <a href="/extra.php?a=c&s={$searchTerm}&p={$page - 1}">{$lang["previous"]}</a>
            {/if}

            {if $page < $totalPages}
                <a href="/extra.php?a=c&s={$searchTerm}&p={$page + 1}">{$lang["next"]}</a>
                <a href="/extra.php?a=c&s={$searchTerm}&p={$totalPages}">{$lang["last"]} &raquo;</a>
            {/if}
        </div>
    {/if}
</div>

{include file="parts/footer.tpl"}