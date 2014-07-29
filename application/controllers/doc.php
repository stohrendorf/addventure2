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
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error('Document not found');
            return;
        }
        if($episode->getText() === NULL) {
            $this->load->library('userinfo');
            if(!$this->userinfo->user || !$this->userinfo->user->canCreateEpisode()) {
                $smarty->display('account_benefits.tpl');
                return;
            }
            $smarty->display('doc_create.tpl');
            return;
        }
        $episode->setHitCount($episode->getHitCount() + 1);
        $this->em->persistAndFlush($episode);
        $smarty->assign('episode', $episode->toSmarty());
        $smarty->display('doc_episode.tpl');
    }

    public function like($docId) {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode || $episode->getText() === NULL) {
            show_error('Document not found');
            return;
        }
        $episode->setLikes($episode->getLikes() + 1);
        $this->em->persistAndFlush($episode);
        redirect(site_url(array('doc', $docId)));
    }

    public function dislike($docId) {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode || $episode->getText() === NULL) {
            show_error('Document not found');
            return;
        }
        $episode->setDislikes($episode->getDislikes() + 1);
        $this->em->persistAndFlush($episode);
        redirect(site_url(array('doc', $docId)));
    }

    private function _createChain(\addventure\Episode &$episode, $numEps) {
        $eps = array();
        while($episode && --$numEps >= 0) {
            $smarty = $episode->toSmarty();
            $parent = $episode->getParent();
            if($parent) {
                $this->load->library('em');
                $link = $this->em->findLink($parent->getId(), $episode->getId());
                if(!$link) {
                    $this->load->library('log');
                    $this->log->crit('No link from doc #' . $parent->getId() . ' to doc #' . $episode->getId());
                    $smarty['chosen'] = 'o.O MAGIC';
                }
                else {
                    $smarty['chosen'] = $link->getTitle();
                }
            }
            array_unshift($eps, $smarty);
            $episode = $parent;
        }
        return $eps;
    }

    public function chain($docId, $numEps = 20) {
        $this->load->helper('smarty');
        $smarty = createSmarty();
        $numEps = filter_var($numEps, FILTER_SANITIZE_NUMBER_INT);
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        $smarty->assign('targetEpisode', $episode->getId());
        $eps = $this->_createChain($episode, $numEps);
        $smarty->assign('episodes', $eps);
        $smarty->display('doc_chain.tpl');
    }

    public function random() {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $this->load->library('em');
        if(ADDVENTURE_DB_DRIVER === 'pdo_mysql') {
            $query = $this->em->getEntityManager()->createNativeQuery('SELECT r1.id AS id FROM Episode AS r1 JOIN (SELECT (RAND() * (SELECT MAX(e.id) FROM Episode e)) AS id) AS r2'
                    . ' WHERE r1.id >= r2.id AND r1.text IS NOT NULL'
                    . ' ORDER BY r1.id ASC LIMIT 1', $rsm);
        }
        else {
            $query = $this->em->getEntityManager()->createNativeQuery('SELECT id FROM Episode WHERE text IS NOT NULL AND id>=(SELECT RANDOM()*MAX(id) FROM Episode) LIMIT 1', $rsm);
        }
        $this->index($query->getSingleScalarResult());
    }

    public function subscribe($docId) {
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canSubscribe()) {
            show_error('Not allowed');
            return;
        }

        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_404('Document not found');
        }

        $subscription = new addventure\Notification();
        $subscription->setUser($this->userinfo->user);
        $subscription->setEpisode($episode);
        try {
            $this->em->persistAndFlush($subscription);
        }
        catch(PDOException $ex) {
            // already subscribed
        }

        redirect('doc/' . $docId);
    }

    public function create($docId) {
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');
        $smarty = createSmarty();
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error('Invalid doc id');
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error('Document not found');
            return;
        }
        if($episode->getText() !== NULL) {
            show_error('Document already created');
            return;
        }
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canCreateEpisode()) {
            $smarty->display('account_benefits.tpl');
            return;
        }

        $this->load->helper('xss_clean');

        $preNotes = $this->input->post('preNotes');
        $title = $this->input->post('title');
        $text = $this->input->post('content');
        $postNotes = $this->input->post('postNotes');
        $options = $this->input->post('options');
        if(!$text || empty($options) || !$title) {
            // TODO retry
            return;
        }

        if($preNotes) {
            $preNotes = xss_clean2($preNotes);
        }
        $title = xss_clean2($title);
        $text = xss_clean2($text);
        if($postNotes) {
            $postNotes = xss_clean2($postNotes);
        }
        array_walk($options, function(&$value) {
            $value = xss_clean2($value);
        });
        
        if(false) {
            // TODO activate this code when the above is ready
            $notifications = $this->em->getNotificationsForDoc($episode->getParent()->getId());
            foreach($notifications as $notification) {
                $message = Swift_Message::newInstance();
                $message->setFrom(ADDVENTURE_EMAIL_ADDRESS, ADDVENTURE_EMAIL_NAME);
                $message->setTo($notification->getUser()->getEmail());
                $message->setSubject('Option filled');
                $message->setBody('...'); // TODO
                $transport = Swift_SendmailTransport::newInstance();
                $mailer = Swift_Mailer::newInstance($transport);
                if(!$mailer->send($message, $failures)) {
                    $this->load->library('log');
                    $this->log->crit('Could not send notification e-mail: ' . print_r($failures, true));
                }
            }
        }
    }

}
