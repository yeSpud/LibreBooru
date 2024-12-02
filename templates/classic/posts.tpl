{include file="parts/header.tpl"}

{include file="parts/menu.tpl"}

{if $action == "a"}
    <div class="content">
        {if isset($errors) && !empty($errors)}
            {foreach from=$errors item=item key=key name=name}
                <div class="error">{$lang["error"]}: {$item}</div>
            {/foreach}
        {/if}

        {include file="pages/posts_add.tpl"}
    </div>
{elseif $action == "h"}
    <div class="content">
        <h3 class="m-0 p-0 mb-10">{$lang["tag_history"]}: <a href="/posts.php?a=p&id={$id}">#{$id}</a></h3>
        <table class="history_table">
            <tr>
                <th style="width:1%">Commit</th>
                <th>{$lang["timestamp"]}</th>
                <th>{$lang["user"]}</th>
                <th>{$lang["tags"]}</th>
            </tr>
            {foreach from=$history item=item key=key name=name}
                <tr>
                    <td>{$key}</td>
                    <td>{$item["timestamp"]}</td>
                    <td><a href="/account.php?a=p&id={$item["user_id"]}">{$item["user"]}</a></td>
                    <td>
                        {foreach from=$item["tags"] item=tag key=tagKey name=tag}
                            <span class="tag_{$tag["category"]}">
                                {if $tag["action"] == "stay"}
                                    <span class="small">{$tag["tag_name"]}</span>
                                {elseif $tag["action"] == "remove"}
                                    <s>{$tag["tag_name"]}</s>
                                {else}
                                    {$tag["tag_name"]}
                                {/if}
                            </span>
                        {/foreach}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
{else}
    <div class="with_sidebar">
        <div class="sidebar content pr-0">
            <form method="GET" action="/posts.php" class="home_search mb-5">
                <input type="hidden" name="a" value="s">
                <input type="text" name="t" class="w-full" {if isset($searchTerm)}value="{$searchTerm}" {/if}
                    onkeyup="tag_search(this)" autocomplete="off">
                <button type="submit" class="w-full mt-5" style="width: 103%">{$lang["search"]}</button>
                <noscript>
                    <p class="small mt-5">{$lang["trouble_with_your_blacklist"]}</p>
                </noscript>
            </form>
            <!-- I could possibly refactor the tags section, but oh well -->
            {if isset($allTags) && !empty($allTags)}
                <h3 class="m-0 p-0"><b>{$lang["tags"]}</b></h3>
                {if isset($allTags["copyright"])}
                    <p class="m-0 p-0"><b>{$lang["copyright"]}</b></p>
                    {foreach from=$allTags["copyright"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_copyright">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
                {if isset($allTags["character"])}
                    <p class="m-0 p-0"><b>{$lang["character"]}</b></p>
                    {foreach from=$allTags["character"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_character">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
                {if isset($allTags["artist"])}
                    <p class="m-0 p-0"><b>{$lang["artist"]}</b></p>
                    {foreach from=$allTags["artist"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_artist">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
                {if isset($allTags["general"])}
                    <p class="m-0 p-0"><b>{$lang["general"]}</b></p>
                    {foreach from=$allTags["general"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_general">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
                {if isset($allTags["meta"])}
                    <p class="m-0 p-0"><b>{$lang["meta"]}</b></p>
                    {foreach from=$allTags["meta"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_meta">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
                {if isset($allTags["other"])}
                    <p class="m-0 p-0"><b>{$lang["other"]}</b></p>
                    {foreach from=$allTags["other"] item=item key=key name=name}
                        <p class="m-0 p-0">
                            <a href="/wiki.php?a=t&t={$item["name"]}">?</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+{$item["name"]}">+</a>
                            <a href="/posts.php?a=s&t={$searchTerm}+-{$item["name"]}">-</a>
                            <a href="/posts.php?a=s&t={$item["name"]}" class="tag_other">{$item["name"]}</a> ({$item["count"]})
                        </p>
                    {/foreach}
                {/if}
            {/if}
            {if $action == "p"}
                <h3 class="m-0 mt-5 p-0"><b>{$lang["statistics"]}</b></h3>
                <p class="m-0 p-0">ID: {$id}</p>
                <p class="m-0 p-0">{$lang["posted"]}: {$post["post_date"]}</p>
                <p class="m-0 p-0">{$lang["by"]}: <a
                        href="/account.php?a=p&id={$uploader["user_id"]}">{$uploader["username"]}</a></p>
                <p class="m-0 p-0">{$lang["size"]}: {$resolution}</p>
                {if !empty($post["source"])}
                    <p class="m-0 p-0">{$lang["source"]}: <a href="{$post["source"]}" target="_blank">{$lang["link"]}</a></p>
                {/if}
                <p class="m-0 p-0">{$lang["rating"]}: {$post["rating"]|capitalize}</p>
                <p class="m-0 p-0">{$lang["score"]}: <span id="postScore">{$score}</span>
                    (<a href="javascript:votePost('{$id}', 'up')">{$lang["up"]|lower}</a> / <a
                        href="javascript:votePost('{$id}', 'down')">{$lang["down"]|lower}</a> / <a
                        href="javascript:votePost('{$id}', 'remove')">{$lang["remove"]|lower}</a>)</p>

                <h3 class="m-0 mt-5 p-0"><b>{$lang["options"]}</b></h3>
                {if !$post["deleted"]}
                    <p class="m-0 p-0">{if $canEdit}<a href="#edit-div" onclick="toggleEditDiv()">{$lang["edit"]}</a>
                        {else}{$lang["no_permission_to_edit"]}
                        {/if}</p>
                    <p class="m-0 p-0"><b><a href="/uploads/{$type}/{$post.image_url}.{$post.file_extension}" target="_blank"
                                id="originalImageLink">{$lang["original_image"]}</a></b></p>
                    {if in_array("report", $permissions)}
                        {if $reportedStatus == "none"}
                            <p class="m-0 p-0"><a href="javascript:reportPost('{$id}')">{$lang["report_to_moderation"]}</a></p>
                        {elseif $reportedStatus == "reported"}
                            <p class="m-0 p-0">{$lang["flagged_for_deletion"]}</p>
                        {else}
                            <p class="m-0 p-0">{$lang["report_was_denied"]}</p>
                        {/if}
                    {else}
                        <p class="m-0 p-0">{$lang["no_permission_to_report"]}</p>
                    {/if}
                    <p class="m-0 p-0"><a href="javascript:toggleFavourite('{$id}')"
                            id="favouriteText">{if $favourited}{$lang["remove_from_favourites"]}
                            {else}{$lang["add_to_favourites"]}
                            {/if}</a></p>
                    {if $canDelete}
                        <p class="m-0 p-0"><a href="javascript:deletePost('{$id}')">{$lang["delete"]}</a></p>
                    {/if}
                {else}
                    {if $canDelete}
                        <p class="m-0 p-0"><a href="javascript:restorePost('{$id}')">{$lang["restore"]}</a></p>
                    {/if}
                {/if}

                <h3 class="m-0 mt-5 p-0">{$lang["history"]}</h3>
                <p class="m-0 p-0"><a href="/posts.php?a=h&id={$id}">{$lang["tags"]}</a>

                <h3 class="m-0 mt-5 p-0">{$lang["related"]}</h3>
                {if isset($previousId)}
                    <p class="m-0 p-0"><a href="/posts.php?a=p&id={$previousId}" id="previousPost">{$lang["previous"]}</a></p>
                {/if}
                {if isset($nextId)}
                    <p class="m-0 p-0"><a href="/posts.php?a=p&id={$nextId}" id="nextPost">{$lang["next"]}</a></p>
                {/if}
                {if !$post["deleted"]}
                    <p class="m-0 p-0">SauceNAO</p>
                    <p class="m-0 p-0">IQDB</p>
                    <p class="m-0 p-0">Waifu2x</p>
                {/if}
            {/if}
        </div>
        <div class="main_content content pl-7">
            {if isset($errors) && !empty($errors)}
                {foreach from=$errors item=item key=key name=name}
                    <div class="error">{$lang["error"]}: {$item}</div>
                {/foreach}
            {/if}

            {if $action == "i" || $action == "s"}
                {include file="pages/posts_index.tpl"}
            {elseif $action == "p"}
                {include file="pages/posts_view.tpl"}
            {/if}
        </div>
    </div>
{/if}

{include file="parts/footer.tpl"}