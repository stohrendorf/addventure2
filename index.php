<?php

require 'doctrine-bootstrap.php';

$system_path = 'system';
$application_folder = 'application';

// Set the current directory correctly for CLI requests
if(defined('STDIN')) {
    chdir(dirname(__FILE__));
}

if(realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path) . '/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/') . '/';

// Is the system path correct?
if(!is_dir($system_path)) {
    exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: " . pathinfo(__FILE__, PATHINFO_BASENAME));
}

// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// Path to the front controller (this file)
define('FCPATH', str_replace(SELF, '', __FILE__));

// Name of the "system folder"
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));


// The path to the "application" folder
if(is_dir($application_folder)) {
    define('APPPATH', $application_folder . '/');
}
else {
    if(!is_dir(BASEPATH . $application_folder . '/')) {
        exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: " . SELF);
    }

    define('APPPATH', BASEPATH . $application_folder . '/');
}
define('TEMPLATEPATH', str_replace('\\', '/', realpath('templates')) . '/');
define('VENDORPATH', str_replace('\\', '/', realpath('vendor')) . '/');

function getLanguageFromHttp()
{
    // from: http://www.thefutureoftheweb.com/blog/use-accept-language-header
    $langs = array();

    if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return 'en';
    }
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

    if(empty($lang_parse[1])) {
        return 'en';
    }
    // create a list like "en" => 0.8
    $langs = array_combine($lang_parse[1], $lang_parse[4]);

    // set default to 1 for any without q factor
    foreach($langs as $lang => $val) {
        if($val === '')
            $langs[$lang] = 1;
    }

    // sort list based on value	
    arsort($langs, SORT_NUMERIC);
    reset($langs);
    return key($langs);
}

function setGettextLang() {
    $lang = getLanguageFromHttp();
    putenv("LANG=$lang");
    setlocale(LC_ALL, $lang);
    // Set the text domain as 'messages'
    bindtextdomain('messages', APPPATH . '/language/');
    textdomain('messages');
}

setGettextLang();

require_once BASEPATH . 'core/CodeIgniter.php';
