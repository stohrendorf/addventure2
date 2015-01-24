{extends 'layout.tpl'}
{block name=title}
    {t}Recover your password{/t}
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            {t}Recover your password{/t}
        </div>
        <div class="panel-body">
            <form class="form" action="{$url.site}/account/recover" method="POST">
                {csrf_field}
                <input class="form-control" type="email" name="email" placeholder="{t}Please enter your e-mail address{/t}" autofocus required/>
                <button class="btn btn-outline btn-block" type="submit">{t}Recover!{/t}</button>
            </form>
            {t escape=no}You will receive an E-mail with a new generated password.
            Please change this password <strong>as soon as possible</strong>!{/t}
        </div>
    </div>
{/block}
