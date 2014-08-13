<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

$obP = new project;

$_project = $obP->getFromSlug($this->url_var[1]);

// No project?
if (empty($_project->id)) {
    header("Location: " . $this->getUrlFromId(2) . "?ref=" . $this->getUrl());
    exit;
}

// Project not accepted?
if (!$_project->isActive() && !$_user->isAdmin()) {
	header("Location: " . $this->getUrlFromId(2) . "?ref=" . $this->getUrl());
	exit;
}

// General
$this->addTwigVars('project', $_project);
$this->addTwigVars('title', $_project->name() . " -  PH Clone");

switch (@$this->url_var[2]) {
    default:



        if (!empty($_POST) and $_user->logged && isset($_POST['comment'])) {

            $comment = new project_comment;
            if (!empty($_POST['reply-to']) && isset($_POST['reply-to'])){
                $comment->replyTo = (int)addslashes($_POST['reply-to']);  
            } else {
                $comment->replyTo = 0;
            }
            $comment->user = $_user->id;
            $comment->language = $this->language;
            $comment->project = $_project->id;
            $comment->date = date('Y-m-d H:i:s', time());
            $comment->comment = htmlentities(strip_tags($_POST['comment']));
            $comment->save();

            $goTo = strip_tags($this->url_var[0]) . '#comment-' . $comment->id;
            header("Location: $goTo");
            exit;
        }

        $obC = new project_comment;

        $commentsArray = $obC->getFromProject($_project->id);
        $this->addTwigVars('comments', $commentsArray);

    	$this->addTwigVars('userVotes', $_project->getUserVotes());
    	$template = $this->twig->loadTemplate('projects/project.twig');
		echo $template->render($this->twigVars);
        break;
}
