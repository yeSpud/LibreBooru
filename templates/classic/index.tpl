{include file="parts/header.tpl"}

<div class="container">
    <div class="home_container">
        <h1><a href="/posts.php?a=i">{$config["sitename"]}</a></h1>
        <div class="home_menu">
            <a href="/posts.php?a=i">{$lang["browse"]}</a>
            <a href="/extra.php?a=c">{$lang["comments"]}</a>
            <a href="/forum.php?a=i">{$lang["forum"]}</a>
            <a href="/account.php?a=i">{$lang["my_account"]}</a>
        </div>
        <form method="GET" action="/posts.php" class="home_search">
            <input type="hidden" name="a" value="s">
            <input type="text" name="t" class="w-full mr-5" onkeyup="tag_search(this)" autocomplete="off">
            <button type="submit">{$lang["search"]}</button>
        </form>
        <div>
            <noscript><small><b><i>{$lang["for_the_best_experience_enable_javascript"]}</i></b></small><br></noscript>
            <small>{replace s=$lang["serving_x_posts_so_far"] n="[count]" r=$postCount}</small>
            <small><i>{replace s=$lang["openbooru_rocks_it"] n="[OpenBooru]" r='<a href="https://github.com/5ynchrogazer/OpenBooru" target="_blank">OpenBooru</a>'}</i></small>
        </div>
        <div>
            <br>
            <small><i>{$lang["tip"]}: {$randomTip}</i></small>
        </div>
    </div>
</div>

{include file="parts/footer.tpl"}