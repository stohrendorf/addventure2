<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class CI_Log {

    /**
     * @var Log
     */
    private $logger;

    public function __construct() {
        $this->logger = Log::singleton('file', implode(DIRECTORY_SEPARATOR, array(FCPATH, 'logs', 'addventure.log')), '');
        if(ADDVENTURE_DEV_MODE) {
            $this->logger->setMask(PEAR_LOG_ALL);
        }
        else {
            $this->logger->setMask(PEAR_LOG_WARNING);
        }
    }

    /**
     * Compatibility layer.
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE) {
        switch(strtoupper($level)) {
            case 'ERROR':
                $this->logger->err($msg);
                break;
            case 'DEBUG':
                $this->logger->debug($msg);
                break;
            default: // in CI, only error and debug are used.
                $this->logger->info("[LEVEL $level]: $msg");
                break;
        }
    }

    public function crit($msg) {
        $this->logger->crit($msg);
    }

    public function error($msg) {
        $this->logger->error($msg);
    }

    public function warning($msg) {
        $this->logger->warning($msg);
    }

    public function debug($msg) {
        $this->logger->debug($msg);
    }

    public function info($msg) {
        $this->logger->info($msg);
    }

}
