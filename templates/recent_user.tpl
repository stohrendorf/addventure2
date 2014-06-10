{extends 'common_episodelist.tpl'}
{block name=title}
    Episodes written by &raquo;{$user.username}&laquo;
{/block}

{block name="headElements" append}
    <link href="{$url.base}/rss.php?what=recent&amp;count=100&amp;user={$user.userid}" rel="alternate" type="application/rss+xml" title="The 100 most recent episodes by &raquo;{$user.username|escape}&laquo; (RSS 2.0)"/>
    <link href="{$url.base}/atom.php?what=recent&amp;count=100&amp;user={$user.userid}" rel="alternate" type="application/atom+xml" title="The 100 most recent episodes written by &raquo;{$user.username|escape}&laquo; (ATOM)"/>
{/block}
