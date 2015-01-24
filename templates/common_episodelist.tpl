{extends 'layout.tpl'}
{block name=body}
    {block name=bodyheader}{/block}
    <div class="panel panel-default">
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
