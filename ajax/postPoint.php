<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

require("../ajax.php");

if ($_user->logged){

	$response = NULL;

	$pid = $_REQUEST['pid'];
	$status = $_REQUEST['status'];
	$uid = $_user->id;

	if ($status == ''){
		$status = 0;
	}

	if (isset($pid) && isset($uid) && isset($status)){
		//$_date = date('Y-m-d', time());

		$obj_p = new project;
		$actionPoint = $obj_p->actionPoint($pid, $uid, $status);

		if ($actionPoint){
			$response = '<a href="#" class="vote" data-id='. $pid . ' data-status="0"><i class="vote-icon fa fa-caret-square-o-up fa-2x"></i></a>';
		} else {
			$response = '<a href="#" class="vote" data-id='. $pid . ' data-status="1"><i class="vote-icon-active fa fa-caret-square-o-up fa-2x"></i></a>';
		}

		echo json_encode($response);
	} else {
		echo json_encode($response);
	}

	
} else {
	echo json_encode($response);
}

?>