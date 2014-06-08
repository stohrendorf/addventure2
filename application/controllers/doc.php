<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Doc extends CI_Controller {

    public function index($docId) {
        /*
         * Case 1: The document exists and has text, so simply show it.
         * Case 2: It exists, but isn't written yet, so check permissions and show the edit form.
         * Case 3: The document doesn't exist, then show an error.
         */
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');
        $smarty = createSmarty();
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $this->em->findEpisode($docId);
        if($ep) {
            if($ep->getText() === NULL) {
                $this->load->library('userinfo');
                if(!$this->userinfo->user || !$this->userinfo->user->canCreateEpisode()) {
                    $smarty->display('account_benefits.tpl');
                    return;
                }
                $smarty->display('doc_create.tpl');
            }
            else {
                $ep->setHitCount($ep->getHitCount() + 1);
                $this->em->persistAndFlush($ep);
                $smarty->assign('episode', $ep->toSmarty());
                $smarty->display('doc_episode.tpl');
            }
        }
        else {
            show_error('Document not found');
            return;
        }
    }

    public function like($docId) {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $this->em->findEpisode($docId);
        if($ep) {
            if($ep->getText() === NULL) {
                show_error('Document not found');
            }
            else {
                $ep->setLikes($ep->getLikes() + 1);
                $this->em->persistAndFlush($ep);
                redirect(site_url(array('doc', $docId)));
            }
        }
        else {
            show_error('Document not found');
            return;
        }
    }

    public function dislike($docId) {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $this->em->findEpisode($docId);
        if($ep) {
            if($ep->getText() === NULL) {
                show_error('Document not found');
            }
            else {
                $ep->setDislikes($ep->getDislikes() + 1);
                $this->em->persistAndFlush($ep);
                redirect(site_url(array('doc', $docId)));
            }
        }
        else {
            show_error('Document not found');
            return;
        }
    }

    private function createChain(\addventure\Episode &$ep, $numEps) {
        $eps = array();
        while($ep && --$numEps >= 0) {
            $sm = $ep->toSmarty();
            $parent = $ep->getParent();
            if($parent) {
                $this->load->library('em');
                $link = $this->em->findLink($parent->getId(), $ep->getId());
                if(!$link) {
                    $this->load->library('log');
                    $this->log->crit('No link from doc #' . $parent->getId() . ' to doc #' . $ep->getId());
                    $sm['chosen'] = 'o.O MAGIC';
                }
                else {
                    $sm['chosen'] = $link->getTitle();
                }
            }
            array_unshift($eps, $sm);
            $ep = $parent;
        }
        return $eps;
    }

    public function chain($docId, $numEps = 20) {
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $numEps = filter_var($numEps, FILTER_SANITIZE_NUMBER_INT);
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        $this->load->library('em');
        $ep = $this->em->findEpisode($docId);
        $smarty->assign('targetEpisode', $ep->getId());
        $eps = $this->createChain($ep, $numEps);
        $smarty->assign('episodes', $eps);
        $smarty->display('doc_chain.tpl');
    }

    public function random() {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $this->load->library('em');
        if(ADDVENTURE_DB_DRIVER === 'pdo_mysql') {
            $q = $this->em->getEntityManager()->createNativeQuery('SELECT r1.id AS id FROM Episode AS r1 JOIN (SELECT (RAND() * (SELECT MAX(e.id) FROM Episode e)) AS id) AS r2'
                    . ' WHERE r1.id >= r2.id AND r1.text IS NOT NULL'
                    . ' ORDER BY r1.id ASC LIMIT 1', $rsm);
        }
        else {
            $q = $this->em->getEntityManager()->createNativeQuery('SELECT id FROM Episode WHERE text IS NOT NULL AND id>=(SELECT RANDOM()*MAX(id) FROM Episode) LIMIT 1', $rsm);
        }
        $this->index($q->getSingleScalarResult());
    }

}
