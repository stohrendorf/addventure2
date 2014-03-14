{extends 'layout.tpl'}
{block name=title}
    Sorry...
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            Sorry!
        </div>
        <div class="panel-body">
            The data you supplied for registration was a bit weird.
            Please check the spelling of your e-mail address and that you have entered a password.
            You may <a href="{$url.site}/account/register">try again</a> now.
        </div>
    </div>
{/block}
