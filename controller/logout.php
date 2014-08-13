<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

$_user->unsetCookie();

if (!empty($_GET['ref'])) {
    $goTo = strip_tags(addslashes($_GET['ref']));
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $goTo = strip_tags(addslashes($_SERVER['HTTP_REFERER']));
} else {
    $goTo = '/';
}

header("Location: $goTo");
exit;

?>