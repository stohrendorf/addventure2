{extends 'layout.tpl'}
{block name=title}
    &raquo;{$episode.autoTitle}&laquo;{if isset($episode.author)} {t}by{/t} {$episode.author.name}{/if}
{/block}

{block name="headElements" append}
    {if isset($episode.author)}
        <link href="{$url.site}/feed/rss/{$episode.author.user}" rel="alternate" type="application/rss+xml"
              title="{t escape=no 1={$episode.author.name|escape}}Recent episodes by &raquo;%1&laquo; (RSS 2.0){/t}"/>
        <link href="{$url.site}/feed/atom/{$episode.author.user}" rel="alternate" type="application/atom+xml"
              title="{t escape=no 1={$episode.author.name|escape}}Recent episodes by &raquo;%1&laquo; (ATOM){/t}"/>
    {/if}
    <style type="text/css">
        #storyline-dialog .modal-dialog,
        #storyline-dialog .modal-content {
            height: 90%;
        }

        #storyline-dialog .modal-body {
            max-height: calc(100% - 120px);
            overflow-y: scroll;
        }
    </style>
{/block}

{block name=body}
    {function name=childTree depth=2}
        {foreach $tree as $child}
            <div style="margin:0 0 0.3em {1.3*$depth}em; font-size: {(100-6*$depth)}%;">
                <a href="{$url.site}/doc/{$child.id}">{$child.title}</a>
                {if isset($child.children)}
                    {call name=childTree tree=$child.children depth={$depth+1}}
                {/if}
            </div>
        {/foreach}
    {/function}
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 style="display:inline;" class="panel-title">
                &raquo;{$episode.autoTitle}&laquo;
                {if isset($episode.author) or isset($episode.created)}
                    <span class="text-info">
                        {if isset($episode.author)}
                            {t}by{/t} <a href="{$url.site}/user/{$episode.author.user}">{$episode.author.name}</a>
                            {if $client.isAdministrator or $client.isModerator}
                                <sup><a href="{$url.site}/maintenance/userinfo/{$episode.author.user}"><span class="glyphicon glyphicon-user"></span> {t}User info{/t}</a></sup>
                            {/if}
                        {/if}
                        {if isset($episode.created)}
                            @ {$episode.created}
                        {/if}
                    </span>
                {/if}
                <br/>
                {if isset($episode.storyline)}
                    <span class="text-info">
                        <a href="{$url.site}/tags/storyline/{$episode.storyline.id}"><span class="glyphicon glyphicon-tag"></span> {$episode.storyline.title|escape}</a>
                        {if $client.isAdministrator or $client.isModerator}
                            <a href="#" id="storyline-btn" class="btn btn-primary btn-sm">{t}Change tag{/t}</a>
                        {/if}
                    </span>
                {else}
                    {if $client.isAdministrator or $client.isModerator}
                        <span class="text-info">
                            <a href="#" id="storyline-btn" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus-sign"></span>{t}Add storyline tag{/t}</a>
                        </span>
                    {/if}
                {/if}
            </h3>
            <a href="{$url.site}/maintenance/illegal/{$episode.id}" style="color:red;" class="pull-right" id="report"
               data-toggle="tooltip" data-placement="right auto" title="{t}This function is for reporting content that breaks the rules, not for crying about a bad story.{/t}"> <span class="glyphicon glyphicon-fire"></span> {t}Report inappropriate content{/t}</a>
            <span class="clearfix"></span>
            {if !empty($episode.tags)}
                <span class="glyphicon glyphicon-tags"></span>
                {foreach $episode.tags as $tag}
                    <a href="docs/bytag/{$tag.id}">{$tag.title}</a>
                {/foreach}
            {/if}
        </div>
        <div class="panel-body">
            {t 1={$episode.hitcount} 2={$episode.likes} 3={$episode.dislikes}}It has been seen %1 times, and %2 people liked it, while %3 didn't.{/t}
            <div class="pull-right">
                {t escape=no}What do <em>you</em> think?{/t}
                <a href="{$url.site}/like/{$episode.id}"> <span class="glyphicon glyphicon-heart"></span> {t}I like it!{/t}</a>
                <a href="{$url.site}/dislike/{$episode.id}"> <span class="glyphicon glyphicon-heart-empty"></span> {t}Na, could have been better...{/t}</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>

    {call name="showEpisode"}
    
    <div class="list-group comments">
        {if !empty($episode.comments)}
            <h4 class="list-group-item list-group-item-info">
                <span class="glyphicon glyphicon-comment"></span> {t}Comments...{/t}
            </h4>
            {foreach $episode.comments as $comment}
                <div class="list-group-item">
                    <h5 class="list-group-item-heading">
                        {if $client.canEdit}
                            <a href="{$url.site}/maintenance/deletecomment/{$comment.id}"><span class="glyphicon glyphicon-trash"></span> {t}Delete{/t}</a>
                        {/if}
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
    
    <div class="panel panel-primary children">
        <div class="panel-body" id="links">
            {$canSubscribe=false}
            {foreach $episode.children as $link}
                {if !empty($link.subtree)}
                    <a id="show-descendants" class="pull-right" style="cursor:pointer;" data-toggle="tooltip" data-placement="right auto" title="{t}Show episode tree. (May contain spoilers!){/t}">
                        <div style="top:0;right:0;" id="show-descendants-plus"><span class="glyphicon glyphicon-plus-sign" style="font-size:2em;"></span></div>
                        <div style="top:0;right:0;display:none;" id="show-descendants-minus"><span class="glyphicon glyphicon-minus-sign" style="font-size:2em;"></span></div>
                    </a>
                    <script type="text/javascript">
                        $(function() {
                            $('#show-descendants').tooltip();
                            $('#show-descendants').click(function() {
                                $('div.episode-descendants').each(function(i, e) {
                                    $(e).collapse('toggle');
                                    if(i==0) {
                                        $(e).on('shown.bs.collapse', function() {
                                            $('html, body').animate({
                                                scrollTop: $('#links-top').offset().top
                                            }, 'slow');
                                        });
                                    }
                                });
                                var plus = $('#show-descendants-plus');
                                var minus = $('#show-descendants-minus');
                                if(plus.css('display') === 'none') {
                                    minus.fadeOut('slow', function(){ plus.fadeIn('slow'); });
                                }
                                else {
                                    plus.fadeOut('slow', function(){ minus.fadeIn('slow'); });
                                }
                            });
                        });
                    </script>
                    <span id="links-top"></span>
                    {break}
                {/if}
            {/foreach}
            {foreach $episode.children as $link}
                <p style="margin:0 0 0.3em 1em;">
                    <a href="{$url.site}/doc/{$link.toEp}"
                       {if !$link.isWritten}
                           class="unwritten-episode" data-toggle="tooltip" data-placement="left auto" title="{t}If you feel inspired now, you can add a new leaf to the tree.{/t}">
                           <span class="glyphicon glyphicon-pencil"></span>
                           {$canSubscribe=true}
                       {elseif !$link.isBacklink}
                           ><span class="glyphicon glyphicon-arrow-right"></span>
                       {else}
                           data-toggle="tooltip" data-placement="left auto" title="{t}This will take you to a (possibly) distant relative.{/t}">
                           <span class="glyphicon glyphicon-random"></span>
                       {/if}
                       {$link.title}
                    </a>
                </p>
                {if !empty($link.subtree)}
                    <div class="episode-descendants collapse">
                        {call name=childTree tree=$link.subtree}
                    </div>
                {/if}
            {/foreach}
            {if $canSubscribe && $client.canSubscribe}
                <p>
                    {if !$isSubscribed}
                        <a type="button" href="{$url.site}/doc/subscribe/{$episode.id}" class="btn btn-block btn-default btn-sm">
                            <span class="glyphicon glyphicon-envelope"></span>
                            {t}Notify me when new options are filled.{/t}
                        </a>
                    {else}
                        <a type="button" href="{$url.site}/doc/unsubscribe/{$episode.id}" class="btn btn-block btn-default btn-sm">
                            <span class="glyphicon glyphicon-envelope"></span>
                            {t}Do not notify me anymore when new options are filled.{/t}
                        </a>
                    {/if}
                </p>
            {/if}
            {if !empty($episode.backlinks)}
                <h5>{t}Backlinks to this Episode{/t}</h5>
                <ul>
                    {foreach $episode.backlinks as $link}
                        <li>
                            <a href="{$url.site}/doc/{$link.fromEp}">{$link.title}</a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
            {if $episode.linkable}
                <p class="text-center text-info"><span class="glyphicon glyphicon-info-sign"></span> {t}This episode is linkable.{/t}</p>
            {/if}
        </div>
        <script type="text/javascript">
            $(function() {
                $('#links>p>a').each(function(i, e) {
                    $(e).tooltip();
                });
                $('#report').tooltip();
            });
        </script>
        {if isset($episode.parent)}
            <div class="panel-footer">
                <a href="{$url.site}/doc/{$episode.parent}"><span class="glyphicon glyphicon-circle-arrow-left"></span> {t}Go to the parent episode.{/t}</a>
                <a href="{$url.site}/doc/chain/{$episode.id}/10" class="pull-right"><span class="glyphicon glyphicon-circle-arrow-up"></span> {t}What leads here?{/t}</a>
            </div>
        {/if}
    </div>

    {if $client.isAdministrator or $client.isModerator}
        <script>
            $(function () {
                $('#verification-dialog').modal('hide');
                $('#storyline-dialog').modal('hide');
                var handleStorylineBtn = function () {
                    $('#storyline-dialog').modal('show');
                    return false;
                };
                $('#storyline-btn').click(handleStorylineBtn);

                var typeaheadHandler = function () {
                    var query = $('#storyline-filter').val();
                    if (query.length <= 0) {
                        return;
                    }
                    $.post(
                            '{$url.site}/api/storylines/',
                            {
                                {csrf_json},
                                'query': query
                            },
                            function (data) {
                                var list = $('#storylines');
                                list.empty();
                                data.entries.forEach(function (e) {
                                    var clone = $('#storyline-template').clone(true);
                                    clone.text(e.title);
                                    clone.html(e.id + '&mdash;' + clone.html())
                                    clone.attr('storylineid', e.id);
                                    clone.appendTo(list);
                                    clone.removeClass('hidden');
                                });
                            },
                            'json'
                            );
                };
                $('#storyline-filter').keyup(typeaheadHandler);

                var selectStorylineTag = function () {
                    $('#storyline-dialog').modal('hide');
                    var tagid = $(this).attr('storylineid');
                    
                    $('#verification-dialog').modal('show');
                    $('#do-apply').click(function(){
                        window.location.href = '{$url.site}/maintenance/setstoryline/{$episode.id}/' + tagid + '/false';
                    });
                    $('#do-apply-all').click(function(){
                        window.location.href = '{$url.site}/maintenance/setstoryline/{$episode.id}/' + tagid + '/true';
                    });
                    return false;
                };
                $('#storyline-template').click(selectStorylineTag);
            });
        </script>
        <div class="modal fade" id="storyline-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Select storyline tag{/t}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{t}Select storyline tag{/t}</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="storyline-filter" class="form-control" placeholder="{t}Find storyline tag{/t}">
                        <a class="list-group-item hidden" id="storyline-template" href="#"></a>
                        <div class="list-group" id="storylines">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Abort{/t}</button>
                    </div>
                </div>
            </div>
        </div>
                    
        <div class="modal fade" id="verification-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Verficiation needed{/t}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{t}Verification needed{/t}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            {t}You are about to change the storyline tag for this episode. Shall the child episodes with the same tag be changed as well?{/t}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-default" id="do-apply-all" href="#">{t}Yes, apply to children{/t}</a>
                        <a type="button" class="btn btn-default" id="do-apply" href="#">{t}No, only this one{/t}</a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{t}Abort{/t}</button>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/block}
