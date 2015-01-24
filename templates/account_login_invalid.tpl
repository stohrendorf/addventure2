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
            {t}Your login attempt was not successful.{/t}
        </div>
    </div>
{/block}
