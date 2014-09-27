<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Controller;
use Quaver\Model\LangStrings;

if (!$_user->isAdmin()) {
    header("Location: /");
    exit;
} 

if (!$_user->logged) {
    header("Location: /");
    exit;
}

$this->addTwigVars('section', 'languages');

if (@isset($_POST['edit']) || @isset($_POST['add'])) {
	$added = false;

    $item = new LangStrings;

    foreach ($_POST['language'] as $k => $v) {
        
        $new_lang = new LangStrings;
        
        if ($_POST['idL'][$k]){
            $_l['id'] = $_POST['idL'][$k];
        } else {
            $_l['id'] = null;
        }
        
        $_l['language'] = $_POST['language'][$k];
        $_l['label'] = $_POST['label'];
        $_l['text'] = $_POST['text'][$k];
        
        $new_lang->setItem($_l);

        $_item['_languages'][] = $new_lang;  
    
    }
    
    $item->setItem($_item);      

    if ($item->saveAll()) {
        header("Location: /admin/languages");
        exit;
    } else {
        $added = false;
    }
}

switch ($this->url_var[1]) {

    case('add'):
    	$this->addTwigVars('typePOST', 'add');
    	if ($added){
	    	header("Location: /admin/languages");
	    	exit;
    	} else {
	    	$template = $this->twig->loadTemplate('admin/lang-Add.twig');
    	}
    	echo $template->render($this->twigVars);
    	break;
    case('edit'):
   	 	$this->addTwigVars('typePOST', 'edit');
        $lang = new LangStrings;
    	$item = $lang->getFromLabel($this->url_var[2]);
    	$this->addTwigVars('item', $item);
	    $template = $this->twig->loadTemplate('admin/lang-Add.twig');
    	echo $template->render($this->twigVars);
    	break;
    case('del'):
        $lang = new LangStrings;
	    $items = $lang->getFromLabel($this->url_var[2]);
        foreach ($items as $item) {
            $item->delete();
        }
        header("Location: /admin/languages");
	    exit;
		break;
    default:
        $lang = new LangStrings;
    	$items = $lang->getLanguageList();
		$this->addTwigVars('items', $items);
		$template = $this->twig->loadTemplate('admin/lang-List.twig');
		echo $template->render($this->twigVars);
        break;
}




