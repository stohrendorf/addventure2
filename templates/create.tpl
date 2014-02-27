{extends 'layout.tpl'}
{block name=headElements}
    <script src="vendor/ckeditor/ckeditor.js"></script>
    <script>
        CKEDITOR.config.removePlugins = 'div,preview,newpage,iframe,flash,templates,forms,colordialog,table,tabletools,table,pagebreak,filebrowser,save,elementspath,print,showblocks,showborders,sourcearea,tab';
    </script>
{/block}

{block name=title}Create FOO{/block}

{block name=body}
    <form>
        <div class="panel-group" id="all-edit">
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#all-edit" href="#collapseA" style="display:block;">Your Notes before the Content</a></h4></div>
                <div id="collapseA" class="panel-collapse collapse">
                    <div class="panel-body">
                        <textarea class="ckeditor" name="preNotes"></textarea>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#all-edit" href="#collapseB" style="display:block;">Your Awesome Episode</a></h4></div>
                <div id="collapseB" class="panel-collapse collapse in">
                    <div class="panel-body">
                        <div class="form-group"><input class="form-control" type="text" placeholder="The Awesome Episode Title" name="title"/></div>
                        <textarea class="ckeditor" name="content"></textarea>
                        <div class="form-group">
                            <input class="form-control" type="text" placeholder="Option 1" name="options"/>
                            <input class="form-control" type="text" placeholder="Option 2" name="options"/>
                            <input class="form-control" type="text" placeholder="Option 3" name="options"/>
                            <input class="form-control" type="text" placeholder="Option 4" name="options"/>
                            <input class="form-control" type="text" placeholder="Option 5" name="options"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#all-edit" href="#collapseC" style="display:block;">Your Notes after the Content</a></h4></div>
                <div id="collapseC" class="panel-collapse collapse">
                    <div class="panel-body">
                        <textarea class="ckeditor" name="postNotes"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
{/block}