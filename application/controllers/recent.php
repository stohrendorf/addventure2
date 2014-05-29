<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Recent extends CI_Controller {

    public function index($page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        global $entityManager;
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $eps = $entityManager->getRepository('addventure\Episode')->getRecentEpisodes(-1, $page);
        $maxPage = floor(($eps->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('recent') . '/'));
        foreach($eps as $ep) {
            $smarty->append('episodes', $ep->toSmarty());
        }
        $smarty->display('recent.tpl');
    }

    public function user($userId, $page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        global $entityManager;

        $userId = filter_var($userId, FILTER_SANITIZE_NUMBER_INT);
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if($page === null || $page === false) {
            $page = 0;
        }
        $smarty = createSmarty();
        $numEpisodes = $entityManager->getRepository('addventure\Episode')->findByUser(
                $userId, function(addventure\Episode $ep) use($smarty) {
            $smarty->append('episodes', $ep->toSmarty());
        }, $page
        );
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $d = $entityManager->getRepository('addventure\Episode')->firstCreatedByUser($userId);
        if($d) {
            $smarty->assign('firstCreated', $d->format("l, d M Y H:i"));
        }
        $d = $entityManager->getRepository('addventure\Episode')->lastCreatedByUser($userId);
        if($d) {
            $smarty->assign('lastCreated', $d->format("l, d M Y H:i"));
        }
        $smarty->assign('episodeCount', $numEpisodes);
        $smarty->assign('userid', $userId);
        $smarty->assign('page', $page);
        $maxPage = floor(($numEpisodes + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url(array('recent/user', $userId)) . '/'));
        $smarty->display('recent_user.tpl');
    }

}