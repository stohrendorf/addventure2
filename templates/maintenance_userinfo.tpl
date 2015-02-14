{extends 'layout.tpl'}
{block name=title}
    {t}User info{/t}
{/block}
{block name=headElements}
    <style type="text/css">
        #users-dialog .modal-dialog,
        #users-dialog .modal-content {
            height: 90%;
        }

        #users-dialog .modal-body {
            max-height: calc(100% - 120px);
            overflow-y: scroll;
        }
    </style>
{/block}
{block name=body}
    <table class="table table-striped table-condensed" style="margin:auto; width:auto;">
        <tbody>
            <tr><td>{t}ID{/t}</td><td>{$user.userid}</td></tr>
            <tr><td>{t}Username{/t}</td><td>{$user.username}</td></tr>
            {if empty($user.email)}
                <tr><td>{t}E-Mail{/t}</td><td>{t}Not set{/t}</td></tr>
            {else}
                <tr><td>{t}E-Mail{/t}</td><td><a href="mailto:{$user.email|escape}">{$user.email|escape}</a></td></tr>
                    {/if}
            <tr><td>{t}Registered since{/t}</td><td>{$user.registeredSince}</td></tr>
            <tr>
                <td>{t}Role{/t}</td>
                <td>
                    <a href="#" class="disabled btn {if $user.role==0}btn-success{else}btn-default{/if}">{t}Anonymous{/t}</a>
                    <a href="#" class="disabled btn {if $user.role==1}btn-success{else}btn-default{/if}">{t}Awaiting approval{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/2" class="btn {if $user.role==2}btn-success{else}btn-default{/if}">{t}Registered{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/3" class="btn {if $user.role==3}btn-success{else}btn-default{/if}">{t}Moderator{/t}</a>
                    <a href="{$url.site}/maintenance/setrole/{$user.userid}/4" class="btn {if $user.role==4}btn-success{else}btn-default{/if}">{t}Administrator{/t}</a>
                </td>
            </tr>
            {if $user.role==0}
                <tr><td></td><td><a href="#" class="btn btn-primary" id="merge-btn">{t}Merge this user{/t}</a></td></tr>
            {/if}
            <tr {if $user.failedLogins>0}class="danger"{/if}>
                <td>{t}Failed logins{/t}</td>
                <td>{$user.failedLogins} (<a href="{$url.site}/maintenance/resetlogins/{$user.userid}">{t}Reset{/t}</a>)</td>
            </tr>
            <tr {if $user.blocked}class="danger"{/if}>
                <td>{t}Status{/t}</td>
                <td>
                    {if $user.blocked}
                        {t}Blocked{/t} (<a href="{$url.site}/maintenance/unblock/{$user.userid}">{t}Unblock{/t}</a>)
                    {else}
                        {t}Unblocked{/t} (<a href="{$url.site}/maintenance/block/{$user.userid}">{t}Block{/t}</a>)
                    {/if}
                </td>
            </tr>
            <tr><td>{t}Episodes{/t}</td><td><a href="{$url.site}/recent/user/{$user.userid}">{t}Show{/t}</a></td></tr>
        </tbody>
    </table>
    {if !empty($notifications)}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3>{t}Subscriptions{/t}</h3>
            </div>
            <div class="panel-body">
                <ul>
                    {foreach $notifications as $n}
                        <li>
                            <a href="{$url.site}/maintenance/deletesubscription/{$user.userid}/{$n.episode.id}"><span class="glyphicon glyphicon-trash"></span></a>
                            <a href="{$url.site}/doc/{$n.episode.id}">{$n.episode.autoTitle|escape}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
    {if $user.role==0}
        <script>
            $(function () {
                $('#users-dialog').modal('hide');
                var handleMergeButton = function () {
                    $('#users-dialog').modal('show');
                    return false;
                };
                $('#merge-btn').click(handleMergeButton);

                var typeaheadHandler = function () {
                    var query = $('#users-filter').val();
                    if (query.length <= 0) {
                        return;
                    }
                    $.post(
                        '{$url.site}/api/users/',
                        {
                            {csrf_json},
                            'query': query
                        },
                        function (data) {
                            var list = $('#users');
                            list.empty();
                            data.entries.forEach(function (e) {
                                var clone = $('#users-template').clone(true);
                                clone.text(e.username);
                                clone.html(e.id + '&mdash;' + clone.html())
                                clone.attr('userid', e.id);
                                clone.appendTo(list);
                                clone.removeClass('hidden');
                            });
                        },
                        'json'
                    );
                };
                $('#users-filter').keyup(typeaheadHandler);

                $('#verification-dialog').modal('hide');
                var setBacklink = function () {
                    $('#users-dialog').modal('hide');
                    
                    var uid = $(this).attr('userid');
                    
                    var question = $('#verification-dialog').modal('show');
                    $('#do-merge').click(function(){
                        window.location.href = '{$url.site}/maintenance/mergeuser/' + uid + '/' + {$user.userid};
                    });
                    
                    return false;
                };
                $('#users-template').click(setBacklink);
            });
        </script>
        <div class="modal fade" id="users-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Select target user{/t}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{t}Select target user{/t}</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="users-filter" class="form-control" placeholder="{t}Username or ID{/t}">
                        <a class="list-group-item hidden" id="users-template" href="#"></a>
                        <div class="list-group" id="users">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Abort{/t}</button>
                    </div>
                </div>
            </div>
        </div>
                    
        <div class="modal fade" id="verification-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Warning{/t}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{t}Warning{/t}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            {t}This operation cannot be undone, as the user you are lookin at will be fully integrated into the target user and then get deleted. Are you sure you want to merge this user?{/t}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-default" id="do-merge" href="#">{t}Merge{/t}</a>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">{t}Abort{/t}</button>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/block}
