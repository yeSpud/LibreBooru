<h2>{$lang["change_username"]}</h2>

<form name="change_username" method="POST" class="mt-10">
    <label for="new_username" class="small"><b>{$lang["new_username"]}</b> ({$lang["username_limit"]})</label><br>
    <input type="text" name="new_username" id="new_username" value="" required required autocomplete="off" tabindex="1" autofocus><br>

    <button type="submit" name="change_username" class="mt-10" tabindex="2">{$lang["change"]}</button>
</form>