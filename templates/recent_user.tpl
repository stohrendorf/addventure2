{extends 'common_episodelist.tpl'}
{block name=title}
    Episodes written by &raquo;{$user.username}&laquo;
{/block}

{block name="headElements" append}
    <link href="{$url.site}/feed/rss/{$user.userid}" rel="alternate" type="application/rss+xml" title="Recent episodes by &raquo;{$user.username|escape}&laquo; (RSS 2.0)"/>
    <link href="{$url.site}/feed/atom/{$user.userid}" rel="alternate" type="application/atom+xml" title="Recent episodes by &raquo;{$user.username|escape}&laquo; (ATOM)"/>
{/block}
