<h2>{$lang["login"]}</h2>
<p class="m-0 mt-10 p-0 small">{replace s=$lang["you_can_register_here"] n="[here]" r='<a href="/account.php?a=r">'|cat:$lang["here"]|cat:'</a>'}</p>

<form name="login" method="POST">
    <label for="username" class="small"><b>{$lang["username"]}</b></label><br>
    <input type="text" name="username" id="username" value="" required autocomplete="off" tabindex="1" autofocus><br>

    <label for="password" class="small"><b>{$lang["password"]}</b></label><br>
    <input type="password" name="password" id="password" value="" required required autocomplete="off" tabindex="2"><br>

    <p class="p-0 small">
        {$lang["by_logging_in"]}<br>
        {$lang["if_your_browser_doesnt_support_cookies"]}
    </p>

    <button type="submit" name="login" tabindex="3">{$lang["login"]}</button>
</form>