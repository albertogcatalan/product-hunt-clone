<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

$url = $this->getUrl();

header("HTTP/1.0 404 Not Found");
trigger_error("[404] $url", E_USER_WARNING);

$template = $this->twig->loadTemplate("404.twig");
echo $template->render($this->twigVars);

?>