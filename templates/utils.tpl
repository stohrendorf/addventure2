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
        {if isset($episode.preNotes)}
            <div class="panel-heading">
                <b>{t}Author's Notes{/t}</b>
                &mdash;
                {$episode.preNotes|smileys}
                <br/><br/>
                <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportTitle/{$episode.id}"}This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                    But machines aren't perfect: do you think this is wrong? <a href="%1">Report it!</a>{/t}</small>
            </div>
        {/if}

        <div class="panel-body" style="background-color:#fffcee;">{$episode.text}</div>

        {if isset($episode.notes)}
            <div class="panel-footer">
                <b>{t}Author's Notes{/t}</b>
                &mdash;
                {$episode.notes|smileys}
                <br/><br/>
                <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportNotes/{$episode.id}"}This note has been <em>automatically</em> extracted from the legacy episode's author name.
                    But machines aren't perfect: do you think this was done wrong? <a href="%1">Report it!</a>{/t}</small>
            </div>
        {/if}
    </div>

    {if !empty($episode.comments)}
        <div class="list-group">
            <h4 class="list-group-item list-group-item-info">
                <span class="glyphicon glyphicon-comment"></span> Comments...
            </h4>
            {foreach $episode.comments as $comment}
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">
                        {if isset($comment.author)}
                            <a href="{$url.site}/recent/user/{$comment.author.user}">{$comment.author.name}</a>
                        {else}
                            &LeftAngleBracket;{t}John Doe{/t}&RightAngleBracket;
                        {/if}
                        <span class="text-info">@ {$comment.created}</span>
                    </h5>
                    <div class="list-group-item-text">{$comment.text|smileys}</div>
                </div>
            {/foreach}
        </div>
    {/if}
{/function}