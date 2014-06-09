<?php

require_once 'config.php';
require_once 'vendor/autoload.php';

ini_set('mbstring.internal_encoding', 'UTF-8');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$doctrineDbConfig = array(
    'host' => 'localhost',
    'driver' => ADDVENTURE_DB_DRIVER,
    'user' => ADDVENTURE_DB_USER,
    'password' => ADDVENTURE_DB_PASSWORD,
    'dbname' => ADDVENTURE_DB_SCHEMA
);

define('LOG_FILENAME', implode(DIRECTORY_SEPARATOR, array(__DIR__, 'logs', 'addventure.log')));
$logger = new \Monolog\Logger('');
$logger->pushHandler(new Monolog\Handler\StreamHandler(LOG_FILENAME, ADDVENTURE_DEV_MODE ? \Monolog\Logger::DEBUG : \Monolog\Logger::WARNING));
Monolog\ErrorHandler::register($logger);

if(php_sapi_name() !== 'cli' && !extension_loaded('apc')) {
    $logger->warning('You are seeing this message because you haven\' enabled APC in your server. Please do so to get better performance');
}
if(ADDVENTURE_DEV_MODE) {
    $logger->warning('You are running the Addventure in development mode');
    define('JSON_FLAGS', JSON_PRETTY_PRINT);
    error_reporting(E_ALL);
}
else {
    define('JSON_FLAGS', 0);
    error_reporting(0);
}


/**
 * @global \Doctrine\ORM\Configuration $doctrineConfig
 */
$doctrineConfig = Setup::createAnnotationMetadataConfiguration(
        array(__DIR__ . "/dao/core"),
        ADDVENTURE_DEV_MODE,
        __DIR__ . "/dao/proxies");

/**
 * @global \Doctrine\ORM\EntityManager $entityManager
 */
$entityManager = EntityManager::create($doctrineDbConfig, $doctrineConfig);
