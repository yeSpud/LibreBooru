<div class="container">
    <div class="title_menu">
        <h1><a href="/index.php">{$config["sitename"]}</a></h1>
        <noscript>
            <p class="p-0 m-0" style="margin-top: -10px">
                <small><b><i>{$lang["for_the_best_experience_enable_javascript"]}</i></b></small>
            </p>
        </noscript>
    </div>
    <div class="top_menu">
        <a href="/account.php?a=i" {if $activePage == "account"}class="active" {/if}>{$lang["my_account"]}</a>
        <a href="/posts.php?a=i" {if $activePage == "browse"}class="active" {/if}>{$lang["browse"]}</a>
        <a href="/extra.php?a=p">{$lang["playlist"]}</a>
        <a href="/extra.php?a=c">{$lang["comments"]}</a>
        <a href="/wiki.php?a=i" {if $activePage == "wiki"}class="active" {/if}>{$lang["wiki"]}</a>
        <a href="/extra.php?a=t" {if $activePage == "tags"}class="active" {/if}>{$lang["tags"]}</a>
        <a href="/forum.php?a=i">{$lang["forum"]}</a>
        <a href="/extra.php?a=s">{$lang["stats"]}</a>
        <a href="/extra.php?a=h">{$lang["help"]}</a>
        {if in_array("moderate", $permissions) || in_array("admin", $permissions)}
            <a href="/admin.php?a=i" {if $activePage == "admin"}class="active" {/if}>{$lang["admin_panel"]}</a>
        {/if}
    </div>
    <div class="sub_menu">
        <div class="items">
            {if $activePage == "account"}
                <a href="/account.php?a=i">{$lang["home"]}</a>
                {if $logged}
                    <a href="/account.php?a=p&id={$user["user_id"]}">{$lang["my_profile"]}</a>
                    <a href="/mail.php?a=i">{$lang["my_mail"]}</a>
                    <a href="/account.php?a=f&id={$user["user_id"]}">{$lang["my_favourites"]}</a>
                {else}
                    <a href="/account.php?a=l">{$lang["login"]}</a>
                    <a href="/account.php?a=r">{$lang["register"]}</a>
                {/if}
                <a href="/account.php?a=o">{$lang["options"]}</a>
            {elseif $activePage == "browse"}
                <a href="/posts.php?a=s&t=video">{$lang["video"]}</a>
                <a href="/posts.php?a=a">{$lang["upload"]}</a>
                <a
                    href="/{if $logged}account.php?a=f&id={$user["user_id"]}{else}account.php?a=l{/if}">{$lang["my_favourites"]}</a>
                <a href="/posts.php?a=r">{$lang["random"]}</a>
                {if !empty($config["contact"])}
                    <a href="mailto:{$config["contact"]}" target="_blank">{$lang["contact_us"]}</a>
                {/if}
                <a href="/extra.php?a=a">{$lang["about"]}</a>
                <a href="/extra.php?a=h">{$lang["help"]}</a>
                <a href="/extra.php?a=o">{$lang["tos"]}</a>
            {elseif $activePage == "wiki"}
                <a href="/wiki.php?a=i">{$lang["list"]}</a>
                {if isset($term)}
                    |
                    <a href="/wiki.php?a=t&t={$term["wiki_term"]}">{$lang["view"]}</a>
                    <a href="/wiki.php?a=e&t={$term["wiki_term"]}">{$lang["edit"]}</a>
                    <a href="/wiki.php?a=h&t={$term["wiki_term"]}">{$lang["history"]}</a>
                    |
                    <a href="/posts.php?a=s&t={$term["wiki_term"]}">{$lang["browse"]}</a>
                {/if}
            {elseif $activePage == "tags"}
                <a
                    href="/extra.php?a=t&t={$searchTerm}&s={$smarty["get"]["s"] ?? "u"}&o={$smarty["get"]["o"] ?? "a"}&p={$smarty["get"]["p"] ?? 1}">{$lang["list"]}</a>
            {elseif $activePage == "admin"}
                <a href="/admin.php?a=i">{$lang["home"]}</a>
                <a href="/admin.php?a=u">{$lang["update"]}</a>
                <a href="/posts.php?a=s&t=status:awaiting">{$lang["approval_queue"]}</a>
                <a href="/admin.php?a=r">{$lang["reports"]}</a>
            {/if}
        </div>
    </div>
</div>