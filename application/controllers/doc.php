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

        $this->load->library('userinfo');
        $queryBuilder = $this->em->getEntityManager()->createQueryBuilder();
        if($this->userinfo->user) {
            $query = $queryBuilder->select('n')->from('addventure\Notification', 'n')
                            ->where('n.user = :uid')->andWhere('n.episode = :eid')
                            ->setParameter('uid', $this->userinfo->user->getId())
                            ->setParameter('eid', $docId)
                            ->getQuery()->getOneOrNullResult();
            $smarty->assign('isSubscribed', $query !== null);
        }
        else {
            $smarty->assign('isSubscribed', false);
        }

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
                    $smarty['chosen'] = _('[Orphaned link]');
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
        $this->load->library('em');
        $query = $this->em->getEntityManager()->createQuery('SELECT MIN(e.id) AS minId, MAX(e.id) AS maxId FROM addventure\Episode e WHERE e.text IS NOT NULL');
        $limits = $query->getOneOrNullResult();

        if(!$limits) {
            redirect('/');
            return;
        }

        $ep = null;
        while(!$ep) {
            $rid = rand($limits['minId'], $limits['maxId']);
            $ep = $this->em->findEpisode($rid);
            if(!$ep->getText()) {
                $ep = null;
            }
        }

        $this->index($ep->getId());
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
        catch(\Doctrine\DBAL\Exception\ConstraintViolationException $ex) {
            // already subscribed
        }

        $this->load->helper('url');
        redirect('doc/' . $docId);
    }

    private function _parseOptions($options, $targets, $isCreation)
    {
        if($options === false || $targets === false || count($options) != count($targets)) {
            $options = array();
            $targets = array();
        }

        $combinedOpts = array();
        for($i = 0; $i < count($options); ++$i) {
            $target = $targets[$i];
            if(!empty($target)) {
                $target = filter_var($targets[$i], FILTER_SANITIZE_NUMBER_INT);
                if(!$target) {
                    $target = '';
                }
            }
            $combinedOpts[] = array(
                'title' => $options[$i],
                'target' => $target
            );
        }

        // remove empty options and wrong targets
        $em = $this->em;
        array_walk($combinedOpts, function(&$entry) use($em, $isCreation) {
            $entry['title'] = trim(xss_clean2($entry['title']));
            if($isCreation && !empty($entry['target'])) {
                $ep = $em->findEpisode($entry['target']);
                if(!$ep || !$ep->getLinkable()) {
                    $entry['target'] = '';
                }
            }
        });

        $combinedOpts = array_filter($combinedOpts, function(&$entry) {
            return !empty($entry['title']);
        });

        return $combinedOpts;
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

        $this->load->helper('url');
        redirect('doc/' . $docId);
    }

    private function _editEpisode($docId, $isCreation)
    {
        $this->load->helper('url');
        $this->load->helper('smarty');
        $this->load->library('em');

        $smarty = createSmarty();
        $smarty->assign('isCreation', $isCreation);

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

        $this->load->library('userinfo');

        if($isCreation) {
            if($episode->getText() !== NULL) {
                show_error(_('Document already created'));
                return;
            }
            if(!$this->userinfo->user || !$this->userinfo->user->canCreateEpisode()) {
                $smarty->display('account_benefits.tpl');
                return;
            }
        }
        else {
            if(!$this->userinfo->user || !$this->userinfo->user->canEdit()) {
                $smarty->display('account_benefits.tpl');
                return;
            }
        }

        // Get the necessary information
        $this->load->helper('xss_clean');
        $preNotes = $this->input->post('preNotes');
        if($preNotes === false) {
            $preNotes = $isCreation ? '' : $episode->getPreNotes();
        }
        $episode->setPreNotes(xss_clean2($preNotes));

        $title = $this->input->post('title');
        if($title === false) {
            $title = $isCreation ? '' : $episode->getTitle();
        }
        $episode->setTitle(xss_clean2(strip_tags($title)));

        $content = $this->input->post('content');
        if($content === false) {
            $content = $isCreation ? '' : $episode->getText();
        }
        $episode->setText(xss_clean2($content));

        $postNotes = $this->input->post('postNotes');
        if($postNotes === false) {
            $postNotes = $isCreation ? '' : $episode->getPostNotes();
        }
        $episode->setPostNotes(xss_clean2($postNotes));

        $signedoff = $this->input->post('signedoff');
        if($signedoff === false || empty($signedoff)) {
            if($isCreation) {
                $signedoff = $this->userinfo->user->getUsername();
            }
            else {
                $signedoff = '';
            }
        }
        $signedoff = trim(xss_clean2($signedoff));

        $episode->setLinkable($isCreation ? ($this->input->post('linkable') === 'true') : $episode->getLinkable() );

        $options = $this->input->post('options');
        $targets = $this->input->post('targets');
        $combinedOpts = $this->_parseOptions($options, $targets, $isCreation);
        if(empty($combinedOpts)) {
            $combinedOpts = array();
            $query = $this->em->getEntityManager()->createQuery('SELECT l FROM addventure\Link l WHERE l.fromEp=?1 ORDER BY l.toEp')
                    ->setParameter(1, $episode->getId());
            foreach($query->getResult() as $link) {
                $combinedOpts[] = array('title' => $link->getTitle(), 'target' => $link->getToEp()->getId());
            }
        }

        $errors = array();
        if(empty($episode->getText())) {
            $errors[] = _('You haven\'t entered a story yet.');
        }
        if(empty($episode->getTitle())) {
            $errors[] = _('Your episode doesn\'t have a title.');
        }
        if($isCreation) {
            if(empty($signedoff)) {
                $errors[] = _('You haven\'t signed your story.');
            }
            else {
                $author = $this->em->findOrCreateAuthorForUser($this->userinfo->user, $signedoff, false);
                if(!$author) {
                    $errors[] = _('The name you chose is already used by somebody else.');
                }
            }
        }
        if(count($combinedOpts) < ADDVENTURE_MIN_LINKS) {
            $errors[] = sprintf(_('You have to provide at least %1$d links.'), ADDVENTURE_MIN_LINKS);
        }
        elseif(count($combinedOpts) > ADDVENTURE_MAX_LINKS) {
            $errors[] = sprintf(_('You may not provide more than %1$d links.'), ADDVENTURE_MAX_LINKS);
        }

        if(!empty($errors) || (!$isCreation && false === $this->input->post('content'))) {
            if($this->input->post('title') !== false) {
                $smarty->assign('errors', $errors);
            }
            $smarty->assign('options', $combinedOpts);
            $smarty->assign('signedoff', $signedoff);

            $smarty->assign('episode', $episode->toSmarty());
            if($episode->getParent()) {
                $smarty->assign('parent', $episode->getParent()->toSmarty());
            }

            $smarty->display('doc_create.tpl');
            return;
        }

        $this->em->getEntityManager()->beginTransaction();

        if($isCreation) {
            $author = $this->em->findOrCreateAuthorForUser($this->userinfo->user, $signedoff, true);
            $episode->setAuthor($author);
        }
        if(!$episode->getCreated()) {
            $episode->setCreated(new \DateTime());
        }
        if($isCreation) {
            foreach($combinedOpts as $opt) {
                $link = new addventure\Link();
                $link->setTitle($opt['title']);
                $link->setFromEp($episode);
                if(!empty($opt['target'])) {
                    $targetEp = $this->em->findEpisode($opt['target']);
                    assert($targetEp != null);
                    assert($targetEp->getLinkable());
                    $link->setToEp($targetEp);
                    $link->setIsBacklink(true);
                }
                else {
                    $newChild = new addventure\Episode();
                    $link->setToEp($newChild);
                    $newChild->setParent($episode);
                    $this->em->getEntityManager()->persist($newChild);
                    $this->em->getEntityManager()->flush($newChild);
                }
                $this->em->getEntityManager()->persist($link);
            }
            $author->getEpisodes()->add($episode);
            $this->em->getEntityManager()->persist($author);
        }
        else {
            foreach($combinedOpts as $opt) {
                $query = $this->em->getEntityManager()->createQuery('SELECT l FROM addventure\Link l WHERE l.fromEp=?1 AND l.toEp=?2')
                        ->setParameter(1, $episode->getId())
                        ->setParameter(2, $opt['target']);
                $link = $query->getOneOrNullResult();
                if(!$link) {
                    show_error(_('Internal fault'), 503);
                    return;
                }
                $link->setTitle($opt['title']);
                $this->em->persistAndFlush($link);
            }
        }
        $this->em->getEntityManager()->persist($episode);
        $this->em->getEntityManager()->flush($episode);

        $this->em->getEntityManager()->commit();
        $this->em->getEntityManager()->flush();

        if($isCreation) {
            // send notifications to subscribers
            if($episode->getParent()) {
                $notifications = $this->em->getNotificationsForDoc($episode->getParent()->getId());
                foreach($notifications as $notification) {
                    $this->_sendNotification($episode->getParent(), $notification->getUser());
                }
            }
        }

        $this->load->helper('url');
        redirect('doc/' . $episode->getId());
    }

    public function create($docId)
    {
        $this->_editEpisode($docId, true);
    }

    public function edit($docId)
    {
        $this->_editEpisode($docId, false);
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
