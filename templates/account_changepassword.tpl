{extends 'layout.tpl'}
{block name=title}
    {t}Change password{/t}
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            {t}Change password{/t}
        </div>
        <div class="panel-body">
            <form class="form" action="{$url.site}/account/changepassword" method="POST">
                {csrf_field}
                <input class="form-control" type="password" name="oldpassword" placeholder="{t}Your old password{/t}" autofocus required/>
                <input class="form-control" type="password" name="newpassword1" placeholder="{t}Your new password{/t}" required/>
                <input class="form-control" type="password" name="newpassword2" placeholder="{t}Repeat your new password{/t}" required/>
                <button class="btn btn-outline btn-block" type="submit">{t}Change!{/t}</button>
            </form>
        </div>
    </div>
{/block}
