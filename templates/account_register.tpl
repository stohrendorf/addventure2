{extends 'layout.tpl'}
{block name=title}
    {t}Register{/t}
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            {t}Register as an Awesome Author{/t}
        </div>
        <div class="panel-body">
            <form class="form" action="{$url.site}/account/register" method="POST">
                {csrf_field}
                <input class="form-control" type="text" name="username" placeholder="{t}Your Username{/t}" autofocus required/>
                <input class="form-control" type="email" name="email" placeholder="{t}E-Mail (required){/t}" required/>
                <input class="form-control" type="password" name="password" placeholder="{t}Password (required){/t}" required/>
                <button class="btn btn-outline btn-block" type="submit">{t}Register!{/t}</button>
            </form>
            <ul>
                <li>{t}Your E-Mail address will not be given to any third party.{/t}</li>
                <li>{t}Your E-Mail will only be used for authentication on this site and for notifications <em>you</em> choose.{/t}</li>
                <li>{t escape=no}After you entered valid information and hit the &raquo;Register!&laquo; button, you will receive an E-Mail with an activation link.{/t}</li>
            </ul>
        </div>
    </div>
{/block}
