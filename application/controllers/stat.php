<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Stat extends CI_Controller {

    public function mostread($page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $this->load->library('em');
        $eps = $this->em->getEpisodeRepository()->getMostReadEpisodes(-1, $page);
        $maxPage = floor(($eps->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('treehouse/mostread') . '/'));
        foreach($eps as $ep) {
            $smarty->append('episodes', $ep->toSmarty());
        }
        $smarty->display('stat_mostread.tpl');
    }

    public function mostliked($page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $this->load->library('em');
        $eps = $this->em->getEpisodeRepository()->getMostLikedEpisodes(-1, $page);
        $maxPage = floor(($eps->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('treehouse/mostliked') . '/'));
        foreach($eps as $ep) {
            $smarty->append('episodes', $ep->toSmarty());
        }
        $smarty->display('stat_mostliked.tpl');
    }

    public function mosthated($page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $this->load->library('em');
        $eps = $this->em->getEpisodeRepository()->getMostHatedEpisodes(-1, $page);
        $maxPage = floor(($eps->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('treehouse/mosthated') . '/'));
        foreach($eps as $ep) {
            $smarty->append('episodes', $ep->toSmarty());
        }
        $smarty->display('stat_mosthated.tpl');
    }

    public function mostepisodes($page = 0) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if(!$page) {
            $page = 0;
        }
        $this->load->library('em');
        $users = $this->em->getEpisodeRepository()->getMostEpisodesByUser(-1, $page);
        $maxPage = floor(($users->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('treehouse/mostepisodes') . '/'));
        foreach($users as $user) {
            $smarty->append('users', array('count'=>$user['episodeCount'], 'user'=>$user[0]->toSmarty()));
        }
        $smarty->display('stat_mostepisodes.tpl');
    }

}