{if $logged}
    <h2>{replace s=$lang["welcome_back"] n="[username]" r=$user["username"]}</h2>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=u">{$lang["logout"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["logout_phrase"]}</p>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=p&id={$user["user_id"]}">{$lang["my_profile"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["my_profile_phrase"]}</p>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=m&t=i">{$lang["my_mail"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["my_mail_phrase"]}</p>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=f&id={$user["user_id"]}">{$lang["my_favourites"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["my_favourites_phrase"]}</p>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=c">{$lang["change_password"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["change_password_phrase"]}</p>

    {if in_array("admin", $permissions) || in_array("moderator", $permissions)}
        <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=n">{$lang["change_username"]}</a></h3>
        <p class="m-0 p-0 small">{$lang["change_username_phrase"]}</p>

        <h3 class="m-0 p-0 mt-10">&raquo; <a href="/admin.php?a=i">{$lang["admin_panel"]}</a></h3>
        <p class="m-0 p-0 small">{$lang["admin_panel_phrase"]}</p>
    {/if}
{else}
    <h2>{$lang["you_are_not_logged_in"]}</h2>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=l">{$lang["login"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["login_phrase"]}</p>

    <h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=r">{$lang["register"]}</a></h3>
    <p class="m-0 p-0 small">{$lang["register_phrase"]}</p>
{/if}

<h3 class="m-0 p-0 mt-10">&raquo; <a href="/account.php?a=o">{$lang["options"]}</a></h3>
<p class="m-0 p-0 small">{$lang["options_phrase"]}</p>