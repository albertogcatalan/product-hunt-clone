<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver;

ini_set('display_errors', 0);

// Check config file
if ( !file_exists('../Quaver/Config.php') || !file_exists('../Quaver/Autoloader.php')) {

    $msg = "This instance of app doesn't seem to be configured, please read the deployment guide, configure and try again.";
    error_log($msg);
    echo "<h1>{$msg}</h1>";
    die;
    
}

// Autoloader
require_once('../Quaver/Autoloader.php');

// Load configuration
require_once('../Quaver/config.php');

// Check dev mode
if (defined('DEV_MODE')) {       
    if (DEV_MODE) {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        ini_set('display_errors', 1);
    }
}

// Load other main classes
require_once('../Quaver/Core/DB.php');
require_once('../Quaver/Core/Core.php');

// Load YAML Parser
require_once('../Quaver/Lib/yaml/vendor/autoload.php');

// Check maintenance 
if (defined(MAINTENANCE_MODE) && MAINTENANCE_MODE === true && $_SERVER['REQUEST_URI'] != '/maintenance') {
    header('Location: /maintenance');
    exit;
}

use Quaver\Core\Core;

// Init core
$core = new Core;
$core->start();

?>
