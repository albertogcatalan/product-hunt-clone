<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

namespace Quaver\Controller;

if (!$_user->logged){
	header("Location: /");
	exit;
} else {

	if (isset($_POST['settingSubmit'])){

		if (isset($_POST['email'])) {
		    $_user->email = addslashes($_POST['email']);
		}

		if (isset($_POST['newsletter'])) {
		    $_user->newsletter = 1;
		}
		
		if ($_user->save()){

			$this->addTwigVars("changes_ok", true);

		} else {

			$this->addTwigVars("changes_ok", false);
		}

	}
	
}

$template = $this->twig->loadTemplate('profiles/settings.twig');
echo $template->render($this->twigVars);

?>
