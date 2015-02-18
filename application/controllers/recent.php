<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Recent extends CI_Controller
{

    public function index($page = 0)
    {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $this->load->library('em');
        $eps = $this->em->getEpisodeRepository()->getRecentEpisodes(-1, $page);
        $maxPage = floor(($eps->count() + getAddventureConfigValue('resultsPerPage') - 1) / getAddventureConfigValue('resultsPerPage'));
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('recent') . '/'));
        $smarty->assign('episodes', array());
        foreach($eps as $ep) {
            $smarty->append('episodes', $ep->toSmarty());
        }
        $smarty->display('recent.tpl');
    }

    public function user($userId, $page = 0)
    {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');

        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
        $user = $this->em->findUser($userId);
        if(!$user) {
            show_error(_('User not found'), 404);
            return;
        }
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if($page === null || $page === false) {
            $page = 0;
        }
        $smarty = createSmarty();
        $smarty->assign('episodes', array());
        $numEpisodes = $this->em->getEpisodeRepository()->findByUser(
                $userId,
                function(addventure\Episode $ep) use($smarty) {
                    $smarty->append('episodes', $ep->toSmarty());
                },
                $page
        );
        $smarty->assign('firstIndex', $page * getAddventureConfigValue('resultsPerPage'));
        $firstCreated = $this->em->getEpisodeRepository()->firstCreatedByUser($userId);
        if($firstCreated) {
            $smarty->assign('firstCreated', $firstCreated->format("l, d M Y H:i"));
        }
        $lastCreated = $this->em->getEpisodeRepository()->lastCreatedByUser($userId);
        if($lastCreated) {
            $smarty->assign('lastCreated', $lastCreated->format("l, d M Y H:i"));
        }
        $smarty->assign('episodeCount', $numEpisodes);
        $smarty->assign('user', $user->toSmarty());
        $smarty->assign('page', $page);
        $maxPage = floor(($numEpisodes + getAddventureConfigValue('resultsPerPage') - 1) / getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url(array('recent/user', $userId)) . '/'));
        $smarty->display('recent_user.tpl');
    }

}
