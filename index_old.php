<?php

require 'doctrine-bootstrap.php';

$s = new Smarty();
$s->setTemplateDir(__DIR__ . '/templates');
$s->setCacheDir(__DIR__ . '/templates/cache');
$s->setCompileDir(__DIR__ . '/templates/compiled');
$s->setConfigDir(__DIR__ . '/templates/config');

function usecaseFixAuthors() {
    global $s, $entityManager, $logger;
    $logger->debug('Usecase: maintenance.fixauthors');
    $docId = NULL;
    if(isset($_GET['docid'])) {
        $docId = filter_input(INPUT_GET, 'docid', FILTER_SANITIZE_NUMBER_INT);
        $ep = $entityManager->getRepository('addventure\Episode')->find($docId);
        if(!$ep) {
            echo "Invalid DocID!";
        }
        elseif(!$ep->getAuthor()) {
            echo "Doc has no author!";
        }
        elseif(!$ep->getNotes()) {
            echo "Doc has no notes!";
        }
        else {
            $author = findOrCreateAuthor($ep->getAuthor()->getName() . $ep->getNotes());
            if($author != null) {
                $oldAuthor = $ep->getAuthor();
                $oldNotes = $ep->getNotes();
                $logger->debug('Fixing authors for ' . count($oldAuthor->getEpisodes()) . ' episode(s)');
                foreach($oldAuthor->getEpisodes() as $ep2) {
                    if($ep2->getNotes() !== $oldNotes) {
                        continue;
                    }
                    $ep2->setAuthor($author);
                    $ep2->setNotes(null);
                    $entityManager->persist($ep2);
                }
                $entityManager->flush();
            }
        }
    }
    $dql = 'SELECT e, LENGTH(e.notes) AS HIDDEN l FROM addventure\Episode e WHERE e.notes IS NOT NULL ORDER BY l';
    $q = $entityManager->createQuery($dql);
    $q->setMaxResults(300);
    try {
        foreach($q->getResult() as $ep) {
            $s->append('episodes', $ep->toSmarty());
        }
    }
    catch(\Exception $e) {
        $logger->crit($e);
    }
    $s->display('fixauthors.tpl');
}

require_once 'addventure-util.php';

if(isset($_GET['doc'])) {
    $ep = $entityManager->find('addventure\Episode', filter_input(INPUT_GET, 'doc', FILTER_SANITIZE_NUMBER_INT));
    if($ep) {
        if($ep->getText() === NULL) {
            $s->display('create.tpl');
        }
        else {
            $chain = filter_input(INPUT_GET, 'chain', FILTER_SANITIZE_NUMBER_INT);
            if($chain === false || $chain === null || $chain <= 0) {
                $s->assign('episode', $ep->toSmarty());
                $s->display('episode.tpl');
            }
            else {
                $eps = array();
                $s->assign('targetEpisode', $ep->getId());
                while($ep && --$chain >= 0) {
                    $sm = $ep->toSmarty();
                    $parent = $ep->getParent();
                    if($parent) {
                        $link = $entityManager->find('addventure\Link', array('fromEp' => $parent->getId(), 'toEp' => $ep->getId()));
                        if(!$link) {
                            $logger->crit('No link from doc #' . $parent->getId() . ' to doc #' . $ep->getId());
                            $sm['chosen'] = 'o.O MAGIC';
                        }
                        else {
                            $sm['chosen'] = $link->getTitle();
                        }
                    }
                    array_unshift($eps, $sm);
                    $ep = $parent;
                }
                $s->assign('episodes', $eps);
                $s->display('chain.tpl');
            }
        }
    }
}
elseif(isset($_GET['maintenance'])) {
    switch(filter_input(INPUT_GET, 'maintenance', FILTER_SANITIZE_STRING)) {
        case 'fixauthors':
            usecaseFixAuthors();
            break;
        case 'reportTitle':
            $logger->debug('reportTitle: ' . $_GET['docid']);
            returnToReferrer();
            break;
        case 'reportNotes':
            $logger->debug('reportNotes: ' . $_GET['docid']);
            returnToReferrer();
            break;
        default:
            echo "EEEK";
            break;
    }
}
elseif(isset($_GET['user'])) {
    $userId = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT);
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
    if($page === null || $page === false) {
        $page = 0;
    }
    $numEpisodes = $entityManager->getRepository('addventure\Episode')->findByUser(
            $userId, function(addventure\Episode $ep) use($s) {
        $s->append('episodes', $ep->toSmarty());
    }, $page
    );
    $s->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
    $d = $entityManager->getRepository('addventure\Episode')->firstCreatedByUser($userId);
    if($d) {
        $s->assign('firstCreated', $d->format("l, d M Y H:i"));
    }
    $d = $entityManager->getRepository('addventure\Episode')->lastCreatedByUser($userId);
    if($d) {
        $s->assign('lastCreated', $d->format("l, d M Y H:i"));
    }
    $s->assign('episodeCount', $numEpisodes);
    $s->assign('userid', $userId);
    $s->assign('page', $page);
    $maxPage = floor(($numEpisodes + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
    $s->assign('pagination', createPagination(0, $maxPage - 1, $page, './?user=' . $userId . '&'));
    $s->display('by_user.tpl');
}
elseif(isset($_GET['recent'])) {
    $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
    $user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT);
    if($user !== null && $user !== false) {
        $eps = $entityManager->getRepository('addventure\Episode')->getRecentEpisodesByUser(-1, $user, $page);
    }
    else {
        $eps = $entityManager->getRepository('addventure\Episode')->getRecentEpisodes(-1, $page);
    }
    $maxPage = floor(($eps->count() + ADDVENTURE_RESULTS_PER_PAGE - 1) / ADDVENTURE_RESULTS_PER_PAGE);
    $s->assign('firstIndex', $page * ADDVENTURE_RESULTS_PER_PAGE);
    $s->assign('pagination', createPagination(0, $maxPage - 1, $page, './?recent&'));
    foreach($eps as $ep) {
        $s->append('episodes', $ep->toSmarty());
    }
    $s->display('recent.tpl');
}
elseif(isset($_GET['log'])) {
    echo '<pre>';
    echo file_get_contents('addventure.log');
    echo '</pre>';
}
elseif(isset($_GET['illegal'])) {
    $logger->debug('Illegal: ' . $_GET['illegal']);
    returnToReferrer();
}
else {
    $s->display('main.tpl');
}
