<?php

function smarty_modifier_i18n($string) {
    $CI = & get_instance();
    $CI->lang->load('strings', '', false, false, __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    $result = $CI->lang->line($string);
    if($result !== false) {
        return $result;
    }
    $CI->load->library('log');
    $CI->log->warning("Missing translation for string: $string");
    return $string;
}
