<?php
/**
 * Twitter strategy for Opauth
 * Based on https://dev.twitter.com/docs/auth/obtaining-access-tokens
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.TwitterStrategy
 * @license      MIT License
 */

class TwitterStrategy extends OpauthStrategy {
	
	/**
	 * Compulsory parameters
	 */
	public $expects = array('key', 'secret');
	
	/**
	 * Optional parameters
	 */
	public $defaults = array(
		'method' => 'POST', 		// The HTTP method being used. e.g. POST, GET, HEAD etc 
		'oauth_callback' => '{complete_url_to_strategy}oauth_callback',
		
		// For Twitter
		'request_token_url' => 'https://api.twitter.com/oauth/request_token',
		'authorize_url' => 'https://api.twitter.com/oauth/authenticate', // or 'https://api.twitter.com/oauth/authorize'
		'access_token_url' => 'https://api.twitter.com/oauth/access_token',
		'verify_credentials_json_url' => 'https://api.twitter.com/1.1/account/verify_credentials.json',
		'verify_credentials_skip_status' => true,
		'twitter_profile_url' => 'http://twitter.com/{screen_name}',

		// From tmhOAuth
		'user_token'					=> '',
		'user_secret'					=> '',
		'use_ssl'						=> true,
		'debug'							=> false,
		'force_nonce'					=> false,
		'nonce'							=> false, // used for checking signatures. leave as false for auto
		'force_timestamp'				=> false,
		'timestamp'						=> false, // used for checking signatures. leave as false for auto
		'oauth_version'					=> '1.0',
		'curl_connecttimeout'			=> 30,
		'curl_timeout'					=> 10,
		'curl_ssl_verifypeer'			=> false,
		'curl_followlocation'			=> false, // whether to follow redirects or not
		'curl_proxy'					=> false, // really you don't want to use this if you are using streaming
		'curl_proxyuserpwd'				=> false, // format username:password for proxy, if required
		'is_streaming'					=> false,
		'streaming_eol'					=> "\r\n",
		'streaming_metrics_interval'	=> 60,
		'as_header'				  		=> true,
	);
	
	public function __construct($strategy, $env) {
		parent::__construct($strategy, $env);
		
		$this->strategy['consumer_key'] = $this->strategy['key'];
		$this->strategy['consumer_secret'] = $this->strategy['secret'];
		
		require dirname(__FILE__).'/Vendor/tmhOAuth/tmhOAuth.php';
		$this->tmhOAuth = new tmhOAuth($this->strategy);
	}
	
	/**
	 * Auth request
	 */
	public function request() {
		$params = array(
			'oauth_callback' => $this->strategy['oauth_callback']
		);
		
		$results =  $this->_request('POST', $this->strategy['request_token_url'], $params);

		if ($results !== false && !empty($results['oauth_token']) && !empty($results['oauth_token_secret'])){
			if (!session_id()) {
				session_start();
			}
			$_SESSION['_opauth_twitter'] = $results;

			$this->_authorize($results['oauth_token']);
		}
	}

	/**
	 * Receives oauth_verifier, requests for access_token and redirect to callback
	 */
	public function oauth_callback() {
		if (!session_id()) {
			session_start();
		}
		$session = $_SESSION['_opauth_twitter'];
		unset($_SESSION['_opauth_twitter']);

		if (!empty($_REQUEST['oauth_token']) && $_REQUEST['oauth_token'] == $session['oauth_token']) {
			$this->tmhOAuth->config['user_token'] = $session['oauth_token'];
			$this->tmhOAuth->config['user_secret'] = $session['oauth_token_secret'];
			
			$params = array(
				'oauth_verifier' => $_REQUEST['oauth_verifier']
			);
		
			$results =  $this->_request('POST', $this->strategy['access_token_url'], $params);

			if ($results !== false && !empty($results['oauth_token']) && !empty($results['oauth_token_secret'])) {
				$credentials = $this->_verify_credentials($results['oauth_token'], $results['oauth_token_secret']);
				
				if (!empty($credentials['id'])) {
					
					$this->auth = array(
						'uid' => $credentials['id'],
						'info' => array(
							'name' => $credentials['name'],
							'nickname' => $credentials['screen_name'],
							'urls' => array(
								'twitter' => str_replace('{screen_name}', $credentials['screen_name'], $this->strategy['twitter_profile_url'])
							)
						),
						'credentials' => array(
							'token' => $results['oauth_token'],
							'secret' => $results['oauth_token_secret']
						),
						'raw' => $credentials
					);
					
					$this->mapProfile($credentials, 'location', 'info.location');
					$this->mapProfile($credentials, 'description', 'info.description');
					$this->mapProfile($credentials, 'profile_image_url', 'info.image');
					$this->mapProfile($credentials, 'url', 'info.urls.website');
					
					$this->callback();
				}
			}
		} else {
			$error = array(
				'code' => 'access_denied',
				'message' => 'User denied access.',
				'raw' => $_GET
			);

			$this->errorCallback($error);
		}
		
				
	}

	private function _authorize($oauth_token) {
		$params = array(
			'oauth_token' => $oauth_token
		);

		if (!empty($this->strategy['force_login'])) $params['force_login'] = $this->strategy['force_login'];
		if (!empty($this->strategy['screen_name'])) $params['screen_name'] = $this->strategy['screen_name'];

		$this->clientGet($this->strategy['authorize_url'], $params);
	}
	
	private function _verify_credentials($user_token, $user_token_secret) {
		$this->tmhOAuth->config['user_token'] = $user_token;
		$this->tmhOAuth->config['user_secret'] = $user_token_secret;
		
		$params = array( 'skip_status' => $this->strategy['verify_credentials_skip_status'] );
		
		$response = $this->_request('GET', $this->strategy['verify_credentials_json_url'], $params);
		
		return $this->recursiveGetObjectVars($response);
	}
	


	/**
	 * Wrapper of tmhOAuth's request() with Opauth's error handling.
	 * 
	 * request():
	 * Make an HTTP request using this library. This method doesn't return anything.
	 * Instead the response should be inspected directly.
	 *
	 * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
	 * @param string $url the request URL without query string parameters
	 * @param array $params the request parameters as an array of key=value pairs
	 * @param string $useauth whether to use authentication when making the request. Default true.
	 * @param string $multipart whether this request contains multipart data. Default false
	 */	
	private function _request($method, $url, $params = array(), $useauth = true, $multipart = false) {
		$code = $this->tmhOAuth->request($method, $url, $params, $useauth, $multipart);

		if ($code == 200) {
			if (strpos($url, '.json') !== false) {
				$response = json_decode($this->tmhOAuth->response['response']);
			} else {
				$response = $this->tmhOAuth->extract_params($this->tmhOAuth->response['response']);
			}
			
			return $response;		
		} else {
			$error = array(
				'code' => $code,
				'raw' => $this->tmhOAuth->response['response']
			);

			$this->errorCallback($error);
			
			return false;
		}
	}
	
}
