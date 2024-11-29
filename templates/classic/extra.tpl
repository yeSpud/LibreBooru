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
                                href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p={$page}&e={$tag["tag_name"]}">{$lang["edit"]|lower}</a>){/if}
                        </td>
                    </tr>
                {/foreach}
            </table>

            <div class="pagination">
                {if $page > 1}
                    <a href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p=1">&laquo;
                        {$lang["first"]}</a>
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p={$page - 1}">{$lang["previous"]}</a>
                {/if}

                {if $page < $totalPages}
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p={$page + 1}">{$lang["next"]}</a>
                    <a
                        href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p={$totalPages}">{$lang["last"]}
                        &raquo;</a>
                {/if}
            </div>
        {/if}
    {/if}
</div>

{include file="parts/footer.tpl"}