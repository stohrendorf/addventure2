<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function createPagination($min, $max, $cur, $baseUrl = './?', $width = 10) {
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
    $res = '<ul class="pagination">';
    if($cur > 0) {
        $res .= '<li><a href="' . $baseUrl . 'page=' . ($cur - 1) . '">&laquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&laquo;</a></li>';
    }
    for($i = $left; $i <= $right; ++$i) {
        if($i == $cur) {
            $res .= '<li class="active"><a href="' . $baseUrl . $i . '">' . ($i + 1) . '</a></li>';
        }
        elseif($i <= $right) {
            $res .= '<li><a href="' . $baseUrl . $i . '">' . ($i + 1) . '</a></li>';
        }
        else {
            $res .= '<li class="disabled"><a href="#">' . ($i + 1) . '</a></li>';
        }
    }
    if($cur < $max) {
        $res .= '<li><a href="' . $baseUrl . ($cur + 1) . '">&raquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&raquo;</a></li>';
    }
    $res .= '</ul>';
    return $res;
}
