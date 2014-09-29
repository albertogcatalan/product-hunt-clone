<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Controller;
use Quaver\Model\Project;

if (isset($_POST['url']) &&  isset($_POST['title']) && isset($_POST['description'])) {

	$error = false;
    
    if ($_user->logged){
    		    			
		$project = new Project;
		
		$items['name'][1] = addslashes($_POST['title']);
		$items['description'][1] = addslashes($_POST['description']);
		/*foreach ($languages as $lang) {
	        $items['name'][$lang->id] = addslashes($_POST['nameP_' . $lang->id]);
	        $items['description'][$lang->id] = addslashes($project->description[$lang->id]);                
	    }*/

		if ($_POST['url']){
            
		    $searchForbiddenChar = array(
		    "%", '_', '[', ']', '^', '!', '<', '>', '¡', '=', '|', '?', '¿', 'ñ', '@', '#'
			);
			
			$query = trim(str_replace($searchForbiddenChar, "", ($_POST['url'])));	 
			$items['url'] = $query;
			$items['slug'] = $project->cleanString($items['name'][1]);
		} 

		
		$items['user'] = $_user->id;	    			
		$items['type'] = "basic";
		$items['added'] = time();
		$items['started'] = date('Y-m-d H:i:s', time());
		$items['active'] = 1;
		$items['cancelled'] = 0;
			
   	} else {
   		$error = true;
   	}    
            
    if (!$error) {
    	$project->setItem($items);
	    if ($project->save()){
	    	$this->addTwigVars("validation_ok", true);
	    } else {
	    	$this->addTwigVars("validation_ok", false);
	    }
		
    } else {
        $this->addTwigVars("validation_ok", false);
    }  	
}


//Load projects
$obj_p = new Project;

// Today projects
$today = date('Y-m-d', time());
$todayProjects = $obj_p->getLastProjects($today);
$this->addTwigVars('todayProjects', $todayProjects);

// Yesterday projects
$yDay = date('Y-m-d', time());
$yesterday = strtotime ('-1 day', strtotime($yDay));
$yesterday = date ('Y-m-d', $yesterday);
$yesterdayProjects = $obj_p->getLastProjects($yesterday);
$this->addTwigVars('yesterdayProjects', $yesterdayProjects);

// 2 days ago projects
$twoDay = date('Y-m-d', time());
$twoAgo = strtotime ('-2 day', strtotime($twoDay));
$twoAgo = date ('Y-m-d', $twoAgo);
$twoAgoProjects = $obj_p->getLastProjects($twoAgo);
$this->addTwigVars('twoAgoProjects', $twoAgoProjects);


$template = $this->twig->loadTemplate('home.twig');
echo $template->render($this->twigVars);

?>