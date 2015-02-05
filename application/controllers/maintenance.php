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
            return;
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

    public function deletecomment($commentid) {
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canEdit()) {
            show_error(_('Forbidden'), 403);
            return;
        }
        $this->load->library('log');
        $commentid = filter_var($commentid, FILTER_SANITIZE_NUMBER_INT);
        if($commentid === null || $commentid === false) {
            $this->log->warning('Maintenance/deletecomment - invalid ID');
            show_404();
            return;
        }
        
        $this->load->library('em');
        $comment = $this->em->getEntityManager()->find('addventure\Comment', $commentid);
        if(!$comment) {
            $this->log->warning('Maintenance/deletecomment - comment not found');
            show_404();
            return;
        }
        $docid = $comment->getEpisode()->getId();
        $this->em->getEntityManager()->remove($comment);
        $this->em->getEntityManager()->flush();
        $this->load->helper('url');
        redirect("doc/$docid");
    }
    
    private function _checkAdminOrModerator()
    {
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !($this->userinfo->user->isAdministrator() || $this->userinfo->user->isModerator())) {
            show_error(_('Forbidden'), 403);
            return false;
        }
        return true;
    }
    
    private function _checkAdmin()
    {
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->isAdministrator()) {
            show_error(_('Forbidden'), 403);
            return false;
        }
        return true;
    }
    
    private function _getNonSelfUser($uid) {
        $this->load->library('log');
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);
        if($uid === null || $uid === false) {
            $this->log->warning('Maintenance - invalid User ID');
            show_404();
            return null;
        }
        
        $this->load->library('userinfo');
        if($uid == $this->userinfo->user->getId()) {
            $this->log->warning('Maintenance - cannot change own account');
            show_404();
            return null;
        }
        
        $this->load->library('em');
        $user = $this->em->findUser($uid);
        if(!$user) {
            $this->log->warning('Maintenance - invalid User ID');
            show_404();
            return null;
        }
        return $user;
    }
    
    public function userinfo($uid) {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }
        
        $this->load->library('log');
        $uid = filter_var($uid, FILTER_SANITIZE_NUMBER_INT);
        if($uid === null || $uid === false) {
            $this->log->warning('Maintenance/userinfo - invalid User ID');
            show_404();
            return;
        }
        
        $this->load->library('em');
        $user = $this->em->findUser($uid);
        
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $smarty->assign('user', $user->toSmarty());

        $smarty->display('maintenance_userinfo.tpl');
    }
    
    public function setrole($uid, $role) {
        if(!$this->_checkAdmin()) {
            return;
        }
        
        $this->load->library('log');
        $user = $this->_getNonSelfUser($uid);
        if(!$user) {
            return;
        }
        
        $role = filter_var($role, FILTER_SANITIZE_NUMBER_INT);
        if($role === null || $role === false) {
            $this->log->warning('Maintenance/setrole - invalid User role');
            show_404();
            return;
        }
        try {
            $role = new addventure\UserRole((int)$role);
        }
        catch(\InvalidArgumentException $ex) {
            $this->log->warning('Maintenance/setrole - invalid User role');
            show_404();
            return;
        }
        
        $user->setRole($role);
        try {
            $user->checkInvariants();
        }
        catch (\InvalidArgumentException $ex) {
            show_error($ex->getMessage());
            return;
        }
        $this->em->persistAndFlush($user);
        
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }
    
    public function resetlogins($uid) {
        if(!$this->_checkAdmin()) {
            return;
        }
        
        $this->load->library('log');
        
        $user = $this->_getNonSelfUser($uid);
        if(!$user) {
            return;
        }
        
        $user->setFailedLogins(0);
        $this->em->persistAndFlush($user);
        
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }
    
    public function block($uid) {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }
        
        $this->load->library('log');
        
        $user = $this->_getNonSelfUser($uid);
        if(!$user) {
            return;
        }
        $user->setBlocked(true);
        $this->em->persistAndFlush($user);
        
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }
    
    public function unblock($uid) {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }
        
        $this->load->library('log');
        
        $user = $this->_getNonSelfUser($uid);
        if(!$user) {
            return;
        }
        $user->setBlocked(false);
        
        $this->em->persistAndFlush($user);
        
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }
}
