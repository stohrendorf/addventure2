<?php

$doctrineDevMode = false;
$doctrineDbConfig = array(
    'driver'   => 'pdo_mysql',
    'user'     => 'A. U. Thor',
    'password' => 'hammer',
    'dbname'   => 'addventure'
);

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

if ($doctrineDevMode) {
  $doctrineCache = new \Doctrine\Common\Cache\ArrayCache();
  define('JSON_FLAGS', JSON_PRETTY_PRINT);
} else {
  $doctrineCache = new Doctrine\Common\Cache\ApcCache();
  define('JSON_FLAGS', 0);
}

$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/dao/core"), $doctrineDevMode, __DIR__ . "/dao/proxies", $doctrineCache);

$entityManager = EntityManager::create($doctrineDbConfig, $doctrineConfig);
