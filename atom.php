<?php

require 'doctrine-bootstrap.php';

header('Content-Type', 'application/atom+xml');

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
        if($user!==null && $user !== false) {
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
    $res = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>');
    $res->addChild('title', 'Addventure2 feed');
    $res->addChild('id', $_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]));
    $res->addChild('updated', (new \DateTime())->format(DateTime::ATOM));
    $res->addChild('author', 'The Addventure Authors');
    $l = $res->addChild('link');
    $l->addAttribute('href', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]));
    $l->addAttribute('rel', 'alternate');
    $res->addChild('subTitle', 'Recent episodes');

    foreach($eps as $ep) {
        $ep->toAtom($res);
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
