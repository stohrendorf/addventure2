<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Userinfo {

    /**
     * @var \addventure\User|null
     */
    public $user = null;

    public function __construct() {
        $CI = & get_instance();
        $CI->load->library('session');
        if($CI->session->userdata('userid') !== FALSE) {
            $userid = $CI->session->userdata('userid');
            $CI->load->library('em');
            $this->user = $CI->em->findUser($userid);
        }
    }

}
