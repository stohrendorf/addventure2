{extends 'layout.tpl'}
{block name=title}
    {t}Write-a-holics{/t}
{/block}
{block name=body}
    <div class="panel panel-default">
        <div class="panel-body">
            <ol start="{$firstIndex+1}">
                {foreach $users as $user}
                    <li>
                        {$user.count} Episode{if $user.count>1}s{/if}
                        by
                        <a href="{$url.site}/recent/user/{$user.user.userid}">
                            {if !empty($user.user.username)}
                                {$user.user.username}
                            {else}
                                {t 1={$user.user.userid}}User #%1{/t}
                            {/if}
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
