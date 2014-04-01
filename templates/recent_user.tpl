{extends 'layout.tpl'}
{block name=title}
    Episodes written by user #{$userid}
{/block}

{block name="headElements" append}
    <link href="{$url.site}/rss.php?what=recent&count=100&user={$userid}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes written by user #{$userid} (RSS 2.0)"/>
    <link href="{$url.site}/atom.php?what=recent&count=100&user={$userid}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes written by user #{$userid} (ATOM)"/>
{/block}

{block name=body}
    <div class="panel panel-default">
        <div class="panel-heading">
            User #{$userid} has written {$episodeCount} episodes{if isset($firstCreated) and  isset($lastCreated)} between {$firstCreated} and {$lastCreated}{/if}.
        </div>
        <div class="panel-body">
            <ol start="{$firstIndex+1}">
                {foreach $episodes as $episode}
                    <li>
                        <a href="{$url.site}/doc/{$episode.id}">
                            {if !empty($episode.title)}
                                {$episode.title}
                            {else}
                                Episode #{$episode.id}
                            {/if}
                        </a>
                        as {$episode.author.name}
                        {if isset($episode.created)}
                            on {$episode.created}
                        {/if}
                        <span class="pull-right">
                        <span class="glyphicon glyphicon-eye-open" style="color:dodgerblue;"></span>{$episode.hitcount}
                        <span class="glyphicon glyphicon-heart" style="color:darkred;"></span>{$episode.likes}
                        <span class="glyphicon glyphicon-heart-empty" style="color:darkred;"></span>{$episode.dislikes}
                        </span>
                    </li>
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
