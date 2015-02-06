{extends 'layout.tpl'}
{block name=title}
    {t}User list{/t}
{/block}
{block name=body}
    <div class="panel panel-default">
        <div class="panel-body">
            <ol start="{$firstIndex+1}">
                {foreach $users as $user}
                    <li>
                        <a href="{$url.site}/maintenance/userinfo/{$user.userid}">
                            {if !empty($user.username)}
                                {$user.username}
                            {else}
                                {t 1={$user.userid}}User #%1{/t}
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
