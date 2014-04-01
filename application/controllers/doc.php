<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Doc extends CI_Controller {

    public function index($docId) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        global $entityManager;
        $smarty = createSmarty();
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $entityManager->find('addventure\Episode', $docId);
        if($ep) {
            if($ep->getText() === NULL) {
                $smarty->display('doc_create.tpl');
            }
            else {
                $ep->setHitCount($ep->getHitCount() + 1);
                $entityManager->persist($ep);
                $entityManager->flush();
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
        global $entityManager;
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $entityManager->find('addventure\Episode', $docId);
        if($ep) {
            if($ep->getText() === NULL) {
                show_error('Document not found');
            }
            else {
                $ep->setLikes($ep->getLikes() + 1);
                $entityManager->persist($ep);
                $entityManager->flush();
                redirect(site_url(array('doc',$docId)));
            }
        }
        else {
            show_error('Document not found');
            return;
        }
    }

    public function dislike($docId) {
        $this->load->helper('url');
        global $entityManager;
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $ep = $entityManager->find('addventure\Episode', $docId);
        if($ep) {
            if($ep->getText() === NULL) {
                show_error('Document not found');
            }
            else {
                $ep->setDislikes($ep->getDislikes() + 1);
                $entityManager->persist($ep);
                $entityManager->flush();
                redirect(site_url(array('doc',$docId)));
            }
        }
        else {
            show_error('Document not found');
            return;
        }
    }

    function chain($docId, $chain) {
        $this->load->helper('pagination');
        $this->load->helper('url');
        $this->load->helper('smarty');
        global $entityManager;
        $smarty = createSmarty();
        $chain = filter_var($chain, FILTER_SANITIZE_NUMBER_INT);
        $eps = array();
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        $ep = $entityManager->find('addventure\Episode', $docId);
        $smarty->assign('targetEpisode', $ep->getId());
        while($ep && --$chain >= 0) {
            $sm = $ep->toSmarty();
            $parent = $ep->getParent();
            if($parent) {
                $link = $entityManager->find('addventure\Link', array('fromEp' => $parent->getId(), 'toEp' => $ep->getId()));
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
        $smarty->assign('episodes', $eps);
        $smarty->display('doc_chain.tpl');
    }

}
