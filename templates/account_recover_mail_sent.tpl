{extends 'layout.tpl'}
{block name=title}
    {t}Mail sent{/t}
{/block}
{block name=body}
    <div class="panel panel-success">
        <div class="panel-heading">
            {t}Mail sent{/t}
        </div>
        <div class="panel-body">
            {t escape=no}A mail has been sent to your E-mail address, containing a freshly
            generated password.
            <strong>Change this password as soon as possible!</strong>{/t}
        </div>
    </div>
{/block}
