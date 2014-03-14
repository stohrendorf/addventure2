<?php

function smarty_function_login_form($params, $smarty) {
    $CI = & get_instance();
    $CI->load->helper('form');
    $CI->load->helper('url');
    echo form_open(site_url('/account/login'), array('class' => 'navbar-form form-signin'));
    ?>
    <h5 class="form-signin-heading">Log in or <a href="<?php echo site_url('/account/register'); ?>">register</a>.</h5>
    <input class="form-control" type="email" placeholder="E-Mail" name="email" required autofocus/>
    <input class="form-control" type="password" placeholder="Password" name="password" required/>
    <div class="form-group">
        <input type="checkbox" name="remember" id="remember" value="yes"/> 
        <label class="checkbox" for="remember">Remember me</label>
    </div>
    <button class="btn btn-outline btn-block" type="submit">Login!</button>
    <?php
    echo form_close();
}
