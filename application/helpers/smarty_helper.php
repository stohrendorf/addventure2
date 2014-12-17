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
            ),
            'jqplot' => array(
                'excanvas' => base_url('vendor/jqplot/excanvas.min.js'),
                'js' => base_url('vendor/jqplot/jquery.jqplot.min.js'),
                'css' => base_url('vendor/jqplot/jquery.jqplot.min.css'),
                'categoryAxisRenderer' => base_url('vendor/jqplot/plugins/jqplot.categoryAxisRenderer.min.js'),
                'canvasAxisTickRenderer' => base_url('vendor/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js'),
                'barRenderer' => base_url('vendor/jqplot/plugins/jqplot.barRenderer.min.js'),
                'canvasTextRenderer' => base_url('vendor/jqplot/plugins/jqplot.canvasTextRenderer.min.js')
            )
        ));
        return $smarty;
    }

}
