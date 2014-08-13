<?php
/*
 * Copyright (c) 2014 Alberto González
 * Distributed under Apache License 2.0
 * (see README for details)
 */


/**
 * core class
 */
class core {

    public $db; //DB object

	// URL management
    public $url_var;
    public $queryString;

	// Language system
    public $language;
    
    // Template system
    public $twig = null;
    public $twigVars = array();

    // Development
    public $debug = false;
    public $log = array();


    /**
     * _constructor
     */
    public function __construct() {

        // Create new DB object
        $this->db = new DB;


        // Twig Template System Loader
        require_once(LIB_PATH . '/Twig/Autoloader.php');
        Twig_Autoloader::register();


        // Getting all directories in /template
        $path = P_PATH . 'template';

        $templatesDir = array($path);
        $dirsToScan = array($path);

        $dirKey = 0;
        while (count($dirsToScan) > $dirKey) {
            $results = scandir($dirsToScan[$dirKey]);
            foreach ($results as $result) {
                if ($result === '.' or $result === '..'
                    or $result == 'cache') continue;

                if (is_dir($dirsToScan[$dirKey] . '/' . $result)) {
                    $templatesDir[] = $dirsToScan[$dirKey] . '/' . $result;
                    $dirsToScan[] = $dirsToScan[$dirKey] . '/' . $result;
                }
            }
            $dirKey++;
        }

		//get query string from URL to core var
        $this->getQueryString();
        $loader = new Twig_Loader_Filesystem($templatesDir);

        $twig_options = array();
        if (defined(TEMPLATE_CACHE) && TEMPLATE_CACHE) $twig_options['cache'] = "./template/cache";
        if (defined(CACHE_AUTO_RELOAD) && CACHE_AUTO_RELOAD) $twig_options['auto_reload'] = true;
        
        $this->twig = new Twig_Environment($loader, $twig_options);

        // Clear cache
        if (isset($this->queryString['clearCache'])) {
            $this->twig->clearCacheFiles();
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header("Location: $url");
            exit;
        }

        // Restoring user session
        if (!empty($this->queryString['PHPSESSID'])) {
            $sessionHash = $this->queryString['PHPSESSID'];
            $_userFromSession = new user;
            $_userFromSession->setCookie($sessionHash);
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header("Location: $url");
            exit;
        }


    }


    /**
     * @param bool $_mvc
     */
    public function start($_mvc = true) {
    	
        global $_lang, $_user;
        
        // Check user login
        $_user = new user;
        if (!empty($_COOKIE[COOKIE_NAME . "_log"])) {
            $_user->getFromCookie($_COOKIE[COOKIE_NAME . "_log"]);
        }

        // Load language
        $_lang = new lang;
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

    /*
     * Global functions
     */

    /**
     * @param $_url
     */
    public function fixTrailingSlash($_url) {

        if ($_url{strlen($_url) - 1} != '/' && strstr($_url, "image/") === false) {
            header("Location: " . $_url . "/");
            exit;
        }
    }

    /**
     * @param $_id
     * @return mixed
     */
    public function getUrlFromId($_id) {
        $_id = (int)$_id;
        $url = $this->db->query("SELECT url FROM url WHERE id = '$_id'");
        $regex = "/(\(.*\))/";
        return preg_replace($regex, "", $url->fetchColumn(0));
    }

    /**
     *
     */
    public function loadMVC()
    {
        $url = $this->getUrl();
        $this->fixTrailingSlash($url);
        $mvc = $this->getVT($url);
        if ($mvc != false) {

            $this->loadController($mvc['controller']);
            
        }
    }


    /**
     * @param $_controllerName
     */
    public function loadController($_controllerName) {

        global $_user, $_lang;
        
        $controllerPath = GLOBAL_PATH . "/controller/" . $_controllerName . ".php";

        $this->getGlobalTwigVars();

        // Load controller
        if (file_exists($controllerPath)) {
            require_once($controllerPath);
        } else {
            if (!empty($_controllerName))
                $this->log("Error loading controller: $_controllerName", "error");
        }
    }


    /**
     *
     */
    public function getQueryString() {
        $uri = $_SERVER['REQUEST_URI'];
        $qs = parse_url($uri, PHP_URL_QUERY);
        if (!empty($qs)) {
            parse_str($qs, $this->queryString);
        }            
    }

    // Get controller template
    /**
     * @param $_url
     * @return mixed
     */
    public function getVT($_url) {
        $result = null;

        $mvc_items = $this->db->query("SELECT * FROM url WHERE enabled = 1");
        $result = $mvc_items->fetchAll();

        foreach ($result as $item) {
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
            $this->loadController('404');
            //die('error 404');
        }
        return $return;
    }

    /**
     * @return string
     */
    public function getUrl() {
        $url = $_SERVER['REQUEST_URI'];
        if (strstr($url, "?") !== false)
            $url = substr($url, 0, strpos($url, "?")); // Remove GET vars
        return $url;
    }

    /*
    * Templates
    */


    /**
     *
     */
    public function getGlobalTwigVars() {
        global $_user, $_lang;

        // Language
        $this->addTwigVars("language", $_lang);

        // Environment
        $this->addTwigVars("_env", DEV_MODE);

        // Languages
        $languageVars = array();
        $ob_l = new lang;
        foreach ($ob_l->getList() as $lang) {
            $item = array(
                "id" => $lang->id,
                "domain" => HTTP_MODE . "www." . DOMAIN_NAME . $lang->tld,
                "name" => utf8_encode($lang->name),
                "slug" => $lang->slug,
                "locale" => $lang->locale,
                "url" => "language/" . $lang->slug . "/",
                "class" => ($_lang->id == $lang->id) ? 'selected' : ''
            );
            array_push($languageVars, $item);
        }
        $this->addTwigVars('languages', $languageVars);

        $this->addTwigVars('actual_url', strip_tags($this->url_var[0]));


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

        // Config
        $config = array(
            "baseHref" => HTTP_MODE . DOMAIN_NAME,
            "thisHref" => HTTP_MODE . DOMAIN_NAME . $this->getUrl(),
            "randomVar" => RANDOM_VAR
        );

        $this->addTwigVars('config', $config);

    }

    /**
     * @param $_key
     * @param $_array
     */
    public function addTwigVars($_key, $_array) {
        $this->twigVars[$_key] = $_array;
    }


}
?>