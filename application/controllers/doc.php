<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Doc extends CI_Controller
{

    public function index($docId)
    {
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
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error(_('Document not found'), 404);
            return;
        }
        if($episode->getText() === NULL) {
            redirect(site_url(array('doc', 'create', $docId)));
            return;
        }
        $episode->setHitCount($episode->getHitCount() + 1);
        $this->em->persistAndFlush($episode);
        $smarty->assign('episode', $episode->toSmarty());
        $smarty->display('doc_episode.tpl');
    }

    public function like($docId)
    {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode || $episode->getText() === NULL) {
            show_error(_('Document not found'), 404);
            return;
        }
        $episode->setLikes($episode->getLikes() + 1);
        $this->em->persistAndFlush($episode);
        redirect(site_url(array('doc', $docId)));
    }

    public function dislike($docId)
    {
        $this->load->helper('url');
        $this->load->library('em');
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode || $episode->getText() === NULL) {
            show_error(_('Document not found'), 404);
            return;
        }
        $episode->setDislikes($episode->getDislikes() + 1);
        $this->em->persistAndFlush($episode);
        redirect(site_url(array('doc', $docId)));
    }

    private function _createChain(\addventure\Episode &$episode, $numEps)
    {
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

    public function chain($docId, $numEps = 20)
    {
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

    public function random()
    {
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

    public function subscribe($docId)
    {
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canSubscribe()) {
            show_error(_('Not allowed'));
            return;
        }

        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error(_('Document not found'), 404);
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

    public function unsubscribe($docId)
    {
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canSubscribe()) {
            show_error(_('Not allowed'));
            return;
        }

        $this->load->library('em');
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error(_('Document not found'), 404);
        }

        $subscriptions = $this->em->getNotificationsForDoc($docId);
        foreach($subscriptions as $sub) {
            if($sub->getUser()->getId() == $this->userinfo->user->getId()) {
                $this->em->getEntityManager()->remove($sub);
                $this->em->getEntityManager()->flush();
                break;
            }
        }

        redirect('doc/' . $docId);
    }

    public function create($docId)
    {
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');

        $smarty = createSmarty();

        // Check if the document is ready to be created and that the user
        // is allowed to create it.
        $docId = filter_var($docId, FILTER_SANITIZE_NUMBER_INT);
        if($docId === false || $docId === null) {
            show_error(_('Invalid doc id'), 404);
            return;
        }
        $episode = $this->em->findEpisode($docId);
        if(!$episode) {
            show_error(_('Document not found'), 404);
            return;
        }
        if($episode->getText() !== NULL) {
            show_error(_('Document already created'));
            return;
        }
        $this->load->library('userinfo');
        if(!$this->userinfo->user || !$this->userinfo->user->canCreateEpisode()) {
            $smarty->display('account_benefits.tpl');
            return;
        }

        // Get the necessary information
        $this->load->helper('xss_clean');
        $preNotes = $this->input->post('preNotes');
        if($preNotes === false) {
            $preNotes = '';
        }
        $preNotes = xss_clean2($preNotes);

        $title = $this->input->post('title');
        if($title === false) {
            $title = '';
        }
        $title = xss_clean2($title);

        $content = $this->input->post('content');
        if($content === false) {
            $content = '';
        }
        $content = xss_clean2($content);

        $postNotes = $this->input->post('postNotes');
        if($postNotes === false) {
            $postNotes = '';
        }
        $postNotes = xss_clean2($postNotes);

        $options = $this->input->post('options');
        if($options === false) {
            $options = array();
        }
        // remove empty options
        array_walk($options, function(&$value) {
            $value = trim(xss_clean2($value));
        });
        array_filter($options, function(&$value) {
            return !empty($value);
        });
        if(empty($content) || empty($options) || empty($title)) {
            if($episode->getParent()) {
                $smarty->assign('parenttext', $episode->getParent()->getText());
                $smarty->assign('parentnotes', $episode->getParent()->getNotes());
            }
            $smarty->assign('content', $content);
            $smarty->assign('options', $options);
            $smarty->assign('title', $title);
            $smarty->assign('prenotes', $preNotes);
            $smarty->assign('postnotes', $postNotes);
            $smarty->assign('docid', $docId);
            $smarty->display('doc_create.tpl');
            return;
        }

        return; // TODO remove when UI is ready
        // send notifications to subscribers
        $notifications = $this->em->getNotificationsForDoc($episode->getParent()->getId());
        foreach($notifications as $notification) {
            $this->_sendNotification($episode->getParent(), $notification->getUser());
        }
    }

    private function _sendNotification(addventure\Episode $srcDoc, addventure\User $recipient)
    {
        $message = Swift_Message::newInstance();
        $message->setFrom(ADDVENTURE_EMAIL_ADDRESS, ADDVENTURE_EMAIL_NAME);
        $message->setTo($recipient->getEmail());
        $message->setSubject(_('Option filled'));
        $docurl = site_url(array('doc', $srcDoc->getId()));
        $unsubscribe = site_url(array('doc', 'unsubscribe', $srcDoc->getId()));
        $message->setBody(sprintf(_(<<<'MSG'
Dear %1$s,

an option of episode "%2$s" (%3$s) has been filled.

You are receiving this message because you have subscribed to updates for that
episode.  You can unsubscribe from further notifications by clicking on this
link: %4$s
MSG
                        ), $recipient->getUsername(), $srcDoc->getAutoTitle(), $docurl, $unsubscribe));
        $transport = Swift_SendmailTransport::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        if(!$mailer->send($message, $failures)) {
            $this->load->library('log');
            $this->log->crit('Could not send notification e-mail: ' . print_r($failures, true));
        }
    }

}
