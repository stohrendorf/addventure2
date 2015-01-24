{extends 'layout.tpl'}
{block name=title}
    {t}One more step...{/t}
{/block}
{block name=body}
    <div class="panel panel-success">
        <div class="panel-heading">
            {t}Just one more step...{/t}
        </div>
        <div class="panel-body">
            {t}A mail has been sent out to the e-mail address you supplied, containing
            a special ugly-looking link back to this site.
            You have to load this link in your browser to tell us that the supplied e-mail
            address belongs to you and to activate your pending account.{/t}
        </div>
    </div>
{/block}
