{extends 'layout.tpl'}
{block name=title}
    {t}Sorry...{/t}
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            {t}Sorry!{/t}
        </div>
        <div class="panel-body">
            {t escape=no 1={$url.site}}The data you supplied for registration was a bit weird.
            Please check the spelling of your e-mail address and that you have entered a password.
            You may <a href="%1/account/register">try again</a> now.{/t}
        </div>
    </div>
{/block}
