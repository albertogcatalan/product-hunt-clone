<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Controller;

if (!$_user->isAdmin()) {
    header("Location: /");
    exit;
} 

if (!$_user->logged) {
    header("Location: /");
    exit;
}

$this->addTwigVars('section', '');
$template = $this->twig->loadTemplate('admin/main.twig');
echo $template->render($this->twigVars);

?>
