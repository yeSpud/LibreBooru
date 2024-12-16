{if $tab == "r"}
    <h2>{replace s=$lang["x_reputation"] n="[username]" r='<a href="/account.php?a=p&id='|cat:$profile["user_id"]|cat:'">'|cat:$profile["username"]|cat:"</a>"}
    </h2>
    <h3 class="m-0 p-0 mt-10">{$lang["total"]}: <span style="color:green">+{$profile["rep_count_plus"]}</span> / <span
            style="color:red">-{$profile["rep_count_minus"]}</span></h3>
    {if $canJudge}
        <hr>
        <form method="POST" name="judge">
            <input type="hidden" name="a" value="p">
            <input type="hidden" name="id" value="{$profile["user_id"]}">
            <input type="hidden" name="t" value="r">

            <input type="radio" name="r" value="1" id="r1"><label for="r1" style="color:green">+1</label>
            <input type="radio" name="r" value="2" id="r2"><label for="r2" style="color:red">-1</label>
            <button type="submit" name="judge" class="ml-10">{$lang["judge"]}</button>
            <br>
            <textarea name="comment" cols="40" rows="3" class="mt-5" placeholder="Comment (Required)"></textarea>
            <p class="m-0 p-0 small"><i>Once judged, you cannot change your opinion.</i></p>
        </form>
        <hr>
    {/if}

    <table class="history_table mt-10">
        <tr>
            <th style="width: 1%"></th>
            <th style="width: 15%">User</th>
            <th>Comment</th>
            <th style="width: 20%; text-align:right">Timestamp</th>
        </tr>
        {foreach from=$reputation item=item key=key name=name}
            <tr>
                <td><b><span style="color:{if $item["given"] == "+"}green{else}red{/if}">{$item["given"]}</span></b></td>
                <td><a href="/account.php?a=p&id={$item["giver_id"]}">{$item["giver_username"]}</a></td>
                <td>{$item["comment"]}</td>
                <td style="text-align:right">{$item["timestamp"]}</td>
            </tr>
        {/foreach}
    </table>
{else}
    <h2>{$profile["username"]}</h2>

    <table class="history_table mt-10">
        <tr>
            <th style="width: 15%"></th>
            <th></th>
        </tr>
        <tr>
            <td><b>{$lang["contact"]}</b></td>
            <td><a href="/mail.php?a=c&r={$profile["username"]}">{$lang["send_mail"]}</a></td>
        </tr>
        <tr>
            <td><b>{$lang["joined"]}</b></td>
            <td>{$profile["registration_date"]}</td>
        </tr>
        <tr>
            <td><b>{$lang["level"]}</b></td>
            <td>{$profile["level_name"]}</td>
        </tr>
        <tr>
            <td><b>{$lang["posts"]} ({$lang["deleted"]})</b></td>
            <td><a href="/posts.php?a=s&t=user:{$profile["username"]}">{$profile["post_count"]}</a>
                ({$profile["deleted_post_count"]})</td>
        </tr>
        <tr>
            <td><b>{$lang["favourites"]}</b></td>
            <td><a href="/account.php?a=f&id={$profile["user_id"]}">{$profile["favourites_count"]}</a></td>
        </tr>
        <tr>
            <td><b>{$lang["comments"]} ({$lang["deleted"]})</b></td>
            <td><a href="/extra.php?a=c&s=user:{$profile["username"]}">{$profile["comment_count"]}</a> ({$profile["deleted_comment_count"]})</td>
        </tr>
        <tr>
            <td><b>{$lang["tag_edits"]} / {$lang["wiki_edits"]}</b></td>
            <td>{$profile["post_edit_count"]} / {$profile["wiki_edit_count"]}</td>
        </tr>
        <tr>
            <td><b>{$lang["forum_posts"]} ({$lang["deleted"]})</b></td>
            <td>0 (0)</td>
        </tr>
        <tr>
            <td><b>{$lang["reputation"]}</b></td>
            <td><span style="color:green">+{$profile["rep_count_plus"]}</span> / <span
                    style="color:red">-{$profile["rep_count_minus"]}</span> (<a
                    href="/account.php?a=p&id={$profile["user_id"]}&t=r">{$lang["add"]}</a>)</td>
        </tr>
    </table>

    <h3 class="m-0 p-0 mt-10"><a href="/account.php?a=f&id={$profile["user_id"]}">{$lang["recent_favourites"]}</a></h3>
    {if isset($favourites) && !empty($favourites)}
        <div class="gallery">
            <!-- Create 6 columns dynamically -->
            {for $i=0 to 5}
                <div class="column" id="col-{$i+1}">
                    {foreach from=$favourites item=item key=key}
                        {if $key % 6 == $i}
                            <a href="/posts.php?a=p&id={$item.post_id}" title="rating:{$item.rating}">
                                <img src="/uploads/thumbs/{if $item.deleted && (in_array("moderate", $permissions) && in_array("admin", $permissions))}.{/if}{$item.image_url}.{if isset($item.is_video)}jpg{else}{$item.file_extension}{/if}"
                                    alt="{$item.description} - rating:{$item.rating}"
                                    class="{if !$item.is_approved}post_awaiting{elseif $item.deleted}post_deleted{else}{if $item.file_extension == "gif"}post_gif{/if}{if isset($item.is_video)}post_video{/if}{/if}">
                            </a>
                        {/if}
                    {/foreach}
                </div>
            {/for}
        </div>
    {/if}

    <h3 class="m-0 p-0 mt-10"><a href="/posts.php?a=s&t=user:{$profile["username"]}">{$lang["recent_uploads"]}</a></h3>
    {if isset($uploads) && !empty($uploads)}
        <div class="gallery">
            <!-- Create 6 columns dynamically -->
            {for $i=0 to 5}
                <div class="column" id="col-{$i+1}">
                    {foreach from=$uploads item=item key=key}
                        {if $key % 6 == $i}
                            <a href="/posts.php?a=p&id={$item.post_id}" title="{$item.tags} rating:{$item.rating} score:{$item.score}">
                                <img src="/uploads/thumbs/{if $item.deleted && (in_array("moderate", $permissions) && in_array("admin", $permissions))}.{/if}{$item.image_url}.{if isset($item.is_video)}jpg{else}{$item.file_extension}{/if}"
                                    alt="{$item.description} - {$item.tags} rating:{$item.rating} score:{$item.score}"
                                    class="{if !$item.is_approved}post_awaiting{elseif $item.deleted}post_deleted{else}{if $item.file_extension == "gif"}post_gif{/if}{if isset($item.is_video)}post_video{/if}{/if}">
                            </a>
                        {/if}
                    {/foreach}
                </div>
            {/for}
        </div>
    {/if}
{/if}