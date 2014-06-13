<?php

function smarty_function_csrf_field() {
    $CI = &get_instance();
    echo '<input type="hidden" name="',
    htmlspecialchars($CI->security->get_csrf_token_name()),
    '" value="',
    htmlspecialchars($CI->security->get_csrf_hash()),
    '" />';
}
