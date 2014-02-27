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

function createPagination($min, $max, $cur, $baseUrl = './?', $width = 10) {
    global $logger;
    $logger->debug("Pagination: min=$min, max=$max, cur=$cur, width=$width");
    if($width > $max - $min) {
        $width = $max - $min + 1;
    }
    $left = $cur - $width / 2;
    if($left < 0) {
        $left = 0;
    }
    $right = $left + $width - 1;
    if($right > $max) {
        $right = $max;
    }
    if($right < $min + $width) {
        $left = $min;
    }
    $logger->debug("Pagination: left=$left, right=$right");
    $res = '<ul class="pagination">';
    if($cur > 0) {
        $res .= '<li><a href="' . $baseUrl . 'page=' . ($cur - 1) . '">&laquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&laquo;</a></li>';
    }
    for($i = $left; $i <= $right; ++$i) {
        if($i == $cur) {
            $res .= '<li class="active"><a href="' . $baseUrl . 'page=' . $i . '">' . ($i + 1) . '</a></li>';
        }
        elseif($i <= $right) {
            $res .= '<li><a href="' . $baseUrl . 'page=' . $i . '">' . ($i + 1) . '</a></li>';
        }
        else {
            $res .= '<li class="disabled"><a href="#">' . ($i + 1) . '</a></li>';
        }
    }
    if($cur < $max) {
        $res .= '<li><a href="' . $baseUrl . 'page=' . ($cur + 1) . '">&raquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&raquo;</a></li>';
    }
    $res .= '</ul>';
    return $res;
}

function returnToReferrer()
{
    $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
    $uri = dirname(filter_input(INPUT_SERVER, 'REQUEST_URI'));
    $referer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
    $rpos = strpos($referer, $host);
    if(!$referer || $rpos<0 || $rpos>strlen('http://')) {
        header("Location: http://$host/$uri/");
    }
    else {
        header("Location: $referer");
    }
    die();
}
