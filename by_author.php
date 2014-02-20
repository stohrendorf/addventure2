<?php

if(!isset($argv[1])) {
    exit(0);
}

require 'doctrine-bootstrap.php';

/**
 * @var addventure\AuthorName
 */
$author = $entityManager->getRepository('addventure\AuthorName')->findOneBy(array('name' => $argv[1]));
if(!$author) {
    echo "Author not found\n";
    exit(0);
}
/**
 * @var addventure\User
 */
$user = $author->getUser();
assert($user != NULL);

$res = array(
    'meta' => array(
        'type' => 'by_author',
        'author' => $author->toJson()
    ),
    'episodes' => array(
    )
);

foreach($author->getEpisode() as $ep) {
    $res['episodes'][] = $ep->toJson();
}

echo json_encode($res, JSON_FLAGS);
