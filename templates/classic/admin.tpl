{include file="parts/header.tpl"}

{include file="parts/menu.tpl"}

<div class="content">
    {if $action == "i"}
        <h2>{replace s=$lang["welcome_to_the_admin_panel"] n="[username]" r=$user["username"]}</h2>

        {if in_array("admin", $permissions)}
            <h3 class="m-0 p-0 mt-10">&raquo; <a href="/admin.php?a=u">{$lang["update_system"]}</a></h3>
            <p class="m-0 p-0 small">{$lang["update_system_phrase"]}</p>
        {/if}

        <h3 class="m-0 p-0 mt-10">&raquo; <a href="/posts.php?a=s&t=status:awaiting">{$lang["approval_queue"]}</a></h3>
        <p class="m-0 p-0 small">{$lang["approval_queue_phrase"]}</p>

        <h3 class="m-0 p-0 mt-10">&raquo; <a href="/admin.php?a=r">{$lang["reports"]}</a></h3>
        <p class="m-0 p-0 small">{$lang["reports_phrase"]}</p>
    {elseif $action == "u"}
        <h2>{$lang["update_system"]}</h2>
        <p class="mb-0 pb-0">{$lang["current_version"]}: <span id="currentVersionId">{$version}</span></p>
        <p class="m-0 p-0">{$lang["branch"]}: {$branch}</p>
        <p class="m-0 p-0">{$lang["latest_stable_version"]}: {$latestStableVersion}</p>
        <p class="m-0 p-0">{$lang["latest_devel_version"]}: {$latestDevelVersion}</p>
        <button onclick="updateSystem();" id="update_button">{$lang["perform_update"]}</button>
        <div class="mt-10" id="update_output_container" style="display: none; border: 1px solid black;"><code
                id="update_output"></code></div>
    {elseif $action == "r"}
        <h2>{$lang["reports"]}</h2>
        {if $reportsType == "p"}
            <p class="mb-0 p-0">
                {$lang["posts"]} -
                <a href="/admin.php?a=r&t=c">{$lang["comments"]}</a>
            </p>
            <p>
                <a href="/admin.php?a=r&t=p">{$lang["reported"]}</a> -
                <a href="/admin.php?a=r&t=p&s=a">{$lang["approved"]}</a> -
                <a href="/admin.php?a=r&t=p&s=r">{$lang["rejected"]}</a>
            </p>
        {else}
            <p class="mb-0 p-0">
                <a href="/admin.php?a=r&t=p">{$lang["posts"]}</a> -
                {$lang["comments"]}
            </p>
        {/if}

        {if $reportsType == "p"}
            <table class="history_table mt-10">
                <tr>
                    <th>{$lang["user"]}</th>
                    <th style="width: 1%;">{$lang["post"]}</th>
                    <th style="width: 1%;">{$lang["reason"]}</th>
                    <th>{$lang["action"]}</th>
                    <th>{$lang["status"]}</th>
                    <th style="text-align: right; width: 15%">{$lang["timestamp"]}</th>
                </tr>
                {foreach $reports as $entry}
                    <tr id="report-{$entry["report_id"]}">
                        <td><a href="/account.php?a=p&id={$entry["user_id"]}" target="_blank">{$entry["username"]}</a></td>
                        <td><a href="/posts.php?a=p&id={$entry["post_id"]}" target="_blank">Post:{$entry["post_id"]}</a></td>
                        <td>{$entry["reason"]}</td>
                        <td>
                            {if $entry["status"] !== "approved"}<a
                                href="javascript:approveReport('{$entry["report_id"]}', '{$entry["post_id"]}')">{$lang["approve"]}</a>{else}{$lang["approve"]}
                            {/if}
                            -
                            {if $entry["status"] !== "rejected"}<a
                                href="javascript:rejectReport('{$entry["report_id"]}')">{$lang["reject"]}</a>{else}{$lang["reject"]}
                            {/if}
                        </td>
                        <td>{$entry["status"]|capitalize}</td>
                        <td style="text-align: right;">{$entry["timestamp"]}</td>
                    </tr>
                {/foreach}
            </table>
        {else}
            Comments
        {/if}
    {/if}
</div>

<script>
    function updateSystem() {
        var updateOutputContainer = document.getElementById("update_output_container");
        var updateOutput = document.getElementById("update_output");
        var updateButton = document.getElementById("update_button");
        var currentVersionSpan = document.getElementById("currentVersionId");
        updateOutputContainer.style.display = "block";
        updateOutput.innerHTML = "{$lang["updating"]}...";
        updateButton.innerHTML = "{$lang["updating"]}...";
        updateButton.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/update_software.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                updateOutput.innerHTML = xhr.responseText;
                updateButton.disabled = false;
                updateButton.innerHTML = "{$lang["perform_update"]}";
                currentVersionSpan.innerHTML = "{$latestVersion}";
            } else {
                updateOutput.innerHTML = "{$lang["error"]}?!";
            }
        };
        xhr.send();
    }
</script>

{include file="parts/footer.tpl"}