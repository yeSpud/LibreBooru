{include file="parts/header.tpl"}

{include file="parts/menu.tpl"}

{if $action !== "h"}
    <div class="with_sidebar">
        <div class="sidebar content pr-0">
            <h3 class="m-0 p-0"><b>{$lang["recent_changes"]}</b></h3>
            {foreach from=$updateHistory item=item key=key name=name}
                <p class="m-0 p-0"><a href="/wiki.php?a=t&t={$item["wiki_term"]}"
                        class="tag_{$item["category"]}">{$item["wiki_term"]}</a></p>
            {/foreach}
        </div>
    {/if}
    <div class="main_content content">
        <form method="GET" action="/wiki.php" class="home_search mb-5">
            <input type="hidden" name="a" value="i">
            <input type="text" name="t" {if isset($searchTerm)}value="{$searchTerm}" {/if} onkeyup="wiki_search(this)"
                autocomplete="off">
            <button type="submit" class="mt-5">{$lang["search"]}</button>
            <noscript>
                <p class="small mt-5">{$lang["enable_javascript_to_enhance_your_search"]}</p>
            </noscript>
        </form>
        {if $action == "i"}
            <h2>{$lang["wiki"]}</h2>
            {if empty($terms)}
                {if $page == 1}
                    {if !empty($searchTerm)}
                        <p class="mt-5">{replace s=$lang["no_results_found_for_x"] n="[query]" r="<b>{$searchTerm}</b>"}
                            <a href="/wiki.php?a=e&t={$searchTerm}">{$lang["want_to_create_it"]}</a>
                        </p>
                    {/if}
                {else}
                    <p class="mt-5">{$lang["no_terms_on_this_page"]}</p>
                {/if}
            {else}
                <table class="history_table mt-10">
                    <tr>
                        <th>{$lang["tag"]}</th>
                        <th style="text-align: right;">{$lang["updated"]}</th>
                    </tr>
                    {foreach $terms as $term}
                        <tr>
                            <td>
                                <a href="/wiki.php?a=t&t={$term["wiki_term"]}"
                                    class="tag_{$term["category"]}">{$term["wiki_term"]}</a>
                            </td>
                            <td style="text-align: right;">{replace s=$lang["updated_at_x"] n="[date]" r=$term["lastUpdated"]}</td>
                        </tr>
                    {/foreach}
                </table>
            {/if}

            <div class="pagination">
                {if $page > 1}
                    <a href="/wiki.php?a=i&t={$searchTerm}&p=1">&laquo; {$lang["first"]}</a>
                    <a href="/wiki.php?a=i&t={$searchTerm}&p={$page - 1}">{$lang["previous"]}</a>
                {/if}

                {if $page < $totalPages}
                    <a href="/wiki.php?a=i&t={$searchTerm}&p={$page + 1}">{$lang["next"]}</a>
                    <a href="/wiki.php?a=i&t={$searchTerm}&p={$totalPages}">{$lang["last"]} &raquo;</a>
                {/if}
            </div>
        {elseif $action == "t"}
            <h2><a href="/wiki.php?a=i">{$lang["wiki"]}</a>: {$term["wiki_term"]}</h2>
            <p class="m-0 mt-7 p-0"><b>{$lang["category"]}: {$category}</b></p>
            <p>
                {$term["content"]}
            </p>
            <hr>
            <p>
                <i>
                    {if $term["locked"]}
                        {$lang["this_term_has_been_locked_by_a_moderator"]}
                    {else}
                        {$lang["this_term_is_open_for_editing"]}
                    {/if}
                    <br>
                    {replace s=$lang["created_on_x_by_y"] n=["[date]", "[username]"] r=[$term["creation_date"], "<a href='/account.php?a=p&id={$term["user_id"]}'>{$creator}</a>"]}<br>
                    {replace s=$lang["last_updated_on_x_by_y"] n=["[date]", "[username]"] r=[$last_updated["timestamp"], "<a href='/account.php?a=p&id={$last_updated["user_id"]}'>{$last_updated["username"]}</a>"]}
                </i>
            </p>
        {elseif $action == "e"}
            <h2>{$lang["editing"]}: <a href="/wiki.php?a=t&t={$_term}">{$_term}</a></h2>
            <form method="POST" name="edit" class="mt-10">
                <label for="content" class="small"><b>{$lang["content"]}</b></label><br>
                <textarea name="content" required cols="60" rows="8" tabindex="1"
                    id="content">{if isset($term["content"])}{$term["content"]}{/if}</textarea><br>

                {if $tag["category"] == "artist"}
                    <label for="pixiv" class="small"><b>Pixiv ID</b> ({$lang["optional"]|lower})</label><br>
                    <input type="text" name="pixiv_id" id="pixiv" value="{if isset($term["pixiv_id"])}{$term["pixiv_id"]}{/if}"
                        autocomplete="off" tabindex="2"><br>

                    <label for="fanbox" class="small"><b>Fanbox ID</b> ({$lang["optional"]|lower})</label><br>
                    <input type="text" name="fanbox_id" id="fanbox"
                        value="{if isset($term["fanbox_id"])}{$term["fanbox_id"]}{/if}" autocomplete="off" tabindex="3"><br>

                    <label for="patreon" class="small"><b>Patreon</b> ({$lang["optional"]|lower})</label><br>
                    <input type="text" name="patreon" id="patreon" value="{if isset($term["patreon"])}{$term["patreon"]}{/if}"
                        autocomplete="off" tabindex="4"><br>

                    <label for="kofi" class="small"><b>Ko-Fi</b> ({$lang["optional"]|lower})</label><br>
                    <input type="text" name="kofi" id="kofi" value="{if isset($term["kofi"])}{$term["kofi"]}{/if}"
                        autocomplete="off" tabindex="5"><br>

                    <label for="twitter" class="small"><b>Twitter</b> ({$lang["optional"]|lower})</label><br>
                    <input type="text" name="twitter_id" id="twitter"
                        value="{if isset($term["twitter_id"])}{$term["twitter_id"]}{/if}" autocomplete="off" tabindex="6"><br>
                {/if}

                {if $isMod}
                    <label class="small"><input type="checkbox" name="locked" value="1"
                            {if isset($term["locked"]) && $term["locked"] == 1}checked{/if} tabindex="7">
                        <b>{$lang["locked"]}</b></label><br>
                {/if}

                <button type="submit" name="edit" tabindex="8" class="mt-10">{$lang["edit"]}</button>
            </form>
        {elseif $action == "h"}
            <h2>{$lang["history"]}: <a href="/wiki.php?a=t&t={$term["wiki_term"]}">{$term["wiki_term"]}</a></h2>
            <table class="history_table mt-10">
                <tr>
                    <th>{$lang["user"]}</th>
                    <th>{$lang["content"]}</th>
                    <th>{$lang["timestamp"]}</th>
                </tr>
                {foreach $history as $entry}
                    <tr>
                        <td><a href="/account.php?a=p&id={$entry["user_id"]}">{$entry["username"]}</a></td>
                        <td>{$entry["old_content"]}</td>
                        <td>{$entry["timestamp"]}</td>
                    </tr>
                {/foreach}
            </table>
        {/if}
    </div>
    {if $action !== "h"}
    </div>
{/if}

{include file="parts/footer.tpl"}