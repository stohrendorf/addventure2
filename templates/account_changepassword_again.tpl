{extends 'account_changepassword.tpl'}
{block name=body prepend}
    <div class="alert alert-danger">
        {t escape=no}Something went wrong.
        Check that you entered your old password and that you entered your new password <em>twice</em> exactly the same.{/t}
    </div>
{/block}
