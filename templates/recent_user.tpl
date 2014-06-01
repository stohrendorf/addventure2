{extends 'layout.tpl'}
{block name=title}
    Episodes written by user #{$userid}
{/block}

{block name="headElements" append}
    <link href="{$url.base}/rss.php?what=recent&amp;count=100&amp;user={$userid}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes written by user #{$userid} (RSS 2.0)"/>
    <link href="{$url.base}/atom.php?what=recent&amp;count=100&amp;user={$userid}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes written by user #{$userid} (ATOM)"/>
{/block}

{block name=body}
    <div class="panel panel-default">
        <div class="panel-heading">
            User #{$userid} has written {$episodeCount} episodes{if isset($firstCreated) and  isset($lastCreated)} between {$firstCreated} and {$lastCreated}{/if}.
        </div>
        <div class="panel-body">
            <ol start="{$firstIndex+1}">
                {foreach $episodes as $episode}
                    {call name=episodeListItem episode=$episode}
                {/foreach}
            </ol>
        </div>
        <div class="panel-footer">
            <div style="text-align: center;">
                {$pagination}
            </div>
        </div>
    </div>
{/block}
