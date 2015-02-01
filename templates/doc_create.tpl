{extends 'layout.tpl'}
{block name=headElements}
    <script src="{$url.ckeditor}"></script>
    <script type="text/javascript">
        CKEDITOR.config.removePlugins = 'div,preview,newpage,iframe,flash,templates,forms,colordialog,table,tabletools,table,pagebreak,filebrowser,save,elementspath,print,showblocks,showborders,sourcearea,tab';
        CKEDITOR.config.contentsCss = '{$url.base}/ckeditor.css'
    </script>
    <style type="text/css">
        .modal-dialog,
        .modal-content {
            height: 90%;
        }

        .modal-body {
            max-height: calc(100% - 120px);
            overflow-y: scroll;
        }
    </style>
{/block}

{block name=title}
    {if $isCreation}
        {t 1=$episode.id}Create episode %1{/t}
    {else}
        {t 1=$episode.id}Edit episode %1{/t}
    {/if}
{/block}

{block name=body}
    <h3>
        <span class="glyphicon glyphicon-edit"></span>
        {if $isCreation}
            {t 1=$episode.id}Create episode %1{/t}
        {else}
            {t 1=$episode.id}Edit episode %1{/t}
        {/if}
    </h3>
    {if $parent}
        <div class="panel panel-default">
            <div class="panel-body" style="background-color:#fffcee;">{$parent.text}</div>

            {if !empty($parent.postNotes)}
                <div class="panel-footer">
                    <h4><b>{t}Author's Notes{/t}</b></h4>
                    <p>{$parent.postNotes|smileys}</p>
                </div>
            {/if}
        </div>
    {/if}
    {if isset($errors)}
        <div class="panel panel-danger">
            <div class="panel-heading">
                {t}Keep calm!{/t}
            </div>
            <div class="panel-body">
                {t}There are some requirements for publishing a story. Please solve these problems first:{/t}
                <ul>
                    {foreach $errors as $error}
                        <li>{$error|escape}</li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {/if}
    <form method="POST">
        {csrf_field}
        <div class="list-group" id="all-edit">
            <div class="list-group-item">
                <div class="list-group-item-heading">
                    <a data-toggle="collapse" data-parent="#all-edit" href="#collapseA"style="display:block;">
                        <span class="glyphicon glyphicon-collapse-down"></span> {t}Something you want to say to your readers?{/t}
                    </a>
                </div>
                <div id="collapseA" class="list-group-item-text collapse">
                    <div class="panel-body">
                        <textarea class="ckeditor" name="preNotes">{$episode.preNotes|default:''}</textarea>
                    </div>
                </div>
            </div>
            <div class="list-group-item">
                <div class="list-group-item-heading">
                    <a data-toggle="collapse" data-parent="#all-edit" href="#collapseB" style="display:block;">
                        <span class="glyphicon glyphicon-collapse-down"></span> {t}Your Awesome Episode{/t}
                    </a>
                </div>
                <div id="collapseB" class="list-group-item-text collapse in">
                    <div class="panel-body">
                        <div class="form-group"><input class="form-control" type="text" placeholder="{t}The Awesome Episode Title{/t}" name="title" value="{$episode.title|default:''}"/></div>
                        <textarea class="ckeditor" name="content">{$episode.text|default:''}</textarea>
                        <div class="form-group" id="options">
                            {if $isCreation}
                                <div class="input-group hidden" id="option-template">
                                    <a href="#" class="rm-option-btn input-group-addon" title="{t}Remove this option{/t}"><span class="glyphicon glyphicon-trash"></span></a>
                                    <a href="#" class="backlink-btn input-group-addon" title="{t}Create a backlink{/t}"><span class="glyphicon glyphicon-random"></span> <span id="backlink-target"></span></a>
                                    <input class="option-text form-control" type="text" placeholder="{t}Link title{/t}" name="options[]" value=""/>
                                    <input class="option-target" type="hidden" name="targets[]" value=""/>
                                </div>
                            {/if}
                            {if empty($options)}
                                {$options=array()}
                            {/if}
                            {$i=0}
                            {foreach $options as $option}
                                {$i=$i+1}
                                <div class="input-group">
                                    {if $isCreation}
                                        <a href="#" class="rm-option-btn input-group-addon" title="{t}Remove this option{/t}">
                                            <span class="glyphicon glyphicon-trash"></span>
                                        </a>
                                        <a href="#" class="backlink-btn input-group-addon" title="{t}Create a backlink{/t}">
                                            <span class="glyphicon glyphicon-random"></span>
                                            <span id="backlink-target">{$option['target']}</span>
                                        </a>
                                    {else}
                                        <span class="backlink-btn input-group-addon">
                                            <span id="backlink-target">{$option['target']}</span>
                                        </span>
                                    {/if}
                                    <input class="option-text form-control" type="text" placeholder="{t}Link title{/t}" name="options[]" value="{$option['title']|escape}"/>
                                    <input class="option-target" type="hidden" name="targets[]" value="{$option['target']}"/>
                                </div>
                            {/foreach}
                        </div>
                        {if $isCreation}
                            <center class="form-inline">
                                <div class="form-group">
                                    <button class="btn btn-default add-option-btn" href="#">
                                        <span class="glyphicon glyphicon-plus"></span>
                                        {t}New link{/t}
                                    </button>
                                </div>
                                <span style="margin:1em;"></span>
                                <div class="input-group checkbox">
                                    <label>
                                        <input type="checkbox" value="true" name="linkable" {if $episode.linkable}checked{/if}/>
                                        {t}Allow back-links to this episode.{/t}
                                    </label>
                                </div>
                                <span style="margin:1em;"></span>
                                <div class="form-group">
                                    <input class="form-control" type="text" placeholder="{t}Signed off by{/t}" name="signedoff" value="{$signedoff}" style="min-width:300px;"/>
                                </div>
                            </center>
                        {/if}
                    </div>
                    {if $isCreation}
                        <script>
                            $(function () {
                                var handleRmBtn = function () {
                                    var btn = $(this);
                                    var inp = btn.parent();
                                    inp.remove();
                                    return false;
                                };
                                $('.rm-option-btn').click(handleRmBtn);

                                var handleAddButton = function () {
                                    var cloned = $('#option-template').clone(true);
                                    cloned.removeClass('hidden');
                                    cloned.appendTo($('#options'));
                                    return false;
                                };
                                $('.add-option-btn').click(handleAddButton);

                                var dlg = $('#backlinks-dialog');
                                dlg.modal('hide');
                                var selectedOption = null;
                                var handleBacklinkButton = function () {
                                    selectedOption = $(this);
                                    dlg.modal('show');
                                    return false;
                                };
                                $('.backlink-btn').click(handleBacklinkButton);

                                var typeaheadHandler = function () {
                                    var query = $('#backlinks-filter').val();
                                    if (query.length <= 0) {
                                        return;
                                    }
                                    $.post(
                                            '{$url.site}/api/backlinks/',
                                            {
                                                {csrf_json},
                                                'query': query
                                            },
                                            function (data) {
                                                var list = $('#backlinks');
                                                list.empty();
                                                data.entries.forEach(function (e) {
                                                    var clone = $('#backlink-template').clone(true);
                                                    clone.text(e.title);
                                                    clone.html(e.id + '&mdash;' + clone.html())
                                                    clone.attr('target', e.id);
                                                    clone.appendTo(list);
                                                    clone.removeClass('hidden');
                                                });
                                            },
                                            'json'
                                            );
                                };
                                $('#backlinks-filter').keyup(typeaheadHandler);

                                var setBacklink = function () {
                                    dlg.modal('hide');
                                    var t = $(this).attr('target');
                                    selectedOption.children('#backlink-target').text(' ' + t);
                                    selectedOption.parent().children('.option-target').val(t);
                                    return false;
                                };
                                $('#backlink-template').click(setBacklink);
                            });
                        </script>
                    {/if}
                </div>
            </div>
            <div class="list-group-item">
                <div class="list-group-item-heading">
                    <a data-toggle="collapse" data-parent="#all-edit" href="#collapseC" style="display:block;">
                        <span class="glyphicon glyphicon-collapse-down"></span> {t}Something you want to say to fellow writers?{/t}
                    </a>
                </div>
                <div id="collapseC" class="list-group-item-text collapse">
                    <div class="panel-body">
                        <textarea class="ckeditor" name="postNotes">{$episode.postNotes}</textarea>
                    </div>
                </div>
            </div>
        </div>
        {if $isCreation}
            <center>
                <div class="form-inline">
                    <button type="submit" class="button form-control default btn-success"><span class="glyphicon glyphicon-share"></span> {t}Publish{/t}</button>
                    {if $parent}
                        {$backurl="{$url.site}/doc/{$parent.id}"}
                    {else}
                        {$backurl={$url.site}}
                    {/if}
                    <a href="{$backurl}" class="button form-control default btn-danger" style="text-decoration: none; color: white;"><span class="glyphicon glyphicon-remove"></span> {t}Abort{/t}</a>
                </div>
            </center>
        {else}
            <center>
                <div class="form-inline">
                    <button type="submit" class="button form-control default btn-success"><span class="glyphicon glyphicon-ok"></span> {t}Save{/t}</button>
                    <a href="{$url.site}/doc/{$episode.id}" class="button form-control default btn-danger" style="text-decoration: none; color: white;"><span class="glyphicon glyphicon-remove"></span> {t}Abort{/t}</a>
                </div>
            </center>
        {/if}
        <div style="min-height: 500px;"></div>
    </form>

    {if $isCreation}
        <div class="modal fade" id="backlinks-dialog" role="dialog" aria-labelledby="" aria-hidden="true" title="{t}Select backlink target{/t}">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{t}Select backlink target{/t}</h4>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="backlinks-filter" class="form-control" placeholder="{t}Episode title or ID{/t}">
                        <a class="list-group-item hidden" id="backlink-template" href="#"></a>
                        <div class="list-group" id="backlinks">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Abort{/t}</button>
                    </div>
                </div>
            </div>
        </div>
    {/if}
{/block}