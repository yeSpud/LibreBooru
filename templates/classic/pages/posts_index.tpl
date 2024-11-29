{if isset($posts) && !empty($posts)}
    <div class="gallery">
        <!-- Create 6 columns dynamically -->
        {for $i=0 to 5}
            <div class="column" id="col-{$i+1}">
                {foreach from=$posts item=item key=key}
                    {if $key % 6 == $i}
                        <a href="/posts.php?a=p&id={$item.post_id}{if isset($urlSearchTerm) && !empty($urlSearchTerm)}&t={$urlSearchTerm}{/if}"
                            title="{$item.tags} rating:{$item.rating} score:{$item.score}">
                            <img src="/uploads/thumbs/{$item.image_url}.{if isset($item.is_video)}jpg{else}{$item.file_extension}{/if}"
                                alt="{$item.description}"
                                class="{if !$item.is_approved}post_awaiting{else}{if $item.file_extension == "gif"}post_gif{/if}{if isset($item.is_video)}post_video{/if}{/if}">
                        </a>
                    {/if}
                {/foreach}
            </div>
        {/for}
    </div>
{else}
    <h2 style="line-height: 0; margin-top: 10px">{$lang["nobody_here_but_us_ravens"]}</h2>
    <p style="line-height: 0;"><small><i>{replace s=$lang["check_your_blacklist"] n="[blacklist]" r='<a href="/account.php?a=o">'|cat:$lang["blacklist"]|cat:'</a>'}</i></small></p>
{/if}

<!-- !TODO: Improve by creating a number for each page -->
<div class="pagination">
    {if $page > 1}
        <a href="/posts.php?a={$action}{if $action == "s"}&t={$urlSearchTerm}{/if}&p=1">&laquo; {$lang["first"]}</a>
        <a href="/posts.php?a={$action}{if $action == "s"}&t={$urlSearchTerm}{/if}&p={$page - 1}">{$lang["previous"]}</a>
    {/if}

    {if $page < $totalPages}
        <a href="/posts.php?a={$action}{if $action == "s"}&t={$urlSearchTerm}{/if}&p={$page + 1}">{$lang["next"]}</a>
        <a href="/posts.php?a={$action}{if $action == "s"}&t={$urlSearchTerm}{/if}&p={$totalPages}">{$lang["last"]} &raquo;</a>
    {/if}
</div>