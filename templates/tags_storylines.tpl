{extends 'layout.tpl'}
{block name=title}
    {t}Storyline tags{/t}
{/block}
{block name=body}
    <div class="panel panel-default">
        <div class="panel-body">
            <ol start="{$firstIndex+1}">
                {foreach $tags as $tag}
                    <li>
                        <a href="{$url.site}/tags/storyline/{$tag.id}">
                            {$tag.title|escape}
                        </a>
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
