{extends 'layout.tpl'}
{block name=title}
    Change password
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            Change password
        </div>
        <div class="panel-body">
            <form class="form" action="{$url.site}/account/changepassword" method="POST">
                {csrf_field}
                <input class="form-control" type="password" name="oldpassword" placeholder="Your old password" autofocus required/>
                <input class="form-control" type="password" name="newpassword1" placeholder="Your new password" required/>
                <input class="form-control" type="password" name="newpassword2" placeholder="Repeat your new password" required/>
                <button class="btn btn-outline btn-block" type="submit">Change!</button>
            </form>
        </div>
    </div>
{/block}
