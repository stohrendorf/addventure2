{extends 'layout.tpl'}
{block name=title}
    Account locked
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            Sorry!
        </div>
        <div class="panel-body">
            Either you or somebody else tried to login to your account and failed
            at least {$smarty.const.ADDVENTURE_MAX_FAILED_LOGINS} times.
            For security reasons, your account has been locked.
        </div>
    </div>
{/block}
