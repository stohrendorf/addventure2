{extends 'account_changepassword.tpl'}
{block name=body prepend}
    <div class="alert alert-danger">
        Something went wrong.
        Check that you entered your old password and that you entered your new password <em>twice</em> exactly the same.
    </div>
{/block}
