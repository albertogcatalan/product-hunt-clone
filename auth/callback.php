<?php
require("../ajax.php");

use Quaver\Model\User;

/**
 * Callback for Opauth
 * 
 * This file (callback.php) provides an example on how to properly receive auth response of Opauth.
 * 
 * Basic steps:
 * 1. Fetch auth response based on callback transport parameter in config.
 * 2. Validate auth response
 * 3. Once auth response is validated, your PHP app should then work on the auth response 
 *    (eg. registers or logs user in to your site, save auth data onto database, etc.)
 * 
 */

/**
 * Define paths
 */
define('CONF_FILE', dirname(__FILE__).'/'.'opauth.conf.php');
define('OPAUTH_LIB_DIR', dirname(__FILE__).'/Opauth/');

/**
* Load config
*/
if (!file_exists(CONF_FILE)){
	trigger_error('Config file missing at '.CONF_FILE, E_USER_ERROR);
	exit();
}
require CONF_FILE;

/**
 * Instantiate Opauth with the loaded config but not run automatically
 */
require OPAUTH_LIB_DIR.'Opauth.php';
$Opauth = new Opauth( $config, false );

	
/**
* Fetch auth response, based on transport configuration for callback
*/
$response = null;

switch($Opauth->env['callback_transport']){	
	case 'session':
		session_start();
		$response = $_SESSION['opauth'];
		unset($_SESSION['opauth']);
		break;
	case 'post':
		$response = unserialize(base64_decode( $_POST['opauth'] ));
		break;
	case 'get':
		$response = unserialize(base64_decode( $_GET['opauth'] ));
		break;
	default:
		echo '<strong style="color: red;">Error: </strong>Unsupported callback_transport.'."<br>\n";
		break;
}

/**
 * Check if it's an error callback
 */
if (array_key_exists('error', $response)){
	echo '<strong style="color: red;">Authentication error: </strong> Opauth returns error auth response.'."<br>\n";
}

/**
 * Auth response validation
 * 
 * To validate that the auth response received is unaltered, especially auth response that 
 * is sent through GET or POST.
 */
else{
	if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])){
		echo '<strong style="color: red;">Invalid auth response: </strong>Missing key auth response components.'."<br>\n";
	}
	elseif (!$Opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)){
		echo '<strong style="color: red;">Invalid auth response: </strong>'.$reason.".<br>\n";
	}
	else {
	
		// REF
	    $goTo = '/';
	    if (!empty($_GET['ref'])) {
	        $goTo = strip_tags(addslashes($_GET['ref']));
	    } elseif (!empty($_SERVER['HTTP_REFERER'])) {
	        $goTo = strip_tags(addslashes($_SERVER['HTTP_REFERER']));
	    }		
		
		switch($response['auth']['provider']){	

			case 'Twitter':
				$user = new User;
			    
				$_idU = $user->isRegistered($response['auth']['uid']);

				if ($_idU) {
					
					$user->getFromId($_idU);

					if ($user->isActive()) {

						$item['name'] = $response['auth']['info']['name'];
				       	$item['active'] = 1;
				       	$item['last_login'] = time();

						// Login data
						$item['uid'] = $response['auth']['uid'];
			       		$item['token'] = $response['auth']['credentials']['token'];
			       		$item['secret'] = $response['auth']['credentials']['secret'];
			       		$item['signature'] = $response['signature'];

			       		//Profile data
			       		$item['account'] = $response['auth']['info']['nickname'];

			       		if (isset($response['auth']['info']['description'])){
			       			$item['biography'] = $response['auth']['info']['description'];
			       		}

			       		if (isset($response['auth']['info']['location'])){
			       			$item['location'] = $response['auth']['info']['location'];
			       		}

			       		if (isset($response['auth']['raw']['entities']['url']['urls'][0]['display_url'])){
			       			$item['web_link'] = $response['auth']['raw']['entities']['url']['urls'][0]['display_url'];
			       		}
		       			
		       			//Save image
		       			$url_image = $response['auth']['info']['image'];
		       			$item['avatar'] = str_replace("_normal", "", $url_image);

				       	$user->setItem($item);
				       	$user->save();
						
		                // Logged in		                
		                $user->setCookie();
		               
		                header("Location: $goTo");
		                exit;    
		            } else {
		                // User not active
		                $_error_login = true;
		                $goTo = '/login/?user-disabled';
		                header("Location: /?user-disabled");
		                exit;
		            }
			       		
			       		
				} else {
			    
			       	$item['name'] = $response['auth']['info']['name'];
			       	$item['email'] = "";
			       
			       	$item['level'] = "user";
			       	$item['active'] = 1;
			       	$item['registered'] = time();
			       	$item['last_login'] = time();
			       	$item['newsletter'] = 0;
					$item['language'] = 1;

					// Login data
					$item['uid'] = $response['auth']['uid'];
		       		$item['token'] = $response['auth']['credentials']['token'];
		       		$item['secret'] = $response['auth']['credentials']['secret'];
		       		$item['signature'] = $response['signature'];

		       		//Profile data
		       		$item['account'] = $response['auth']['info']['nickname'];

		       		if (isset($response['auth']['info']['description'])){
		       			$item['biography'] = $response['auth']['info']['description'];
		       		}

		       		if (isset($response['auth']['info']['location'])){
		       			$item['location'] = $response['auth']['info']['location'];
		       		}

		       		if (isset($response['auth']['raw']['entities']['url']['urls'][0]['display_url'])){
		       			$item['web_link'] = $response['auth']['raw']['entities']['url']['urls'][0]['display_url'];
		       		}
	       			
	       			//Save image
	       			$url_image = $response['auth']['info']['image'];
	       			$item['avatar'] = str_replace("_normal", "", $url_image);

			       	$user->setItem($item);
			       	$user->save();
			       
			    	if ($user->id > 0) {
			       		
		                // Logged in		                
			       		$user->cookie();
			       		$user->setCookie();
			       	}
			       
			       header("Location: $goTo");
		           exit;
				    				    
			    }
								
				break;
		}
	
	
		
	}
}