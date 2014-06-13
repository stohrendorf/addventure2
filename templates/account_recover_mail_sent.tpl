{extends 'layout.tpl'}
{block name=title}
    Mail sent
{/block}
{block name=body}
    <div class="panel panel-success">
        <div class="panel-heading">
            Mail sent
        </div>
        <div class="panel-body">
            A mail has been sent to your E-mail address, containing a freshly
            generated password.
            <strong>Change this password as soon as possible!</strong>
        </div>
    </div>
{/block}
