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
        catch(Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->log->debug('Maintenance/' . $description . ': Duplicate ' . $docId);
        }
        $this->load->helper('url');
        redirect(array('doc', $docId));
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

    public function deletecomment($commentid)
    {
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

    private function _getNonSelfUser($uid)
    {
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

    public function userinfo($uid)
    {
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

        $queryBuilder = $this->em->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder->select('n')->from('addventure\Notification', 'n')
                        ->where('n.user = :uid')->setParameter('uid', $uid)->getQuery();
        $notifications = array();
        foreach($query->getResult() as $n) {
            $notifications[] = $n->toSmarty();
        }
        $smarty->assign('notifications', $notifications);

        $smarty->display('maintenance_userinfo.tpl');
    }

    public function setrole($uid, $role)
    {
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
            $role = new addventure\UserRole((int) $role);
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
        catch(\InvalidArgumentException $ex) {
            show_error($ex->getMessage());
            return;
        }
        $this->em->persistAndFlush($user);

        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }

    public function resetlogins($uid)
    {
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

    public function block($uid)
    {
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

    public function unblock($uid)
    {
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

    public function deletesubscription($uid, $docid)
    {
        if(!$this->_checkAdmin()) {
            return;
        }
        $user = $this->_getNonSelfUser($uid);
        if(!$user) {
            return;
        }

        $n = $this->em->getEntityManager()->find('addventure\Notification', array('episode' => $docId, 'user' => $uid));
        if($n) {
            $this->em->getEntityManager()->remove($n);
            $this->em->getEntityManager()->flush();
        }
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $uid));
    }

    public function userlist($page = 0)
    {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }


        $this->load->library('em');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page || $page < 0) {
            $page = 0;
        }

        $queryBuilder = $this->em->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('u')
                ->from('addventure\User', 'u')
                ->orderBy('u.username');
        $queryBuilder->setFirstResult($page * ADDVENTURE_RESULTS_PER_PAGE);
        $queryBuilder->setMaxResults(ADDVENTURE_RESULTS_PER_PAGE);
        $query = $queryBuilder->getQuery();
        $users = new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);

        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $maxPage = floor(($users->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('maintenance/userlist') . '/'));
        foreach($users as $user) {
            $smarty->append('users', $user->toSmarty());
        }
        $smarty->display('maintenance_userlist.tpl');
    }

    public function reports($page = 0)
    {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }

        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page || $page < 0) {
            $page = 0;
        }

        $this->load->library('em');
        $queryBuilder = $this->em->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('r')
                ->from('addventure\Report', 'r')
                ->orderBy('r.episode');
        $queryBuilder->setFirstResult($page * ADDVENTURE_RESULTS_PER_PAGE);
        $queryBuilder->setMaxResults(ADDVENTURE_RESULTS_PER_PAGE);
        $query = $queryBuilder->getQuery();
        $reports = new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);

        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('currentPage', $page);
        $maxPage = floor(($reports->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('maintenance/reports') . '/'));
        $smarty->assign('reports', array());
        foreach($reports as $report) {
            $smarty->append('reports', $report->toSmarty());
        }
        $smarty->display('maintenance_reports.tpl');
    }

    public function deletereport($docId, $type, $returnPage)
    {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }

        $this->load->library('em');
        $report = $this->em->getEntityManager()->find('addventure\Report', array('episode' => $docId, 'type' => $type));
        if($report) {
            $this->em->getEntityManager()->remove($report);
            $this->em->getEntityManager()->flush();
        }
        $this->load->helper('url');
        redirect("maintenance/reports/$returnPage");
    }

    public function mergeuser($destination, $source) {
        if(!$this->_checkAdmin()) {
            return;
        }
        
        $this->load->library('em');
        $sourceUser = $this->em->findUser($source);
        $destinationUser = $this->em->findUser($destination);
        if($sourceUser && $destinationUser) {
            $this->em->mergeUser($destinationUser, $sourceUser);
        }
        $this->load->helper('url');
        redirect(array('maintenance', 'userinfo', $destination));
    }

    public function setstoryline($docId, $tagId, $recursive) {
        if(!$this->_checkAdminOrModerator()) {
            return;
        }
        
        if($recursive === 'false') {
            $recursive = false;
        }
        elseif($recursive === 'true') {
            $recursive = true;
        }
        else {
            show_404();
        }

        $this->load->library('em');
        $tag = $this->em->getEntityManager()->find('addventure\StorylineTag', $tagId);
        $doc = $this->em->findEpisode($docId);
        if(!$tag || !$doc) {
            show_404();
            return;
        }
        
        if(!$recursive) {
            $doc->setStorylineTag($tag);
            $this->em->persistAndFlush($doc);
        }
        else {
            $childQueue = array();
            $childQueue[] = $doc;
            $initialTag = $doc->getStorylineTag();
            $updateCount = 0;
            while(!empty($childQueue)) {
                $doc = array_shift($childQueue);
                if($doc->getStorylineTag()==null xor $initialTag==null) {
                    // only one of both is null
                    $this->em->getEntityManager()->detach($doc);
                    continue;
                }
                elseif($initialTag!=null && $doc->getStorylineTag()->getId() != $initialTag->getId()) {
                    // both are not null and have different tags
                    $this->em->getEntityManager()->detach($doc);
                    continue;
                }
                $doc->setStorylineTag($tag);
                $this->em->persistAndFlush($doc);
                ++$updateCount;
                foreach($doc->getChildLinks() as $link) {
                    array_push($childQueue, $link->getToEp());
                    $this->em->getEntityManager()->detach($link);
                }
                $this->em->getEntityManager()->detach($doc);
            }
            $this->load->library('log');
            $this->log->warning("Storyline update affected $updateCount episodes");
        }
        
        $this->load->helper('url');
        redirect(array('doc', $docId));
    }
}
