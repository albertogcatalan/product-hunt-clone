<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

$ac = NULL;
$ac = $this->url_var[1];

// Check if user is logged to get profile data and display
if (!empty($ac)) {

	$profile = new user;

	$_getProfile = $profile->getFromAccount($ac);

	if ($_getProfile->id > 0){
		$this->addTwigVars('profile', $_getProfile);

		$pointsAdded = NULL;
		$pointsAdded = $_getProfile->getPointsAdded();

		//var_dump($pointsAdded);
		//exit;

		$this->addTwigVars('pointsAdded', $pointsAdded);


	} else {
		header("Location: /");
		exit;
	}
		
} else {
	header("Location: /");
	exit;
}


$template = $this->twig->loadTemplate('profiles/profile.twig');
echo $template->render($this->twigVars);

?>