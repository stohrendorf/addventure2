<?php

function smarty_modifier_smileys($string) {
    $CI = & get_instance();
    $CI->load->helper('smiley');
    $CI->load->helper('url');
    return parse_smileys($string, base_url('images/smileys'));
}
