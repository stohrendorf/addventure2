{extends 'layout.tpl'}
{block name=title}
    {t}Account locked{/t}
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            {t}Sorry!{/t}
        </div>
        <div class="panel-body">
            {t 1={$smarty.const.ADDVENTURE_MAX_FAILED_LOGINS}}Either you or somebody else tried to login to your account and failed
            at least %1 times.
            For security reasons, your account has been locked.{/t}
        </div>
    </div>
{/block}
