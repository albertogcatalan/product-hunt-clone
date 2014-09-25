<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

require("../ajax.php");

use Quaver\Model\Project;
use Quaver\Model\User;

//sanitize post value
$last_date = filter_var($_POST["last_date"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);

//set new project object
$p = new Project;

// prepare day block   
$datetime = DateTime::createFromFormat('Y-m-d', $last_date);
$datetime->sub(new DateInterval('P1D'));

$date = $datetime->format('Y-m-d');
$dateHeader = $datetime->format("l, F jS");

$headerBlock = '<div class="block-day">

    <time class="date center" datetime="'.$date.'">
        <i class="fa fa-calendar"></i>
            '.$dateHeader.'
        </time>';

$footerBlock = '</div>';

//Limit our results within a specified date
$results = $p->getLastProjects($date);

if ($results) { 

	//set new user
	$u = new User;

    if (!$_user->logged){
    	$modal = 'data-toggle="modal" data-target="#postModal"';
    }

    foreach ($results as $item) {
    	
	    if ($_user->logged){
	    	if ($u->checkPoint($item->id, $_user->id)) {
		        $point = '<a href="#" class="vote" data-id="'.$item->id.'" data-status="1" '.$modal.'>
		        <i class="vote-icon-active fa fa-caret-square-o-up fa-2x"></i></a>';
		     } else {
		        $point = '<a href="#" class="vote" data-id="'.$item->id.'" data-status="0" '.$modal.'>
		        <i class="vote-icon fa fa-caret-square-o-up fa-2x"></i></a>';
		     }
	    } else {
	    	$point = '<a href="#" class="vote" data-id="'.$item->id.'" data-status="0" '.$modal.'>
		        <i class="vote-icon fa fa-caret-square-o-up fa-2x"></i></a>';
	    }
	    	



    	$bodyBlock = '
    	<div class="project center-block">
		  <div class="block-post">
		    <div class="pointer">
		      <div class="point-content" data-id="'.$item->id.'">

		      '.$point.'

		      </div>
		      <span class="vote-count" data-id="'.$item->id.'">'.$item->points.'</span>
		    </div>
		    <div class="author">
		      <a href="/profile/'.$item->author->account.'" rel="tooltip" data-toggle="tooltip" data-placement="top" title="'.$item->author->name.' @'.$item->author->account.'">
		          <img alt="'.$item->author->account.'" class="image-rounded user-avatar" height="48" src="'.$item->author->avatar.'" width="48">
		      </a>
		    </div>
		    <div class="post">
		      <a class="url" href="/l/'.$item->id.'" target="_blank">'.$item->name().'</a>
		      <span class="post-description">'.$item->description().'</span>
		      <span class="post-actions">
		        <a href="/project/'.$item->slug.'" class="post-comments">
		          View Details
		        </a>
		      </span>
		    </div>
		  </div>';
        
    
    }
} 

echo $headerBlock.$bodyBlock.$footerBlock;

unset($obj);

?>
