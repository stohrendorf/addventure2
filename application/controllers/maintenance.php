<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Maintenance extends CI_Controller
{

    private function _report($docId, $description, $type)
    {
        $this->load->library('log');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === null || $docId === false) {
            $this->log->warning('Maintenance/' . $description . ' - invalid DocID');
            show_404();
        }
        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            $this->log->warning('Maintenance/' . $description . ' - Document not found: ' . $docId);
            show_404();
            return;
        }
        $this->log->debug('Maintenance/' . $description . ': ' . $docId);
        $report = new addventure\Report();
        $report->setEpisode($episode);
        $report->setType($type);
        try {
            $this->em->persistAndFlush($report);
        }
        catch(PDOException $e) {
            $this->log->debug('Maintenance/' . $description . ': Duplicate ' . $docId);
        }
    }

    public function illegal($docId)
    {
        $this->_report($docId, 'Illegal', addventure\Report::ILLEGAL);
    }

    public function reportTitle($docId)
    {
        $this->_report($docId, 'TopNotes', addventure\Report::WRONG_TOP_NOTES);
    }

    public function reportNotes($docId)
    {
        $this->_report($docId, 'BottomNotes', addventure\Report::WRONG_BOTTOM_NOTES);
    }

    public function reportFormatting($docId)
    {
        $this->_report($docId, 'Formatting', addventure\Report::FORMATTING);
    }

    private static function _setMetadata(Smarty& $smarty, $varname, \Doctrine\Common\Cache\CacheProvider &$cache = null)
    {
        if($cache == null) {
            return;
        }
        $data = $cache->getStats();
        array_walk($data, function(&$val) {
            if($val === false) {
                $val = null;
            }
        });
        $smarty->assign($varname, $data);
        $smarty->assign($varname . 'Class', get_class($cache));
    }

    public function cacheinfo()
    {
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->isAdministrator()) {
            show_error(_('Forbidden'), 403);
            return;
        }
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $config = & $this->em->getEntityManager()->getConfiguration();

        static::_setMetadata($smarty, 'metadata', $config->getMetadataCacheImpl());
        static::_setMetadata($smarty, 'hydration', $config->getHydrationCacheImpl());
        static::_setMetadata($smarty, 'query', $config->getQueryCacheImpl());
        $smarty->display('maintenance_cacheinfo.tpl');
    }

}
