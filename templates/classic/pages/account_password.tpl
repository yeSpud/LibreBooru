<h2>{$lang["change_password"]}</h2>

<form name="change_password" method="POST" class="mt-10">
    {if $config["change_pass_requires_old"]}
        <label for="old_password" class="small"><b>{$lang["old_password"]}</b></label><br>
        <input type="password" name="old_password" id="old_password" value="" required required autocomplete="off"
            tabindex="1" autofocus><br>
    {/if}

    <label for="new_password" class="small"><b>{$lang["new_password"]}</b> ({$lang["password_limit"]})</label><br>
    <input type="password" name="new_password" id="new_password" value="" required required autocomplete="off"
        tabindex="2"
        {if !$config["change_pass_requires_old"]}autofocus{/if}><br>

    <label for="new_password2" class="small"><b>New Password</b> ({$lang["again"]|lower})</label><br>
    <input type="password" name="new_password2" id="new_password2" value="" required required autocomplete="off"
        tabindex="3"><br>

    <p class="p-0 small">
        {$lang["chaning_your_password"]}
    </p>

    <button type="submit" name="change_password" tabindex="4">{$lang["change"]}</button>
</form>