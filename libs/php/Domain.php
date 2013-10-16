<?php

if ( !defined( 'LF_DEFAULT_TLD' ) ) {
    define( 'LF_DEFAULT_TLD', 'livefyre.com' );
}
if ( !defined( 'LF_DEFAULT_PROFILE_DOMAIN' ) ) {
    define( 'LF_DEFAULT_PROFILE_DOMAIN', 'livefyre.com' );
}

define( 'LF_COOKIE_PREFIX', 'livefyre_' );

require_once dirname(__FILE__) . '/User.php';

/**
 * Livefyre Class representing a Livefyre Domain
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_Domain {

    /**
     * Host to point to
     *
     * @var string
     */
    private $_host;

    /**
     * Hash key for security function
     *
     * @var string
     */
    private $_key;

    /**
     * 
     *
     * @var string
     */
    private $_livefyreTLD;

    /**
     * Name of the Janrain Capture App
     *
     * @var string
     */
    private $engageAppName;

    /**
     * Name of the AuthDelegate Javascript variable
     *
     * @var string
     */
    private $authDelegate;

    /**
     * Array of strings to be manipulated in the widget
     *
     * Maps certain strings to certain parts of the commenting
     * widget to allow for customization and localization.
     *
     * @var string[]
     */
    private $strings;
    
    /**
     * @param string    network name
     * @param string    network hash key
     * @param string[]  domain options
     * @param string    http request helper
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct( $network, $key = null, $options, $httpAPI = null ) {
        if ( isset( $options['livefyre_tld'] ) ) {
            $this->setLivefyreTLD($options['livefyre_tld']);
        } else {
            $this->setLivefyreTLD(LF_DEFAULT_TLD);
        }
        if ( isset( $options['engage_app_name'] ) ) {
            $this->setEngageAppName($options['engage_app_name']);
        }
        if ( isset( $options['authDelegate'] ) ) {
            $this->setAuthDelegate($options['authDelegate']);
        }
        if ( isset( $options['strings'] ) ) {
            $this->setStrings($options['strings']);
        }
        $this->setHost($network);
        $this->setKey($key);
        if ( defined('LF_DEFAULT_HTTP_LIBRARY') ) {
            $httplib = LF_DEFAULT_HTTP_LIBRARY;
            $this->http = new $httplib;
        } else {
            require_once dirname(__FILE__) . '/Http.php';
            $this->http = new Livefyre_http; 
        }
    }
    
    /**
     * Getter for the Livefyre Top Level Domain
     * 
     * @return  string  Livefyre tld
     */
    public function getLivefyreTLD() {
        return $this->_livefyreTLD;
    }
    
    /**
     * Getter for the Livefyre host
     * 
     * @return  string  Livefyre host
     */
    public function getHost() {
        return $this->_host;
    }
    
    /**
     * Getter for the domain hash key
     * 
     * @return  string  Domain hash key
     */
    public function getKey() {
        return $this->_key;
    }
    
    /**
     * Getter for the Jainrain Engage App Name
     * 
     * @return  string  Janrain Engage App name
     */
    public function getEngageApp() {
        return $this->_engageAppName;
    }

    /**
     * Getter for the Livefyre Javascript Auth Delegate variable name
     * 
     * @return  string  Javascript auth delegate variable name
     */
    public function getAuthDelegate() {
        return $this->authDelegate;
    }

    /**
     * Getter for the strings array.
     *
     * Returns the array conainting the replacement values for the widget. This
     * allows for string customization and localization.
     * 
     * @return  string[]    Array of replacement strings
     */
    public function getStrings() {
        return $this->strings;
    }

    /**
     * Setter for the Livefyre Top Level Domain
     * 
     * @return  string  Livefyre tld
     */
    public function setLivefyreTLD($livefyreTLD) {
        $this->_livefyreTLD = $livefyreTLD;
    }
    
    /**
     * Setter for the livefyre host
     * 
     * @return  string  Livefyre host
     */
    public function setHost($host) {
        $this->_host = $host;
    }
    
    /**
     * Setter for the domain hash key
     * 
     * @return  string  Domain hash key
     */
    public function setKey($key) {
        $this->_key = $key;
    }
    
    /**
     * Setter for the Jainrain Engage App Name
     * 
     * @return  string  Janrain Engage App name
     */
    public function setEngageApp($appName) {
        $this->_engageAppName = $appName;
    }

    /**
     * Setter for the Livefyre Javascript Auth Delegate variable name
     * 
     * @return  string  Javascript auth delegate variable name
     */
    public function setAuthDelegate($authDelegate) {
        $this->_authDelegate = $authDelegate;
    }

    /**
     * Setter for the strings array.
     *
     * Returns the array conainting the replacement values for the widget. This
     * allows for string customization and localization.
     * 
     * @return  string[]    Array of replacement strings
     */
    public function setStrings($stringsName) {
        $this->_strings = $stringsName;
    }

    /**
     * Creates a Livefyre User class
     * 
     * @return  User   Array of replacement strings
     */
    public function user($uid, $displayName = null) {
        return new Livefyre_User($uid, $this, $displayName);
    }
    
    /**
     * Sets the pulling profile url for user profiles.
     *
     * Ping for pull registered url to paralelle Livefyre profiles and customer
     * profiles.
     * 
     * @param   string  Url of the site to pull user data from
     * @return  User    Array of replacement strings
     */
    public function setPullURL( $urlTemplate ) {
        $requestURL = 'http://' . $this->getHost() . '/?pull_profile_url='
            . urlencode($urlTemplate) . '&actor_token=' . $this->user('system')->token();
        return $this->http->request( $requestURL, array( 'method' => 'POST' ) );
    }
    
    public function setUserAffiliation( $userId, $type, $scope = 'domain', $targetId = null ) {
        $allowedTypes = array( 'admin', 'member', 'none', 'outcast', 'owner' );
        $allowedScope = array( 'domain', 'site', 'conv' );
        if ( !in_array( $type, $allowedTypes ) ) {
            trigger_error( 'You cannot set a Livefyre user\'s affiliation to a type other than the allowed: '
                . implode( ', ', $allowedTypes ), E_USER_ERROR );
            return false;
        } else {
            if ( !in_array( $scope, $allowedScope ) ) {
                trigger_error( 'You cannot set a Livefyre user\'s affiliation within a scope other than the allowed: '
                    . implode( ', ', $allowedScope ), E_USER_ERROR );
                return false;
            }
            $userJid = $userId . '@' . $this->getHost();
            $systemuser = $this->user( 'system' );
            $requestURL = 'http://' . $this->getHost() . '/api/v1.1/private/management/user/' . $userJid . '/role/?lftoken=' . $this->user('system')->token();
            $postData = array(
                'affiliation' => $type
            );
            if ($scope == 'domain') { 
                $postData['domain_wide'] = '1';
            } elseif ($scope == 'conv') {
                $postData['conv_id'] = $targetId;
            } elseif ($scope == 'site') {
                $postData['site_id'] = $targetId;
            }
            return $this->http->request( $requestURL, array('method'=>'POST', 'data'=>$postData) );
        }
        return false;
    }
    
    /**
     * Default Livefyre domain level cookie name
     * 
     * @return   string     Domain level cookie name
     */
    public function tokenCookieName() {
        return LF_COOKIE_PREFIX . 'token_' . $this->getHost();
    }
    
    /**
     * Default Livefyre display name cookie name
     * 
     * @return   string     Display name cookie name
     */
    public function dnameCookieName() {
        return LF_COOKIE_PREFIX . 'display_name_' . $this->getHost();
    }
    
    /**
     * Set a Livefyre user's cookie
     *
     * @param   string  Token to set
     * @param   string  Path of the cookie
     * @param   string  Cookie's domain
     * @param   string  Epoch time cookie will expire
     * @param   string  Sets cookie security level
     */
    public function setTokenCookie( $token, $cookiePath, $cookieDomain, $expire = null, $secure = false ) {
        $this->setCookie($this->tokenCookieName(), $token, $cookiePath, $cookieDomain, $expire, $secure = false);
    }
    
    public function setDisplayNameCookie( $displayName, $cookiePath, $cookieDomain, $expire = null, $secure = false ) {
        if ($expire == null) {
            $expire = time() + 1210000;
        }
        $this->setCookie($this->dnameCookieName(), $displayName, $cookiePath, $cookieDomain, $expire, $secure = false);
    }
    
    /**
     * Sets a Livefyre browser cookie
     *
     * @param   string  Name of the token
     * @param   string  Cookie value
     * @param   string  Path of the cookie
     * @param   string  Cookie's domain
     * @param   string  Epoch time cookie will expire
     * @param   string  Sets cookie security level
     */
    public function setCookie( $name, $value, $cookiePath, $cookieDomain, $expire = null, $secure = false ) {
        if ( $expire == null ) {
            $expire = time() + 86400;
        }
        setcookie( $name, $value, $expire, $cookiePath, $cookieDomain, $secure, false );
    }
    
    /**
     * Destroys a Livefyre session
     *
     * @param   string  Path of the cookie
     * @param   string  Cookie's domain
     */
    public function clearCookies( $cookiePath, $cookieDomain ) {
        setcookie( $this->dnameCookieName(), ' ', time() - 31536000, $cookiePath, $cookieDomain );
        setcookie( $this->tokenCookieName(), ' ', time() - 31536000, $cookiePath, $cookieDomain );
    }
    
    /**
     * Returns the Livefyre Version 1 Javascript librrary code
     * 
     * @return   string     Javascript library code
     */
    public function sourceJSV1() {
        return '<script type="text/javascript" src="http://zor.' . $this->getLivefyreTLD() . '/wjs/v1.0/javascripts/livefyre_init.js"></script>';
    }
    
    /**
     * Returns the Livefyre Version 3 Javascript librrary code
     * 
     * @return   string     Javascript library code
     */
    public function sourceJSV3() {
        return '<script type="text/javascript" src="http://zor.' . $this->getLivefyreTLD() . '/wjs/v3.0/javascripts/livefyre.js"></script>';
    }
    

    /**
     * Checks that login works for a particular cookie in V1
     *
     * @param   string  URL of the token
     * @param   string  Path of the cookie
     * @param   string  Token cookie's name
     * @param   string  Domain cookie's name
     */
    public function authenticateJS( $tokenURL = '', $cookiePath = '/', $tokenCookie = null, $dnameCookie = null  ) {
        
        /*
            This script should be rendered when it appears the user is logged in
            Now we attempt to fetch Livefyre credentials from a cookie,
            falling back to ajax as needed.
        */
        $tokenCookie = $tokenCookie ? $tokenCookie : $this->tokenCookieName();
        $dnameCookie = $dnameCookie ? $dnameCookie : $this->dnameCookieName();
        ?>
            <script type="text/javascript">
                LF.ready(function() {
                    var lfTokenCookie = '<?php echo $token_cookie; ?>';
                    var lfDnameCookie = '<?php echo $dname_cookie; ?>';
                    if (!$jl.cookie(lfTokenCookie)) {
                        <?php
                        if ( !empty($token_url) ) {
                            ?>
                            // fetch via ajax
                            $jl.ajax({
                                url: '<?php echo $token_url; ?>',
                                type: 'json',
                                success: function(json){
                                    LF.login(json);
                                    $jl.cookie(lfTokenCookie, json.token, {expires:1, path:'<?php echo $cookie_path ?>'});
                                    $jl.cookie(lfDnameCookie, json.profile.display_name, {expires:1, path:'<?php echo $cookie_path ?>'});
                                },
                                error: function(a, b){
                                    console.log("There was some problem fetching a livefyre token. ", a, b);
                                }
                            });
                            <?php
                        }
                        ?>
                    } else {
                        try {
                            LF.login({
                                token: $jl.cookie(lfTokenCookie),
                                profile:{
                                    display_name: $jl.cookie(lfDnameCookie)
                                }
                            });
                        } catch (e) {
                            console.log("Error attempting to login with ", lfTokenCookie, " cookie value: ", $jl.cookie(lfTokenCookie), " ", e);
                        }
                    }
                });
            </script>
        <?php
    
    }


    /**
     * Checks that login works for a particular cookie in V3
     *
     * @param   string  URL of the token
     * @param   string  Path of the cookie
     * @param   string  Token cookie's name
     * @param   string  Domain cookie's name
     */
    public function authenticateJSV3( $tokenURL = '', $cookiePath = '/', $tokenCookie = null, $dnameCookie = null  ) {
        
        /*
            This script should be rendered when it appears the user is logged in
            Now we attempt to fetch Livefyre credentials from a cookie,
            falling back to ajax as needed.
        */
        $tokenCookie = $tokenCookie ? $tokenCookie : $this->tokenCookieName();
        //$dname_cookie = $dname_cookie ? $dname_cookie : $this->dname_cookie_name();
        ?>
            <script type="text/javascript">
                if (document.location.href.indexOf('http://localhost') == 0) {
                    console.log('Livefyre needs at least one . (dot) in the domain name.  Therefore, localhost is considered invalid - try using 127.0.0.1 instead.');
                }
                // these are just utility methods for working with cookies
                function lfSetCookie(a,b,c){if(c){var d=new Date;d.setTime(d.getTime()+c*24*60*60*1e3);var e="; expires="+d.toGMTString()}else var e="";document.cookie=a+"="+b+e+"; path=/"}function lfGetCookie(a){var b=a+"=";var c=document.cookie.split(";");for(var d=0;d<c.length;d++){var e=c[d];while(e.charAt(0)==" ")e=e.substring(1,e.length);if(e.indexOf(b)==0)return e.substring(b.length,e.length)}return null}function lfDeleteCookie(a){lfSetCookie(a,"",-1)}
                
                function doLivefyreAuth() {
                    var lfTokenCookie = '<?php echo $token_cookie; ?>';
                    if (!lfGetCookie(lfTokenCookie)) {
                        <?php
                        if ( !empty($token_url) ) {
                            $sep = strpos($token_url, '?') === FALSE ? '?' : '&' ;
                            ?>
                            // fetch via JSONP
                            window.lfTokenCallback = function(json){
                                fyre.conv.login(json.token);
                                lfSetCookie(lfTokenCookie, json.token, 1);
                            };
                            var h = document.getElementsByTagName("head")[0];
                            var s = document.createElement('script');
                            s.type = 'text/javascript';
                            s.src = '<?php echo $token_url . $sep . "callback=lfTokenCallback"; ?>';
                            h.appendChild(s);
                            <?php
                        }
                        ?>
                    } else {
                        try {
                            fyre.conv.login(lfGetCookie(lfTokenCookie));
                        } catch (e) {
                            console.log("Error attempting to login with ", lfTokenCookie, " cookie value: ", lfGetCookie(lfTokenCookie), " ", e);
                        }
                    }
                }
            </script>
        <?php
    
    }

    /**
     * Accessor method to check the validity of the system token
     *
     * @param   string  Token to check
     */
    public function validateSystemToken($token) {
        // This replaces the below - it uses JWT to verify that the token is valid for user id = 'system'
        return lftokenValidateSystemToken($token, $this->getKey(), $this->getHost());
    }

    /**
     * Accessor method to check the validity of the server token
     *
     * @param   string  Token to check
     */
    public function validateServerToken($token) {
        return lftokenValidateServerToken($token, $this->getKey());
    }

    /**
     * Checks the the validity of a system token
     *
     * @param   string  Token to check
     * @param   string  Key to hash with
     * @param   string  Name of the domain
     * @return  bool    Result of token validity
     */
    private function lftokenValidateSystemToken($token, $key, $domain) {
        // This replaces the below - it uses JWT to verify that the token is valid for user id = 'system'
        $payload = JWT::decode($token, $key);
        $required = array('expires','user_id','domain');
        foreach ($required as $fieldName) {
            if ( !isset($payload->$fieldName) ) {
                return false;
            }
        }
        if ( $domain != $payload->domain || $payload->expires < time() || $payload->user_id != 'system' ) {
            return false;
        }
        return true;
    }

    /**
     * Checks the the validity of a system token
     *
     * @param   string  Token to check
     * @param   string  Key to hash with
     * @return  bool    Result of token validity
     */
    private function lftokenValidateServerToken($token, $key) {
        $parts = explode( ',', $token );
        $signature = array_pop( $parts );
        $serverkey = $this->hmacsha1( base64_decode( $key ), "Server Key" );
        $temp = base64_encode( $this->hmacsha1( $serverkey, implode( ',', $parts ) ) );
        if (count($parts) > 1) {
            $timestamp = strtotime($parts[1]);
            $duration = $parts[2];
        } else {
            $timestamp = time() - 1;
            $duration = 0;
        }
        return ( $signature == $temp ) && ( time() - $timestamp < $duration );
    }

    /**
     * Checks response to server token valididation
     *
     * @param   string  Payload data
     * @param   string  Response to check against
     * @param   string  Key to use for hashing
     * @deprecated 
     */
    function lftokenValidateResponse($data, $response, $key) {
        // This was a poorly chosen name, not a great interface.
        // Deprecated but here for backcompat
        return lftokenValidateServerToken($data . ',' . $response, $key);
    }
}

?>
