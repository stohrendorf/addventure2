<?php

if(!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

function createPagination($numPages, $cur, $baseUrl, $margin = 5) {
    $left = (($cur - $margin) >= 0) ? $cur - $margin : 0;
    $right = (($cur + $margin) < $numPages) ? $cur + $margin : $numPages;

    $res = '<ul class="pagination">';
    $res .= '<li><a href="'.$baseUrl.'0"><span class="glyphicon glyphicon-step-backward"></span></a></li>';
    if($cur > 0) {
        $res .= '<li><a href="' . $baseUrl . ($cur - 1) . '">&laquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&laquo;</a></li>';
    }
    for($i = $left; $i < $right; ++$i) {
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
    if($cur < $numPages-1) {
        $res .= '<li><a href="' . $baseUrl . ($cur + 1) . '">&raquo;</a></li>';
    }
    else {
        $res .= '<li class="disabled"><a href="#">&raquo;</a></li>';
    }
    $res .= '<li><a href="'.$baseUrl.($numPages-1).'"><span class="glyphicon glyphicon-step-forward"></span></a></li>';
    $res .= '</ul>';
    return $res;
}
