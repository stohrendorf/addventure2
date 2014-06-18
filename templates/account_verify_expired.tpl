{extends 'layout.tpl'}
{block name=title}
    Account registration expired
{/block}
{block name=body}
    <div class="panel panel-danger">
        <div class="panel-heading">
            Sorry!
        </div>
        <div class="panel-body">
            You waited too long to activate your pending account; it has been
            deleted.  If you still want to register, try again and verify your
            account within {$smarty.const.ADDVENTURE_MAX_AWAITING_APPROVAL_HOURS}
            hours.
        </div>
    </div>
{/block}
