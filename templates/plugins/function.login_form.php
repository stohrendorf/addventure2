<?php

function smarty_function_login_form() {
    $CI = & get_instance();
    $CI->load->helper('form');
    $CI->load->helper('url');
    echo form_open(site_url('/account/login'), array('class' => 'navbar-form form-signin'));
    ?>
    <h5 class="form-signin-heading"><?php echo $CI->lang->line('LOG_IN_OR_register'); ?> <a href="<?php echo site_url('/account/register'); ?>"><?php echo $CI->lang->line('log_in_or_REGISTER'); ?></a>.</h5>
    <input class="form-control" type="text" placeholder="Username" name="username" required autofocus/>
    <input class="form-control" type="password" placeholder="Password" name="password" required/>
    <div class="checkbox">
        <label>
            <input class="form-control" type="checkbox" name="remember" id="remember" value="yes"/> <?php echo $CI->lang->line('Remember me'); ?>
        </label>
    </div>
    <button class="btn btn-outline btn-block" type="submit"><?php echo $CI->lang->line('do_login'); ?></button>
    <a href="<?php echo site_url('/account/recover'); ?>"><?php echo $CI->lang->line('forgot_password'); ?></a>
    <?php
    echo form_close();
}
