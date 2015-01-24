{extends 'common_episodelist.tpl'}
{block name=title}
    {t escape=no 1={$user.username}}Episodes written by &raquo;%1&laquo;{/t}
{/block}

{block name="headElements" append}
    <link href="{$url.site}/feed/rss/{$user.userid}" rel="alternate" type="application/rss+xml"
          title="{t escape=no 1={$user.username|escape}}Recent episodes by &raquo;%1&laquo; (RSS 2.0){/t}"/>
    <link href="{$url.site}/feed/atom/{$user.userid}" rel="alternate" type="application/atom+xml"
          title="{t escape=no 1={$user.username|escape}}Recent episodes by &raquo;%1&laquo; (ATOM){t}"/>
{/block}
