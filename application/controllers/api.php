<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once 'dao/core/User.php';

class Api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->output->set_content_type('application/json');
    }

    public function backlinks()
    {
        $result = array();
        
        $this->load->library('userinfo');
        if(!$this->userinfo->user || $this->userinfo->user->getRole()->get() < addventure\UserRole::Registered)
        {
            echo json_encode(array('entries' => $result));
            return;
        }

        $filter = $this->input->post('query');
        if($filter === false || empty($filter)) {
            echo json_encode(array('entries' => $result));
            return;
        }

        $this->load->library('em');
        if(preg_match('/^[0-9]+$/', $filter)) {
            $filter = filter_var($filter, FILTER_SANITIZE_NUMBER_INT);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('DISTINCT e')->from('addventure\Episode', 'e')
                    ->where('e.linkable = TRUE')
                    ->andWhere('CONCAT(IDENTITY(e.id), \'\') LIKE :filter')
                    ->orderBy('e.id') // and then ordered by target
                    ->setMaxResults(getAddventureConfigValue('resultsPerPage'));
            $qb->setParameter('filter', '%' . addcslashes($filter, '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }
        else {
            $filter = filter_var($filter, FILTER_SANITIZE_STRING);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('DISTINCT e, LENGTH(e.title) AS HIDDEN len')->from('addventure\Episode', 'e')
                    ->where('e.linkable = TRUE')
                    ->andWhere('UPPER(e.title) LIKE :filter')
                    ->orderBy('len') // the most-matching first
                    ->addOrderBy('e.title') // and then ordered by target
                    ->setMaxResults(getAddventureConfigValue('resultsPerPage'));
            $qb->setParameter('filter', '%' . addcslashes(mb_convert_case($filter, MB_CASE_UPPER), '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }

        echo json_encode(array('entries' => $result));
    }

    public function users()
    {
        $result = array();
        
        $this->load->library('em');
        $this->load->library('userinfo');
        if(!$this->userinfo->user || $this->userinfo->user->getRole()->get() < addventure\UserRole::Moderator)
        {
            echo json_encode(array('entries' => $result));
            return;
        }
        
        $filter = $this->input->post('query');
        if($filter === false || empty($filter)) {
            echo json_encode(array('entries' => $result));
            return;
        }

        if(preg_match('/^[0-9]+$/', $filter)) {
            $filter = filter_var($filter, FILTER_SANITIZE_NUMBER_INT);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('DISTINCT u')->from('addventure\User', 'u')
                    ->where('CONCAT(IDENTITY(u.id), \'\') LIKE :filter')
                    ->orderBy('u.id') // and then ordered by target
                    ->setMaxResults(getAddventureConfigValue('resultsPerPage'));
            $qb->setParameter('filter', '%' . addcslashes($filter, '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }
        else {
            $filter = filter_var($filter, FILTER_SANITIZE_STRING);
            $qb = $this->em->getEntityManager()->createQueryBuilder();
            $qb->select('DISTINCT u, LENGTH(u.username) AS HIDDEN len')->from('addventure\User', 'u')
                    ->where('UPPER(u.username) LIKE :filter')
                    ->orderBy('len') // the most-matching first
                    ->addOrderBy('u.username') // and then ordered by target
                    ->setMaxResults(getAddventureConfigValue('resultsPerPage'));
            $qb->setParameter('filter', '%' . addcslashes(mb_convert_case($filter, MB_CASE_UPPER), '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
            foreach($qb->getQuery()->getResult() as $link) {
                $result[] = $link->toJson();
            }
        }

        echo json_encode(array('entries' => $result));
    }

    public function addcomment($docId)
    {
        $this->output->set_content_type('text/plain');
        
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canCreateComment()) {
            return;
        }

        $this->load->helper('xss_clean');
        $commentText = $this->input->post('comment');
        if($commentText === false) {
            return;
        }
        $commentText = trim(xss_clean2(strip_tags($commentText)));
        if(empty($commentText)) {
            return;
        }

        $authorName = $this->input->post('author');
        if($authorName === false) {
            return;
        }
        $authorName = trim(strip_tags($authorName));
        if(empty($authorName)) {
            return;
        }

        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            return;
        }

        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            return;
        }
        if($episode->getText() === NULL) {
            return;
        }

        $author = $this->em->findOrCreateAuthorForUser($this->userinfo->user, $authorName, true);
        if(!$author) {
            return;
        }

        $cmt = new addventure\Comment();
        $cmt->setAuthorName($author);
        $cmt->setEpisode($episode);
        $cmt->setText($commentText);
        $cmt->setCreated(new \DateTime());
        $episode->getComments()->add($cmt);
        $this->em->getEntityManager()->persist($cmt);
        $this->em->persistAndFlush($episode);
    }

    public function storylines()
    {
        $result = array();
        $this->load->library('em');


        $this->load->library('userinfo');
        if(!$this->userinfo->user || $this->userinfo->user->getRole()->get() < addventure\UserRole::Moderator)
        {
            echo json_encode(array('entries' => $result));
            return;
        }

        $filter = $this->input->post('query');
        if($filter === false || empty($filter)) {
            echo json_encode(array('entries' => $result));
            return;
        }

        $filter = filter_var($filter, FILTER_SANITIZE_STRING);
        $qb = $this->em->getEntityManager()->createQueryBuilder();
        $qb->select('DISTINCT t, LENGTH(t.title) AS HIDDEN len')->from('addventure\StorylineTag', 't')
                ->where('UPPER(t.title) LIKE :filter')
                ->orderBy('len') // the most-matching first
                ->addOrderBy('t.id')
                ->setMaxResults(getAddventureConfigValue('resultsPerPage'));
        $qb->setParameter('filter', '%' . addcslashes(mb_convert_case($filter, MB_CASE_UPPER), '%_') . '%', Doctrine\DBAL\Types\Type::STRING);
        foreach($qb->getQuery()->getResult() as $tag) {
            $result[] = $tag->toJson();
        }

        echo json_encode(array('entries' => $result));
    }

}
