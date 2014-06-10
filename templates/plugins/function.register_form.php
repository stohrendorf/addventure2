<?php

function smarty_function_register_form($params, $smarty) {
    $CI = & get_instance();
    $CI->load->helper('form');
    $CI->load->helper('url');
    echo form_open(site_url('/account/register'), array('class' => 'form'));
    ?>
    <input class="form-control" type="text" name="username" placeholder="Your Username" autofocus required/>
    <input class="form-control" type="email" name="email" placeholder="E-Mail (required)" required/>
    <input class="form-control" type="password" name="password" placeholder="Password (required)" required/>
    <button class="btn btn-outline btn-block" type="submit">Register!</button>
    <?php
    echo form_close();
}
