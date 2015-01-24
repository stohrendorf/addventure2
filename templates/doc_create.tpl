{extends 'layout.tpl'}
{block name=headElements}
    <script src="{$url.ckeditor}"></script>
    <script type="text/javascript">
        CKEDITOR.config.removePlugins = 'div,preview,newpage,iframe,flash,templates,forms,colordialog,table,tabletools,table,pagebreak,filebrowser,save,elementspath,print,showblocks,showborders,sourcearea,tab';
    </script>
{/block}

{block name=title}{t 1=$docid}Create episode %1{/t}{/block}

{block name=body}
    <h3><span class="glyphicon glyphicon-edit"></span> {t 1=$docid}Create episode %1{/t}</h3>
    {if isset($parenttext)}
        <div class="panel panel-default">
            <div class="panel-body" style="background-color:#fffcee;">{$parenttext}</div>

            {if !empty($parentnotes)}
                <div class="panel-footer">
                    <div class="panel panel-info">
                        <div class="panel-heading">Author's Notes</div>
                        <div class="panel-body">{$parentnotes|smileys}</div>
                    </div>
                </div>
            {/if}
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
                        <textarea class="ckeditor" name="preNotes">{$prenotes}</textarea>
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
                        <div class="form-group"><input class="form-control" type="text" placeholder="{t}The Awesome Episode Title{/t}" name="title"/>{$title}</div>
                        <textarea class="ckeditor" name="content">{$content}</textarea>
                        <div class="form-group">
                            {if empty($options)}
                                {$options=array('','')}
                            {/if}
                            {$i=0}
                            {foreach $options as $option}
                                {$i=$i+1}
                                <input class="form-control" type="text" placeholder="{t 1=$i}Option %1{/t}" name="options[]"/>
                            {/foreach}

                        </div>
                    </div>
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
                        <textarea class="ckeditor" name="postNotes">{$postnotes}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="button form-control default"><span class="glyphicon glyphicon-share"></span> {t}Publish!{/t}</button>
    </form>
{/block}