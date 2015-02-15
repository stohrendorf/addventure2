<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tags extends CI_Controller
{

    public function storyline($tagId, $page = 0)
    {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');

        $tagId = filter_var($tagId, FILTER_SANITIZE_NUMBER_INT);
        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if($page === null || $page === false) {
            $page = 0;
        }
        $smarty = createSmarty();
        $smarty->assign('episodes', array());
        $numEpisodes = $this->em->getEpisodeRepository()->findByStoryline(
            $tagId, function(addventure\Episode $ep) use($smarty) {
                $smarty->append('episodes', $ep->toSmarty());
            },
            $page
        );
        $smarty->assign('firstIndex', $page * getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('episodeCount', $numEpisodes);
        $tag = $this->em->getEntityManager()->find('addventure\StorylineTag', $tagId);
        $smarty->assign('storyline', $tag->toSmarty());
        $smarty->assign('page', $page);
        $maxPage = floor(($numEpisodes + getAddventureConfigValue('resultsPerPage') - 1) / getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url(array('tags/storyline', $tagId)) . '/'));
        $smarty->display('tags_storyline.tpl');
    }

    public function storylines($page = 0)
    {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');

        $page = filter_var($page, FILTER_SANITIZE_NUMBER_INT);
        if($page === null || $page === false) {
            $page = 0;
        }
        
        $queryBuilder = $this->em->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('t')
                ->from('addventure\StorylineTag', 't')
                ->orderBy('t.title');
        $queryBuilder->setFirstResult($page * getAddventureConfigValue('resultsPerPage'));
        $queryBuilder->setMaxResults(getAddventureConfigValue('resultsPerPage'));
        $query = $queryBuilder->getQuery();
        $query->setQueryCacheLifetime(60*60);
        $tags = new \Doctrine\ORM\Tools\Pagination\Paginator($query, false);
        
        $smarty = createSmarty();
        $smarty->assign('firstIndex', $page * getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('page', $page);
        $maxPage = floor(($tags->count() + getAddventureConfigValue('resultsPerPage') - 1) / getAddventureConfigValue('resultsPerPage'));
        $smarty->assign('pagination', createPagination($maxPage, $page, site_url('tags/storylines') . '/'));
        foreach($tags as $tag) {
            $smarty->append('tags', $tag->toSmarty());
        }
        $smarty->display('tags_storylines.tpl');
    }

}
