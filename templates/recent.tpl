{extends 'layout.tpl'}
{block name=title}
    The freshest leaves
{/block}

{block name=body}
    <div class="panel panel-default">
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
                        {if isset($episode.author)}
                            by <a href="{$url.site}/recent/user/{$episode.author.user}">{$episode.author.name}</a>
                        {/if}
                        {if isset($episode.created)}
                            on {$episode.created}
                        {/if}
                        <span class="pull-right">
                            <span class="glyphicon glyphicon-eye-open"></span>{$episode.hitcount}
                            <span class="glyphicon glyphicon-heart"></span>{$episode.likes}
                            <span class="glyphicon glyphicon-heart-empty"></span>{$episode.dislikes}
                        </span>
                        <span class="clearfix"></span>
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
