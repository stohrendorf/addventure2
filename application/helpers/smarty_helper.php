<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if(!function_exists('createSmarty')) {

    function createSmarty() {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->library('userinfo');

        $smarty = new Smarty();

        if($CI->userinfo->user) {
            $smarty->assign('client', $CI->userinfo->user->toSmarty());
        }
        else {
            $smarty->assign('client', \addventure\User::defaultSmarty());
        }

        $smarty->setTemplateDir(TEMPLATEPATH);
        $smarty->setCacheDir(TEMPLATEPATH . '/cache');
        $smarty->setCompileDir(TEMPLATEPATH . '/compiled');
        $smarty->setConfigDir(TEMPLATEPATH . '/config');
        $smarty->addPluginsDir(TEMPLATEPATH . '/plugins');
        $smarty->assign('url', array(
            'base' => rtrim(base_url(), '/'),
            'site' => site_url(),
            'current' => $CI->uri->uri_string(),
            'jquery' => base_url('vendor/frameworks/jquery/jquery.min.js'),
            'ckeditor' => base_url('vendor/ckeditor/ckeditor.js'),
            'bootstrap' => array(
                'js' => base_url('vendor/twbs/bootstrap/dist/js/bootstrap.min.js'),
                'css' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'),
                'theme' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css')
            )
        ));
        return $smarty;
    }

}