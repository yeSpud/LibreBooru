{include file="parts/header.tpl"}

{include file="parts/menu.tpl"}

<div class="content">
    {if isset($errors) && !empty($errors)}
        {foreach from=$errors item=item key=key name=name}
            <div class="error">Error: {$item}</div>
        {/foreach}
    {/if}
    {if $action == "i"}
        {include file="pages/account_index.tpl"}
    {elseif $action == "l"}
        {if !$logged}
            {include file="pages/account_login.tpl"}
        {/if}
    {elseif $action == "r"}
        {if !$logged}
            {include file="pages/account_register.tpl"}
        {/if}
    {elseif $action == "c"}
        {if $logged}
            {include file="pages/account_password.tpl"}
        {/if}
    {elseif $action == "n"}
        {if $logged}
            {if in_array("admin", $permissions)}
                {include file="pages/account_username.tpl"}
            {/if}
        {/if}
    {elseif $action == "o"}
        {if $logged}
            {include file="pages/account_options.tpl"}
        {/if}
    {elseif $action == "p"}
        {include file="pages/account_profile.tpl"}
    {/if}
</div>

{include file="parts/footer.tpl"}