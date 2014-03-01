<?php

require_once 'config.php';
require_once 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$doctrineDbConfig = array(
    'driver' => 'pdo_mysql',
    'user' => ADDVENTURE_DB_USER,
    'password' => ADDVENTURE_DB_PASSWORD,
    'dbname' => ADDVENTURE_DB_SCHEMA
);

if(php_sapi_name() === 'cli') {
    $doctrineCache = new \Doctrine\Common\Cache\ArrayCache();
}
elseif(extension_loaded('apc')) {
    $doctrineCache = new Doctrine\Common\Cache\ApcCache();
}
else {
    $logger->warning('You are seeing this message because you haven\' enabled APC in your server. Please do so to get better performance');
    $doctrineCache = new \Doctrine\Common\Cache\ArrayCache();
}

$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/dao/core"), ADDVENTURE_DEV_MODE, __DIR__ . "/dao/proxies", $doctrineCache);

/**
 * @global Doctrine\ORM\EntityManager $entityManager
 */
$entityManager = EntityManager::create($doctrineDbConfig, $doctrineConfig);


/**
 * @global Log $logger
 */
$logger = Log::singleton('file', 'addventure.log', '');
if(ADDVENTURE_DEV_MODE) {
    define('JSON_FLAGS', JSON_PRETTY_PRINT);
    $logger->setMask(PEAR_LOG_ALL);
    error_reporting(E_ALL);
}
else {
    define('JSON_FLAGS', 0);
    $logger->setMask(PEAR_LOG_WARNING);
    error_reporting(0);
}

function getFullLogData() {
    if(file_exists('addventure.log')) {
        return file_get_contents('addventure.log');
    }
    else {
        return '';
    }
}
