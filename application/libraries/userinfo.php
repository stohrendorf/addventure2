<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Userinfo {

    /**
     * @var \addventure\User
     */
    public $user = null;

    public function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        if($CI->session->userdata('userid') !== FALSE) {
            $userid = $CI->session->userdata('userid');
            global $entityManager;
            $this->user = $entityManager->find('addventure\User', $userid);
        }
    }

}
