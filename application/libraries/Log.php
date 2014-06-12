<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class CI_Log {

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct() {
        global $logger;
        $this->logger = $logger;
    }

    /**
     * Compatibility layer.
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE) {
        switch(strtoupper($level)) {
            case 'ERROR':
                $this->logger->addError($msg);
                break;
            case 'DEBUG':
                $this->logger->addDebug($msg);
                break;
            default: // in CI, only error and debug are used.
                $this->logger->addInfo("[LEVEL $level, PHP_ERROR $php_error]: $msg");
                break;
        }
    }

    public function crit($msg) {
        $this->logger->addCritical($msg);
    }

    public function error($msg) {
        $this->logger->addError($msg);
    }

    public function warning($msg) {
        $this->logger->addWarning($msg);
    }

    public function debug($msg) {
        $this->logger->addDebug($msg);
    }

    public function info($msg) {
        $this->logger->addInfo($msg);
    }

}
