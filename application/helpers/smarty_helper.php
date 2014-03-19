<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if(!function_exists('createSmarty')) {

    function createSmarty() {
        $CI = & get_instance();
        $CI->load->helper('url');
        $CI->load->library('session');
        $role = \addventure\User::Anonymous;
        $blocked = false;
        $userid = -1;
        $email = '';
        if($CI->session->userdata('userid') !== FALSE) {
            global $entityManager;
            $userid = $CI->session->userdata('userid');
            $user = $entityManager->find('addventure\User', $userid);
            if($user) {
                $blocked = $user->getBlocked();
                $role = $user->getRole();
                $email = $user->getEmail();
            }
            else {
                $userid = -1;
            }
        }

        $smarty = new Smarty();
        $smarty->setTemplateDir(TEMPLATEPATH);
        $smarty->setCacheDir(TEMPLATEPATH . '/cache');
        $smarty->setCompileDir(TEMPLATEPATH . '/compiled');
        $smarty->setConfigDir(TEMPLATEPATH . '/config');
        $smarty->addPluginsDir(TEMPLATEPATH . '/plugins');
        $smarty->assign('url', array(
            'base' => rtrim(base_url(), '/'),
            'site' => site_url(),
            'jquery' => base_url('vendor/frameworks/jquery/jquery.min.js'),
            'ckeditor' => base_url('vendor/ckeditor/ckeditor.js'),
            'bootstrap' => array(
                'js' => base_url('vendor/twbs/bootstrap/dist/js/bootstrap.min.js'),
                'css' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'),
                'theme' => base_url('vendor/twbs/bootstrap/dist/css/bootstrap-theme.min.css')
            )
        ));
        $smarty->assign('client', array(
            'blocked' => $blocked,
            'userid' => $userid,
            'role' => $role,
            'email' => $email
        ));
        return $smarty;
    }

}
