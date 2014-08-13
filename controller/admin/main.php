<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */


// Check if user is logged and is admin
if (!$_user->logged) {
    header("Location: /");
    exit;
}
if (!$_user->isAdmin()) {
	header("Location: /");
	exit;
} 

// Select URI
switch ($this->url_var[1]) {

	default:
		$template = $this->twig->loadTemplate('admin/aHome.twig');
		echo $template->render($this->twigVars);
        break;
}

?>