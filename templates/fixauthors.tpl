{extends 'layout.tpl'}
{block name=title}
    Maintenance: Fix Authors
{/block}
{block name=body}
    <ul>
        {foreach $episodes as $ep}
            <li>
                <a href="?doc={$ep.id}">#{$ep.id}</a>&mdash;{$ep.author.name}&mdash;{$ep.notes}&mdash;<a href="?maintenance=fixauthors&docid={$ep.id}">Join!</a>
            </li>
        {/foreach}
    </ul>
{/block}
