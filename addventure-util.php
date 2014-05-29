<?php

if(!defined('ADDVENTURE_DEV_MODE')) {
    die('Forbidden.');
}

function findOrCreateAuthor($author, $createUser = true) {
    $author = trim($author);
    if(empty($author)) {
        return NULL;
    }
    global $entityManager;
    $tmp = $entityManager->getRepository('addventure\AuthorName')->findOneBy(array('name' => $author));
    if($tmp) {
        return $tmp;
    }
    $nAuthor = new addventure\AuthorName();
    $nAuthor->setName($author);
    if($createUser) {
        $nAuthor->setUser(new addventure\User());
    }
    return $nAuthor;
}
