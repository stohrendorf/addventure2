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
                    <b>{t}Author's Notes{/t}</b>
                    <p>{$episode.preNotes|smileys}</p>
                    {if $smarty.const.ADDVENTURE_LEGACY_INFO}
                        <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportTitle/{$episode.id}"}This note has been <em>automatically</em> taken from the episode's legacy title, as it seemed pretty long.
                            But machines aren't perfect: do you think this is wrong? <a href="%1">Report it!</a>{/t}</small>
                    {/if}
                {/if}
                {if $client.canEdit}
                    <a href="{$url.site}/doc/edit/{$episode.id}"><span class="glyphicon glyphicon-edit"></span> {t}Edit{/t}</a>
                {/if}
            </div>
        {/if}

        <div class="panel-body" style="background-color:#fffcee;">
            {$episode.text}
        </div>

        {if isset($episode.notes)}
            <div class="panel-footer">
                <b>{t}Author's Notes{/t}</b>
                <p>{$episode.notes|smileys}</p>
                {if $smarty.const.ADDVENTURE_LEGACY_INFO}
                    <small style="font-style: italic;">{t escape=no 1="{$url.site}/maintenance/reportNotes/{$episode.id}"}This note has been <em>automatically</em> extracted from the legacy episode's author name.
                        But machines aren't perfect: do you think this was done wrong? <a href="%1">Report it!</a>{/t}</small>
                {/if}
            </div>
        {/if}
    </div>

    <div class="list-group">
        {if !empty($episode.comments)}
            <h4 class="list-group-item list-group-item-info">
                <span class="glyphicon glyphicon-comment"></span> {t}Comments...{/t}
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
            {if $client.canCreateComment}
                <div class="list-group-item">
                    <button class="btn btn-default btn-sm btn-block" id="add-comment">
                        <span class="glyphicon glyphicon-comment"></span>
                        {t}Add a comment{/t}
                    </button>
                </div>
            {/if}
        {else}
            <h4 class="list-group-item list-group-item-info">
                <span class="glyphicon glyphicon-comment"></span> {t}No comments yet{/t}
            </h4>
            {if $client.canCreateComment}
                <div class="list-group-item">
                    <button class="btn btn-default btn-sm btn-block" id="add-comment">
                        <span class="glyphicon glyphicon-comment"></span>
                        {t}Be the first to comment on this episode!{/t}
                    </button>
                </div>
            {/if}
        {/if}
    </div>


    <div class="modal fade" id="comment-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Add a comment{/t}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{t}Add a comment{/t}</h4>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="comment-text" style="min-height: 200px;" required placeholder="{t}Your comment{/t}"></textarea>
                    <input type="text" class="form-control" id="comment-author" required placeholder="{t}Signed off{/t}" value="{$client.username}"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="publish-comment">{t}Publish!{/t}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{t}Abort{/t}</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(function () {
            var dlg = $('#comment-dialog');
            dlg.modal('hide');
            var addCommentBtn = function () {
                dlg.modal('show');
                return false;
            };
            $('#add-comment').click(addCommentBtn);

            var publishComment = function () {
                $.post(
                    '{$url.site}/api/addcomment/{$episode.id}',
                    {
                        {csrf_json},
                        'comment': $('#comment-text').val(),
                        'author': $('#comment-author').val()
                    },
                    function () {
                        location.reload();
                    }
                );
                return false;
            };
            $('#publish-comment').click(publishComment);
        });
    </script>

{/function}