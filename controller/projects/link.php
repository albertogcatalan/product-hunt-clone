<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License
 * (see README for details)
 */

// Set vars
$_id = null;
$p = null;
$obj_project = null;

$_id = $this->url_var[1];
if (isset($_id)){
	$obj_project = new project;
	$p = $obj_project->getFromId($_id);

	header("Location: " . $p->slug);
	exit;    	

} else {
	header("Location: /");
	exit;    	
}

?>
