<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Controller;
use Quaver\Model\Lang;

$language = new Lang;
$langCurrent = $language->getFromSlug($this->url_var[1]);

if ($langCurrent) {
    $language->setCookie();
    if (!empty($_SERVER['HTTP_REFERER'])){
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
    	$newLang = new url;
        header("Location: " . $newLang->getFromId(1)->url);
        exit;
    }
} else {
    header("Location: " . $this->getUrlFromId(2) . "?ref=" . $this->getUrl());
    exit;
}

?>