<?php

if(!isset($argv[1])) {
    exit(0);
}

require 'doctrine-bootstrap.php';

$res = array(
    'meta' => array(
        'type' => 'chain',
        'chain' => array(
            'id' => $argv[1]
        )
    ),
    'episodes' => array(
    )
);

for($ep = $entityManager->getRepository('addventure\Episode')->findOneBy(array('oldId' => $argv[1])); $ep; $ep = $ep->getParent()) {
    $res['episodes'][] = $ep->toJson();
}

echo json_encode($res, JSON_FLAGS);
