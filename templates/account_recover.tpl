{extends 'layout.tpl'}
{block name=title}
    Recover your password
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            Recover your password
        </div>
        <div class="panel-body">
            <form class="form" action="{$url.site}/account/recover" method="POST">
                {csrf_field}
                <input class="form-control" type="email" name="email" placeholder="Please enter your e-mail address" autofocus required/>
                <button class="btn btn-outline btn-block" type="submit">Recover!</button>
            </form>
            You will receive an E-mail with a new generated password.
            Please change this password <strong>as soon as possible</strong>!
        </div>
    </div>
{/block}
