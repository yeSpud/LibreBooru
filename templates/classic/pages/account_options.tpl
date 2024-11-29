<h2>Options</h2>

<form name="change_password" method="POST" class="mt-10">
    {if $config["change_pass_requires_old"]}
        <label for="old_password" class="small"><b>Old Password</b></label><br>
        <input type="password" name="old_password" id="old_password" value="" required required autocomplete="off"
            tabindex="1" autofocus><br>
    {/if}

    <label for="new_password" class="small"><b>New Password</b> (>8 chars)</label><br>
    <input type="password" name="new_password" id="new_password" value="" required required autocomplete="off"
        tabindex="2"
        {if !$config["change_pass_requires_old"]}autofocus{/if}><br>

    <label for="new_password2" class="small"><b>New Password</b> (again)</label><br>
    <input type="password" name="new_password2" id="new_password2" value="" required required autocomplete="off"
        tabindex="3"><br>

    <p class="p-0 small">
        Changing your password will destory <b>all</b> sessions and log you out.
    </p>

    <button type="submit" name="change_password" tabindex="4">Change</button>
</form>