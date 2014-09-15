<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under MIT License
 * (see README for details)
 */

namespace Quaver\Core;

use Quaver\Core\DB;
use Quaver\Model\Lang;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Quaver\Model\User;

/**
 * Core class
 */
class Core
{
    // DB object
    public $db; 

	// URL management
    public $url_var;
    public $queryString;

	// Language system
    public $language;
    
    // Template system
    public $twig = null;
    public $twigVars = array();

    /**
     * constructor
     */
    public function __construct()
    {
        // Create new DB object
        $this->db = new DB;

        // Twig Template System Loader
        require_once(LIB_PATH . '/Twig/Autoloader.php');
        \Twig_Autoloader::register();

        // Getting all directories in /template
        $path = VIEW_PATH;

        $templatesDir = array($path);
        $dirsToScan = array($path);

        $dirKey = 0;
        while (count($dirsToScan) > $dirKey) {
            $results = scandir($dirsToScan[$dirKey]);
            foreach ($results as $result) {
                if ($result === '.' || $result === '..') continue;

                if (is_dir($dirsToScan[$dirKey] . '/' . $result)) {
                    $templatesDir[] = $dirsToScan[$dirKey] . '/' . $result;
                    $dirsToScan[] = $dirsToScan[$dirKey] . '/' . $result;
                }
            }
            $dirKey++;
        }

		//get query string from URL to core var
        $this->getQueryString();
        $loader = new \Twig_Loader_Filesystem($templatesDir);

        $twig_options = array();
        if (defined('TEMPLATE_CACHE') && TEMPLATE_CACHE) $twig_options['cache'] = "./Cache";
        if (defined('CACHE_AUTO_RELOAD') && CACHE_AUTO_RELOAD) $twig_options['auto_reload'] = true;
        
        $this->twig = new \Twig_Environment($loader, $twig_options);

        // Clear Twig cache
        if (defined(TEMPLATE_CACHE) && TEMPLATE_CACHE) {
            if (isset($this->queryString['clearCache'])) {
                $this->twig->clearCacheFiles();
                $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                header("Location: $url");
                exit;
            }
        }

        // Restoring user_default session sample    
        if (!empty($this->queryString['PHPSESSID'])) {
            $sessionHash = $this->queryString['PHPSESSID'];
            $_userFromSession = new User;
            $_userFromSession->setCookie($sessionHash);
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header("Location: $url");
            exit;
        }
        


    }

    /**
     * @param bool $_mvc
     */
    public function start($_mvc = true)
    {	
        global $_lang;

        // Set user_default global
        global $_user;
        $_user = new User;
        if (!empty($_COOKIE[COOKIE_NAME . "_log"])) {
            $_user->getFromCookie($_COOKIE[COOKIE_NAME . "_log"]);
        }
        
       
        // Load language
        $_lang = new Lang;
        if (!empty($_GET['lang'])) {
            $lang_slug = substr($_GET['lang'], 0, 3);
            $_lang->getFromSlug($lang_slug);
            $_lang->setCookie();
        } else {
            $_lang->getSiteLanguage();
        }
        $this->language = $_lang->id;

        // Assoc URL to MVC
        if ($_mvc) $this->loadMVC();
    }


    /**
     * Load architecture
     */
    public function loadMVC()
    {
        $url = $this->getUrl();
        $this->fixTrailingSlash($url);
        $mvc = $this->getVT($url);
        if ($mvc != false) {
            $this->setController($mvc['controller']);   
        }
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $url = $_SERVER['REQUEST_URI'];
        if (strstr($url, "?") !== false)
            $url = substr($url, 0, strpos($url, "?")); // Remove GET vars
        return $url;
    }

    /**
     * @param $_url
     */
    public function fixTrailingSlash($_url)
    {
        if ($_url{strlen($_url) - 1} != '/' && strstr($_url, "image/") === false) {
            header("Location: " . $_url . "/");
            exit;
        }
    }

    /**
     * @param $_url
     * @return mixed
     */
    public function getVT($_url)
    {
        $routes = null;

        try {
            $yaml = new Parser();
            $routes = $yaml->parse(file_get_contents('./Quaver/Routes.yml'));

        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }

        foreach ($routes as $item) {
            $regexp = "/^" . str_replace(array("/", "\\\\"), array("\/", "\\"), $item['url']) . "$/";
            preg_match($regexp, $_url, $match);

            if (@$match) {
                $this->url_var = $match;
                $mvc = $item;
                break;
            }
        }

        if (@$mvc) {
            $return = $mvc;
        } else {
            $this->setController('e404');
        }
        return $return;
    }


    /**
     * @param $_controllerName
     */
    public function setController($_controllerName)
    {
        global $_lang;
        global $_user;
        
        $controllerPath = CONTROLLER_PATH . "/" . $_controllerName . ".php";

        $this->getGlobalTwigVars();

        // Load controller
        if (file_exists($controllerPath)) {
            require_once($controllerPath);
        } else {
            if (!empty($_controllerName)){
                $msg = "Error loading controller: $_controllerName";
                error_log($msg);
                echo "<h1>{$msg}</h1>";
                die;
            }

        }
    }


    /**
     * URL parser
     */
    public function getQueryString()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $qs = parse_url($uri, PHP_URL_QUERY);
        if (!empty($qs)) {
            parse_str($qs, $this->queryString);
        }            
    }


    /**
     * Set main variables
     */
    public function getGlobalTwigVars()
    {
        global $_lang;
        global $_user;

        // Language
        $this->addTwigVars("language", $_lang);

        // Environment
        $this->addTwigVars("_env", DEV_MODE);

        // Languages
        $languageVars = array();
        $ob_l = new Lang;
        foreach ($ob_l->getList() as $lang) {
            $item = array(
                "id" => $lang->id,
                "name" => utf8_encode($lang->name),
                "slug" => $lang->slug,
                "locale" => $lang->locale,
            );
            array_push($languageVars, $item);
        }
        $this->addTwigVars('languages', $languageVars);

        // Current url
        $this->addTwigVars('url', strip_tags($this->url_var[0]));

        // User
        $userVars = array(
            "avatar" => $_user->avatar,
            "admin" => $_user->isAdmin(),
            "logged" => $_user->logged,
            "account" => $_user->account,
            "sessionHash" => $_user->cookie,
        );
        
        $this->addTwigVars("user", $userVars);
        $this->addTwigVars("_user", $_user);

        // Login
        if (@isset($this->queryString['login-error']))
            $this->addTwigVars('loginError', true);

        if (@isset($this->queryString['user-disabled']))
            $this->addTwigVars('userDisabled', true);

        // Extra parametres
        $config = array(
            "randomVar" => RANDOM_VAR,
            "css" => CSS_PATH,
            "js" => JS_PATH,
            "img" => IMG_PATH,
        );

        $this->addTwigVars('qv', $config);

    }

    /**
     * @param $_key
     * @param $_array
     */
    public function addTwigVars($_key, $_array)
    {
        $this->twigVars[$_key] = $_array;
    }

}
?>
