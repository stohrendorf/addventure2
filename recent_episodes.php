<?php

require 'doctrine-bootstrap.php';

$dql = 'SELECT e FROM addventure\Episode e ORDER BY e.created DESC';
$eps = $entityManager->createQuery($dql)->setMaxResults(20)->getResult();

$res = array(
    'meta' => array(
        'type' => 'recent'
    ),
    'episodes' => array(
    )
);

foreach($eps as $ep) {
    $res['episodes'][] = $ep->toJson();
}

echo json_encode($res, JSON_FLAGS);
