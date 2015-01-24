{extends 'layout.tpl'}
{block name=title}
    {t}Account registration expired{/t}
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            {t}Sorry!{/t}
        </div>
        <div class="panel-body">
            {t 1={$smarty.const.ADDVENTURE_MAX_AWAITING_APPROVAL_HOURS}}
            You waited too long to activate your pending account; it has been
            deleted.  If you still want to register, try again and verify your
            account within %1 hours.{/t}
        </div>
    </div>
{/block}
