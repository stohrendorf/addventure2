{extends 'layout.tpl'}
{block name=title}
    Write-a-holics
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
                                User #{$user.user.userid}
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
