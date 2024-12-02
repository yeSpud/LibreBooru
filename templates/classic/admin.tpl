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
                id="update_output">Updating...</code></div>
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