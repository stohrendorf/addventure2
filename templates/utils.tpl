{function name=episodeListItem}
    <li>
        <a href="{$url.site}/doc/{$episode.id}">
            &raquo;{$episode.autoTitle}&laquo;
        </a>
        {if isset($episode.author)}
            by <a href="{$url.site}/recent/user/{$episode.author.user}">{$episode.author.name}</a>
        {/if}
        {if isset($episode.created)}
            on {$episode.created}
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
                <div class="panel panel-info">
                    <div class="panel-heading">Author's Notes</div>
                    <div class="panel-body">{$episode.preNotes}</div>
                    <div class="panel-footer"><small>This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                            But machines aren't perfect: do you think this is wrong? <a href="{$url.site}/maintenance/reportTitle/{$episode.id}">Report it!</a></small></div>
                </div>
            </div>
        {/if}

        <div class="panel-body" style="background-color:#fffcee;">{$episode.text}</div>

        {if isset($episode.notes)}
            <div class="panel-footer">
                <div class="panel panel-info">
                    <div class="panel-heading">Author's Notes</div>
                    <div class="panel-body">{$episode.notes}</div>
                    <div class="panel-footer"><small>This note has been <em>automatically</em> extracted from the legacy episode's author name.
                            But machines aren't perfect: do you think this was done wrong? <a href="{$url.site}/maintenance/reportNotes/{$episode.id}">Report it!</a></small></div>
                </div>
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
                            &LeftAngleBracket;John Doe&RightAngleBracket;
                        {/if}
                        <span class="text-info">@ {$comment.created}</span>
                    </h5>
                    <div class="list-group-item-text">{$comment.text}</div>
                </div>
            {/foreach}
        </div>
    {/if}
{/function}