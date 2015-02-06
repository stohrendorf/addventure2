{extends 'common_episodelist.tpl'}
{block name=title}
    {t escape=no 1={$user.username}}Episodes written by &raquo;%1&laquo;{/t}
{/block}

{block name="headElements" append}
    <link href="{$url.site}/feed/rss/{$user.userid}" rel="alternate" type="application/rss+xml"
          title="{t escape=no 1={$user.username|escape}}Recent episodes by &raquo;%1&laquo; (RSS 2.0){/t}"/>
    <link href="{$url.site}/feed/atom/{$user.userid}" rel="alternate" type="application/atom+xml"
          title="{t escape=no 1={$user.username|escape}}Recent episodes by &raquo;%1&laquo; (ATOM){/t}"/>
{/block}

{block name="bodyheader"}
    <div class="panel panel-info">
        <div class="panel-heading">
            <h4>
                {t escape=no 1={$user.username}}Episodes written by &raquo;%1&laquo;{/t}
                {if $client.isAdministrator or $client.isModerator}
                    <sup><a href="{$url.site}/maintenance/userinfo/{$user.userid}"><span class="glyphicon glyphicon-user"></span> {t}User info{/t}</a></sup>
                {/if}
            </h4>
        </div>
        <div class="panel-body">
            {t 1={$user.username} 2={$episodeCount} 3={$firstCreated} 4={$lastCreated}}%1 has written %2 episodes between %3 and %4.{/t}
        </div>
    </div>
{/block}