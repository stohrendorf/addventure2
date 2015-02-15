{function name=episodeListItem}
    <li>
        <a href="{$url.site}/doc/{$episode.id}">
            &raquo;{$episode.autoTitle}&laquo;
        </a>
        {if isset($episode.author)}
            {t}by{/t} <a href="{$url.site}/recent/user/{$episode.author.user}">{$episode.author.name}</a>
        {/if}
        {if isset($episode.created)}
            @ {$episode.created}
        {/if}
        <span class="pull-right">
            <span class="glyphicon glyphicon-eye-open" style="color:dodgerblue;"></span>&nbsp;{$episode.hitcount}
            &nbsp;&nbsp;
            <span class="glyphicon glyphicon-heart" style="color:darkred;"></span>&nbsp;{$episode.likes}
            &nbsp;&nbsp;
            <span class="glyphicon glyphicon-heart-empty" style="color:darkred;"></span>&nbsp;{$episode.dislikes}
        </span>
        <span class="clearfix"></span>
    </li>
{/function}
{function name=showEpisode}
    <div class="panel panel-default">
        {if isset($episode.preNotes) || $client.canEdit}
            <div class="panel-heading">
                {if isset($episode.preNotes)}
                    <h4><b>{t}Author's Notes{/t}</b></h4>
                    <p>{$episode.preNotes|smileys}</p>
                    {if $config.legacyInfo}
                        <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportTitle/{$episode.id}"}This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                            But machines aren't perfect: do you think this is wrong? <a href="%1">Report it!</a>{/t}</small>
                    {/if}
                {/if}
                {if $client.canEdit}
                    <a href="{$url.site}/doc/edit/{$episode.id}"><span class="glyphicon glyphicon-edit"></span>&nbsp;{t}Edit{/t}</a>
                {/if}
            </div>
        {/if}

        <div class="panel-body" style="background-color:#fffcee;">
            {$episode.text}
        </div>

        {if isset($episode.postNotes)}
            <div class="panel-footer">
                <h4><b>{t}Author's Notes{/t}</b></h4>
                <p>{$episode.postNotes|smileys}</p>
                {if $config.legacyInfo}
                    <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportNotes/{$episode.id}"}This note has been <em>automatically</em> extracted from the legacy episode's author name.
                        But machines aren't perfect: do you think this was done wrong? <a href="%1">Report it!</a>{/t}</small>
                {/if}
            </div>
        {/if}
    </div>

{/function}