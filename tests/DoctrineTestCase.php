<?php

namespace addventure;

/**
 * Base class for unit tests involving the database.
 */
class DoctrineTestCase extends \PHPUnit_Extensions_Database_TestCase {
    
    protected function getEm() {
        return initDoctrineConnection();
    }

    protected function getConnection() {
        $pdo = initDoctrineConnection()->getConnection()->getWrappedConnection();
        return $this->createDefaultDBConnection($pdo);
    }

    protected function getDataSet() {
        return null;
    }

}
