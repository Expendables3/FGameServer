<?php

/**
 * Copyright 2011 ZingMe
 * 
 */
if (!function_exists('curl_init')) {
    throw new Exception('ZingMe require the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('ZingMe require the JSON PHP extension.');
}

class BaseZingMe {
    /**
     * Version.
     */

    const GRAPHAPI_VERSION = '1.0';
    const CLIENT_GRAPHAPI_VERSION = 'php-1.02';

    /**
     * Default options for curl.
     */
    public static $CURL_OPTS = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => self::CLIENT_GRAPHAPI_VERSION,
    );

    /**
     * the environment type
     *
     * @var type 
     */
    protected $env = "development";

    /**
     * Maps aliases to zingme api domains.
     */
    public static $DOMAIN_MAP = array(
        "development" => array(
            'oauth' => 'https://dev-oauth-me.zing.vn/',
            'graph' => 'https://dev-graphapi-me.zing.vn/',
            'photo' => 'https://graphapi-me.zing.vn/',
        ),
        "production" => array(
            'oauth' => 'https://oauth-me.zing.vn/',
            'graph' => 'https://graphapi-me.zing.vn/',
            'photo' => 'http://graphapi-me.zing.vn/',
        )
    );

    /**
     * The Application App Name
     *
     * @var string
     */
    protected $appname;

    /**
     * The Application API Key.
     *
     * @var string
     */
    protected $apikey;

    /**
     * The Application API Secret.
     *
     * @var string
     */
    protected $secretkey;

    /**
     * A CSRF state variable to assist app/game in the defense against CSRF attacks
     */
    protected $state;

    /**
     * Indicates if the CURL based @ syntax for file uploads is enabled.
     *
     * @var boolean
     */
    protected $fileUploadSupport = false;

    /**
     * The OAuth access token received in exchange for a valid authorization
     * code.  null means the access token has yet to be determined.
     *
     * @var string
     */
    protected $accessToken = null;
    protected $signedRequest = null;
    protected $dataSignedRequest = null;
    private $accesstoken_path = "oauth/accesstoken";

    public function __construct($config = array()) {
        //$apikey, $secretkey, $env = 'production'

        if (!isset($config['appname']) || $config['appname'] == '') {
            throw new ZingMeApiException(-9999, "ClientGraphAPIException", "appname miss in config");
        }

        $this->appname = $config['appname'];

        if (isset($config["apikey"])) {
            $this->apikey = $config["apikey"];
        }

        if (isset($config["secretkey"])) {
            $this->secretkey = $config["secretkey"];
        }

        if (isset($config["env"])) {
            $this->env = $config["env"];
        } else {
            $this->env = "development";
        }
    }

    private function useFileUploadSupport() {
        return $this->fileUploadSupport;
    }

    /**
     * Sets the access token for api calls.  Use this if you get
     * your access token by other means and just want the SDK
     * to use it.
     *
     * @param string $access_token an access token.
     * 
     */
    public function setAccessToken($access_token) {
        $this->accessToken = $access_token;
    }

    /**
     * Return access token was sets	 
     *
     * @param string $access_token an access token.
     * 
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    public function getUrlAuthorized($redirect_uri, $state = '') {
        $params = array();
        $params['client_id'] = $this->apikey;
        $params['redirect_uri'] = $redirect_uri;
        if (!empty($state)) {
            $params['state'] = $state;
        }
        $login_url = $this->getUrl('oauth', 'oauth/authorize', $params);
        return $login_url;
    }

    /**
     * If app/game running on ZingMe canvas (such as http://me.zing.vn/apps/[appname].
     * So app/game can get userid from signed_request param. if app/game run out side canvas
     * app/game need to get Me Info to get userid of user logged in.
     * @param string get userid of user logged in.
     */
    public function getUserLoggedIn($signed_request) {
        $data_signed_request = $this->getSignedRequest($signed_request);
        if ($data_signed_request) {
            if (array_key_exists('uid', $data_signed_request)) {
                if ($this->checkSignedRequestExpires($data_signed_request)) {
                    $uid = $data_signed_request['uid'];
                    return $uid;
                }
            }
        }
        throw new ZingMeApiException(-10004, "ClientGraphAPIException", "can not get usreid form signed request");
    }

    /**
     *
     * Get access token from authorized code
     * 
     * @param type $code
     * @return array data of access token
     */
    public function getAccessTokenFromCode($code) {
        $path = sprintf($this->accesstoken_path);

        $params = array();
        $params['client_id'] = $this->apikey;
        $params['client_secret'] = $this->secretkey;

        $url = $this->getUrl("oauth", $path, $params);
        $url .= "&code=" . $code;
        $data = $this->sendRequest($url);
        return $data;
    }

    /**
     * Return access token from signed request. It commonly used when an app/game 
     * running on ZingMe via url http://me.zing.vn/[appname]	
     *
     * @return string The access token
     */
    public function getAccessTokenFromSignedRequest($signed_request) {
        $data_signed_request = $this->getSignedRequest($signed_request);

        if ($data_signed_request) {
            if (array_key_exists('access_token', $data_signed_request)) {
                if ($this->checkSignedRequestExpires($data_signed_request)) {
                    $access_token = $data_signed_request['access_token'];
                    return $access_token;
                }
            }
        }
        throw new ZingMeApiException(-10002, "ClientGraphAPIException", "Can not get access token from signed request");
    }

    ///////////////////////////

    protected function checkSignedRequestExpires($data_signed_request) {
        if (array_key_exists('expires', $data_signed_request) && array_key_exists('issued_at', $data_signed_request)) {
            $expires = $data_signed_request['expires'];
            $issued_at = $data_signed_request['issued_at'];
            $time = time();
            if ($time > ($expires + $issued_at)) {
                $this->dataSignedRequest = null;
                throw new ZingMeApiException(-13, "OAuthException", "Access token had been expired");
            }
        }
        return true;
    }

    protected function getSignedRequest($signed_request) {
        if ($this->signedRequest != $signed_request || $this->dataSignedRequest == null) {
            $this->dataSignedRequest = $this->parseSignedRequest($signed_request);
            if ($this->dataSignedRequest != null)
                $this->signedRequest = $signed_request;
        }
        return $this->dataSignedRequest;
    }

    protected function parseSignedRequest($signed_request) {

        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = self::base64UrlDecode($encoded_sig);
        $data = json_decode(self::base64UrlDecode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            self::errorLog('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $this->getApiSecret(), $raw = true);

        if ($sig !== $expected_sig) {
            self::errorLog('Bad Signed JSON signature!');
            throw new ZingMeApiException(-10005, "ClientGraphAPIException", "Bad Signed JSON signature!");
            return null;
        }

        return $data;
    }

    protected function getApiSecret() {
        return "zme." . $this->secretkey . "." . $this->apikey;
    }

    protected function getUrl($name, $path = '', $params = array()) {
        $url = self::$DOMAIN_MAP[$this->env][$name];
        if ($path) {
            if ($path[0] === '/') {
                $path = substr($path, 1);
            }
            $url .= $path;
        }

        if ($params) {
            $url .= '?' . http_build_query($params, null, '&');
        }
        return $url;
    }

    protected function sendRequest($url, $params = null, $ch = null) {
        if (!$ch) {
            $ch = curl_init();
        }

        if ($params == null)
            $params = array();

        $opts = self::$CURL_OPTS;

        if ($params !== null && is_array($params)) {
            $params['_ver'] = self::GRAPHAPI_VERSION;
            $params['_client_ver'] = self::CLIENT_GRAPHAPI_VERSION;
            if ($this->useFileUploadSupport()) {
                $opts[CURLOPT_POSTFIELDS] = $params;
            } else {
                $opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
            }
        }

        $opts[CURLOPT_URL] = $url;

        if (isset($opts[CURLOPT_HTTPHEADER])) {
            $existing_headers = $opts[CURLOPT_HTTPHEADER];
            $existing_headers[] = 'Expect:';
            $opts[CURLOPT_HTTPHEADER] = $existing_headers;
        } else {
            $opts[CURLOPT_HTTPHEADER] = array('Expect:');
        }
		$opts[CURLOPT_SSL_VERIFYPEER] = false;
        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);

        if (curl_errno($ch) == 60) { // CURLE_SSL_CACERT
            self::errorLog('Invalid or no certificate authority found');
            throw new ZingMeApiException(-10000, "ClientGraphAPIException", "Curl: Invalid or no certificate authority found");
        }
        if (curl_errno($ch)) {
            throw new ZingMeApiException(
                    curl_errno($ch), curl_error($ch), ""
            );
        }
        curl_close($ch);
        $result = $this->parseResponse($response);
        return $result;
    }

    protected function sendRequestPhoto($url, $accesstoken, $filename, $description) {
        $handle = fopen($filename, "r");
        $filedata = fread($handle, filesize($filename));
        fclose($handle);
        /* End data test */
        $post = array( 'file' => $filedata, "access_token" => $accesstoken, "description" => $description);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/plain'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        $rs = curl_exec($ch);
        curl_close($ch);
    }

    protected function parseResponse($response) {

        try {
            $json = json_decode($response, true);
        } catch (Exception $e) {
            throw new ZingMeApiException(-100001, "json decode", $e->getMessage());
        }

        if ($json['error_code'] != 0) {
            $error_type = -1;
            if (isset($json['error_type'])) {
                $error_type = $json['error_type'];
            }
            throw new ZingMeApiException($json['error_code'], $error_type, $json['error_message']);
        }

        return $json['data'];
    }

    /**
     * Prints to the error log if you aren't in command line mode.
     *
     * @param string $msg Log message
     */
    protected static function errorLog($msg) {
        // disable error log if we are running in a CLI environment
        // @codeCoverageIgnoreStart
        if (php_sapi_name() != 'cli') {
            error_log($msg);
        }
        // uncomment this if you want to see the errors on the page
        // print 'error_log: '.$msg."\n";
        // @codeCoverageIgnoreEnd
    }

    /**
     * Base64 encoding that doesn't need to be urlencode()ed.
     * Exactly the same as base64_encode except it uses
     *   - instead of +
     *   _ instead of /
     *
     * @param string $input base64UrlEncoded string
     * @return string
     */
    protected static function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

}

class ZingMeApiException extends Exception {

    private $_err_code = 0;
    private $_err_type = "";
    private $_err_msg = "";

    public function __construct($err_code, $err_type, $err_msg) {
        $this->_err_code = $err_code;
        $this->_err_type = $err_type;
        $this->_err_msg = $err_msg;

        parent::__construct($this->_err_msg, $this->_err_code);
    }

    public function getErrType() {
        return $this->_err_type;
    }

    public function getErrMsg() {
        return $this->_err_msg;
    }

    public function __toString() {
        $str = $this->_err_type . ': ';
        if ($this->_err_code != 0) {
            $str .= $this->_err_code . ': ';
        }
        return $str . $this->_err_msg;
    }

}

?>
