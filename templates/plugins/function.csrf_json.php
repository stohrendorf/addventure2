<?php

function smarty_function_csrf_json() {
    $CI = &get_instance();
    echo "'", addcslashes($CI->security->get_csrf_token_name(), "'"), "':";
    echo "'", addcslashes($CI->security->get_csrf_hash(), "'"), "'";
}
