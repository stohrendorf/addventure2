<?php

(@include_once 'config.php') or require_once 'config-testing.php';
require_once 'vendor/autoload.php';

ini_set('mbstring.internal_encoding', 'UTF-8');
if(ENVIRONMENT !== 'testing') {
    define('LOG_FILENAME', implode(DIRECTORY_SEPARATOR, array(__DIR__, 'logs', 'addventure.log')));
}
else {
    define('LOG_FILENAME', implode(DIRECTORY_SEPARATOR, array(__DIR__, 'logs', 'addventure-test.log')));
}

/**
 * @staticvar \Monolog\Logger $logger
 * @return \Monolog\Logger
 * @codeCoverageIgnore
 */
function initLogger() {
    static $logger = null;
    if($logger !== null) {
        return $logger;
    }
    $logger = new \Monolog\Logger('');
    $logger->pushHandler(new Monolog\Handler\RotatingFileHandler(LOG_FILENAME, 7, (ENVIRONMENT !== 'production') ? \Monolog\Logger::DEBUG : \Monolog\Logger::WARNING));
    if(ENVIRONMENT !== 'testing') {
        Monolog\ErrorHandler::register($logger);
    }

    if(php_sapi_name() !== 'cli') {
        if(!extension_loaded('apc') && !extension_loaded('memcache') && !extension_loaded('xcache') && !extension_loaded('redis')) {
            $logger->warning('You are seeing this message because you haven\' enabled APC/memcache/xcache/redis in your server. Please do so to get better performance');
        }
    }
    return $logger;
}

if(ENVIRONMENT !== 'production') {
    initLogger()->warning('You are not running the Addventure in production mode');
    define('JSON_FLAGS', JSON_PRETTY_PRINT);
    if(ENVIRONMENT !== 'testing') {
        error_reporting(E_ALL);
    }
}
else {
    define('JSON_FLAGS', 0);
    error_reporting(0);
}

/**
 * @return \Doctrine\ORM\EntityManager
 * @codeCoverageIgnore
 */
function initDoctrineConnection() {
    static $entityManager = null;

    if($entityManager !== null) {
        return $entityManager;
    }

    if(ENVIRONMENT !== 'testing') {
        $doctrineDbConfig = array(
            'host' => 'localhost',
            'driver' => ADDVENTURE_DB_DRIVER,
            'user' => ADDVENTURE_DB_USER,
            'password' => ADDVENTURE_DB_PASSWORD,
            'dbname' => ADDVENTURE_DB_SCHEMA,
            'charset' => 'utf8'
        );
    }
    else {
        $doctrineDbConfig = array(
            'driver' => 'pdo_sqlite',
            'user' => 'addventure',
            'password' => 'addventure',
            'memory' => true
        );
    }

    $doctrineConfig = Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/dao/core"), ENVIRONMENT !== 'production', __DIR__ . "/dao/proxies");
    
    $entityManager = Doctrine\ORM\EntityManager::create($doctrineDbConfig, $doctrineConfig);

    if(ENVIRONMENT === 'testing') {
        $entityManager->clear();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $classes = $entityManager->getMetadataFactory()->getAllMetadata();
        //$tool->dropSchema($classes);
        $tool->createSchema($classes);
    }

    return $entityManager;
}

$entityManager = initDoctrineConnection();

/**
 * Collapse multiple whitespaces, trim the string and check the length
 * @param string $text Text to simplify
 * @param int $maxLength Maximum allowed string length
 * @param boolean $allowEmpty Whether to allow empty strings
 * @return string Simplified string
 * @throws \InvalidArgumentException if the length is exceeded or if $allowEmpty is true and the string is empty
 */
function simplifyWhitespace($text, $maxLength, $allowEmpty = true) {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if(mb_strlen($text) > $maxLength) {
        throw new \InvalidArgumentException("Text too long");
    }
    elseif(!$allowEmpty && empty($text)) {
        throw new \InvalidArgumentException("Text must not be empty");
    }
    return $text;
}
