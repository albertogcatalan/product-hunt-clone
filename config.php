<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */

// Paths
define('GLOBAL_PATH', dirname( __FILE__ ));
define('MODEL_PATH', GLOBAL_PATH . '/app/model');
define('LIB_PATH', GLOBAL_PATH . '/app/lib');

// Main
define('DOMAIN_NAME', 'yourdomain.com');
define('DOMAIN_TEST', '');

// Cookies
define('COOKIE_NAME', 'yourcookiename');
define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
define('COOKIE_PATH', '/');

// Modes
define('HTTP_MODE', 'http://');
define('MAINTENANCE_MODE', false);
define('DEV_MODE', true);

// MANDRILL
define('MANDRILL', false);
define('MANDRILL_USERNAME', '');
define('MANDRILL_APIKEY', '');

// Contact mail
define('CONTACT_EMAIL', '');
define('CONTACT_NAME', '');

// Random variable to FrontEnd files
define('RANDOM_VAR', ''); // format YYYYMMDD

// Template cache (Twig)
define('TEMPLATE_CACHE', false);

// Auto reload cache (Twig)
define('CACHE_AUTO_RELOAD', true);

// Default Language
define('LANG', 1);

// Domains with language
define('LANG_TLD', serialize(array(
    ".com" => 1,
    ".es" => 2
)));

// Database configuration
define('DB_PREFIX', 'qv_'); //DISABLED
define('DB_HOSTNAME',  '');
define('DB_USERNAME', ''); 
define('DB_PASSWORD', ''); 
define('DB_DATABASE', ''); 

/*
 * Cypher
 *
 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
 * /!\ /!\ /!\ /!\ /!\ WARNING /!\ /!\ /!\ /!\ /!\ /!\ /!\
 * /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\ /!\
 * PLEASE DON'T CHANGE OR DELETE
 *
 */
define('CIPHER_KEY', ''); //RC4

/*
 * Global variables DO NOT TOUCH
 */
$_user = '';
$_language = '';

?>