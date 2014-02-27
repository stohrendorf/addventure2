<?php

require 'doctrine-bootstrap.php';

header('Content-Type', 'application/rss+xml');

if(!isset($_GET['what'])) {
    die('');
}

function dieBadRequest() {
    http_response_code(400);
    die('');
}

function printRecent() {
    global $entityManager;
    $count = filter_input(INPUT_GET, 'count', FILTER_SANITIZE_NUMBER_INT);
    $user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT);
    try {
        if($user !== null && $user !== false) {
            $eps = $entityManager->getRepository('addventure\Episode')->getRecentEpisodesByUser($count, $user);
        }
        else {
            $eps = $entityManager->getRepository('addventure\Episode')->getRecentEpisodes($count);
        }
        if(!$eps) {
            dieBadRequest();
        }
    }
    catch(\InvalidArgumentException $ex) {
        dieBadRequest();
    }
    $res = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0"></rss>');
    $ch = $res->addChild('channel');
    $ch->addChild('title', 'Addventure2 feed');
    $ch->addChild('description', 'Recent episodes');
    $ch->addChild('language', 'en-US');
    $ch->addChild('copyright', 'The Addventure Authors');
    $ch->addChild('pubDate', (new \DateTime())->format(DateTime::RSS));

    foreach($eps as $ep) {
        $ep->toRss($ch);
    }

    echo $res->asXML();
}

switch(filter_input(INPUT_GET, 'what', FILTER_SANITIZE_STRING)) {
    case 'recent':
        printRecent();
        break;
    default:
        dieBadRequest();
}
