{extends 'layout.tpl'}
{block name=title}
    Register
{/block}
{block name=body}
    <div class="panel panel-primary">
        <div class="panel-heading">
            Register as an Awesome Author
        </div>
        <div class="panel-body">
            {register_form}
            <ul>
                <li>Your E-Mail address will not be given to any third party.</li>
                <li>Your E-Mail will only be used for authentication on this site and for notifications <em>you</em> choose.</li>
                <li>After you entered valid information and hit the &raquo;Register!&laquo; button, you will receive an E-Mail with an activation link.</li>
            </ul>
        </div>
    </div>
{/block}
