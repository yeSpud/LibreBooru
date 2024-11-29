<h2>{$lang["register"]}</h2>
<p class="m-0 mt-10 p-0 small">{replace s=$lang["in_case_you_already_have_an_account"] n="[here]" r='<a href="/account.php?a=l">'|cat:$lang["here"]|cat:'</a>'}</p>

<form name="register" method="POST">
    <label for="username" class="small"><b>{$lang["username"]}</b> ({$lang["username_limit"]})</label><br>
    <input type="text" name="username" id="username" value="" required required autocomplete="off" tabindex="1"
        autofocus><br>

    <label for="password" class="small"><b>{$lang["password"]}</b> ({$lang["password_limit"]})</label><br>
    <input type="password" name="password" id="password" value="" required required autocomplete="off" tabindex="2"><br>

    <label for="password2" class="small"><b>{$lang["password"]}</b> ({$lang["again"]|lower})</label><br>
    <input type="password" name="password2" id="password2" value="" required required autocomplete="off"
        tabindex="3"><br>

    <p class="p-0 small">{replace s=$lang["by_signing_up"] n="[terms_of_service_and_privacy_policy]" r='<a href="/extra.php?a=o" target="_blank">'|cat:$lang["terms_of_service_and_privacy_policy"]|cat:'</a>'}</p>

    {if isset($guidelines)}
        <p class="p-0 small">
            {$guidelines}
        </p>
    {/if}

    <button type="submit" name="register" tabindex="4">{$lang["register"]}</button>
</form>