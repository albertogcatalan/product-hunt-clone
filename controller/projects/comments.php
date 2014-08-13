<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

if (!empty($_POST) and $_user->logged && isset($_POST['comment'])) {

    $comment = new project_comment;
    if (!empty($_POST['reply-to']) && isset($_POST['reply-to'])){
        $comment->replyTo = (int)addslashes($_POST['reply-to']);  
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
$this->addTwigVars('comments', $obC->getFromProject($_project->id));

$template = $this->$twig->loadTemplate('projects/comments.twig');
echo $template->render($this->twigVars);
?>
