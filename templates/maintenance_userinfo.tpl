{extends 'layout.tpl'}
{block name=title}
    {t}User info{/t}
{/block}
{block name=body}
    <table class="table table-striped table-condensed" style="margin:auto; width:auto;">
        <tbody>
            <tr><td>{t}ID{/t}</td><td>{$user.userid}</td></tr>
            <tr><td>{t}Username{/t}</td><td>{$user.username}</td></tr>
            <tr><td>{t}E-Mail{/t}</td><td>{$user.email}</td></tr>
            <tr><td>{t}Registered since{/t}</td><td>{$user.registeredSince}</td></tr>
            <tr>
                <td>{t}Role{/t}</td>
                <td>
                    <a href="#" class="disabled btn {if $user.role==0}btn-success{else}btn-default{/if}">{t}Anonymous{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/1" class="btn {if $user.role==1}btn-success{else}btn-default{/if}">{t}Awaiting approval{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/2" class="btn {if $user.role==2}btn-success{else}btn-default{/if}">{t}Registered{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/3" class="btn {if $user.role==3}btn-success{else}btn-default{/if}">{t}Moderator{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/4" class="btn {if $user.role==4}btn-success{else}btn-default{/if}">{t}Administrator{/t}</a>
                </td>
            </tr>
            <tr>
                <td>{t}Failed logins{/t}</td>
                <td>{$user.failedLogins} (<a href="{$url.site}/maintenance/resetlogins/{$user.userid}">{t}Reset{/t}</a>)</td>
            </tr>
            <tr>
                <td>{t}Status{/t}</td>
                <td>
                    {if $user.blocked}
                        {t}Blocked{/t} (<a href="{$url.site}/maintenance/unblock/{$user.userid}">{t}Unblock{/t}</a>)
                    {else}
                        {t}Unblocked{/t} (<a href="{$url.site}/maintenance/block/{$user.userid}">{t}Block{/t}</a>)
                    {/if}
                </td>
            </tr>
        </tbody>
    </table>
{/block}
