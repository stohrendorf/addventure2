<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function createSmarty() {
    $smarty = new Smarty();
    $smarty->setTemplateDir(TEMPLATEPATH);
    $smarty->setCacheDir(TEMPLATEPATH . '/cache');
    $smarty->setCompileDir(TEMPLATEPATH . '/compiled');
    $smarty->setConfigDir(TEMPLATEPATH . '/config');
    $smarty->assign('url', array(
        'base' => rtrim(base_url(), '/'),
        'site' => site_url(),
        'jquery' => base_url('vendor/frameworks/jquery/jquery.min.js'),
        'bootstrap' => array(
            'js' => base_url('vendor/twbs/bootstrap/dist/js/bootstrap.min.js'),
            'css' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'),
            'theme' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css')
        )
    ));
    return $smarty;
}
