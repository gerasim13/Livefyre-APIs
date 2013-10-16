<?php

require_once dirname(__FILE__) . '/Token.php';

/**
 * Livefyre Class representing a Livefyre User
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_User {

    /**
     * Unique user ID
     *
     * @var string
     */
    private $uId;

    /**
     * Domain user is under
     *
     * @var string
     */
    private $domain;

    /**
     * User's display name
     *
     * @var string
     */
    private $displayName;
    
    /**
     * @param string    user identifier
     * @param string    domain user is under
     * @param string    user's display_name
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct($uId, $domain, $displayName = null) {
        $this->uid = $uid;
        $this->domain = $domain;
        $this->displayName = $displayName;
    }
    
    /**
     * Getter for the Livefyre User ID
     * 
     * @return  string  Livefyre User ID
     */
    public function getUId() { 
        return $this->uid;
    }

    /**
     * Getter for the Livefyre Domain this user is associated with
     * 
     * @return  string  Livefyre User's Domain
     */
    public function getDomain() {
        return $this->domain;
    }

    /**
     * Getter for the Livefyre User's display name
     * 
     * @return  string  Livefyre User's display name
     */
    public function getDisplayName() {
        return $this->displayName;
    }
    
    /**
     * Getter for the Livefyre User's JID
     * 
     * @return  string  Livefyre User's JID
     */
    public function jid() {
        return $this->getUId() . '@' . $this->domain->getHost();
    }
    
    /**
     * Builds a Livefyre User token
     * 
     * @param   int     duration the token will survive
     * @return  string  Livefyre User Token
     */
    public function token( $maxAge = 86400 ) {
        $domainKey = $this->domain->getKey();
        assert('$domainKey != null /* Domain key is necessary to generate token */');
        return Livefyre_Token::fromUser($this, $maxAge);
    }
    
    /**
     * Builds the JSON encoded authentication object
     * 
     * @param   int     duration the token will survive
     * @return  string  Livefyre Site Identifier
     */
    public function auth_JSON( $maxAge = 86400 ) {
        return json_encode( 
            array(
                "token" => $this->token( $maxAge ),
                "profile" => array(
                    "display_name" => $this->getDisplayName()
                )
            )
        );
    }
    
    /**
     * Pushes user data to our backend
     *
     * Used for profile updates in our Ping for Pull profile update.
     * 
     * @param   string  Data to be pushed to our backed
     * @return  string[]    Body of the http response
     */
    public function push( $userData ) {
        $post_data = array( 'data' => json_encode( $userData ) );
        $token_base64 = $this->token();
        $domain = $this->getDomain( );
        $remoteURL = "http://{$domain->get_host()}/profiles/?actor_token={$token_base64}&id={$user_data['id']}";
        $result = $domain->http->request($remoteURL, array('method' => 'POST', 'data' => $postData));
        if (is_array( $result ) && isset($result['response']) && $result['response']['code'] == 200) {
            return $result['body'];
        } else {
            return false;
        }
    }
}

?>
