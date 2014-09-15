<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 * Based on AwesomezGuy autoloader
 */

namespace Quaver;

/**
 * load file
 * @param type $namespace 
 * @return type
 */
function load($namespace) {
	$splitpath = explode('\\', $namespace);
	$path = '';
	$name = '';
	$firstword = true;
	$countSplitPath = count($splitpath);

	for ($i = 0; $i < $countSplitPath; $i++) {
		if ($splitpath[$i] && !$firstword) {
			if ($i == count($splitpath) - 1)
				$name = $splitpath[$i];
			else
				$path .= DIRECTORY_SEPARATOR . $splitpath[$i];
		}
		if ($splitpath[$i] && $firstword) {
			if ($splitpath[$i] != __NAMESPACE__)
				break;
			$firstword = false;
		}
	}
	if (!$firstword) {
		$fullpath = __DIR__ . $path . DIRECTORY_SEPARATOR . $name . '.php';
		return include_once($fullpath);
	}
	return false;
}

/**
 * loadPath
 * @param type $absPath 
 * @return type
 */
function loadPath($absPath) {
	return include_once($absPath);
}

spl_autoload_register(__NAMESPACE__ . '\load');

?>
