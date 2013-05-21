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
    private $uid;

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
    private $display_name;
    
    /**
     * @param string    user identifier
     * @param string    domain user is under
     * @param string    user's display_name
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct($uid, $domain, $display_name = null) {
        $this->uid = $uid;
        $this->domain = $domain;
        $this->display_name = $display_name;
    }
    
    /**
     * Getter for the Livefyre User ID
     * 
     * @return  string  Livefyre User ID
     */
    public function get_uid() { 
        return $this->uid;
    }

    /**
     * Getter for the Livefyre Domain this user is associated with
     * 
     * @return  string  Livefyre User's Domain
     */
    public function get_domain() {
        return $this->domain;
    }

    /**
     * Getter for the Livefyre User's display name
     * 
     * @return  string  Livefyre User's display name
     */
    public function get_display_name() {
        return $this->display_name;
    }
    
    /**
     * Getter for the Livefyre User's JID
     * 
     * @return  string  Livefyre User's JID
     */
    public function jid() {
        return $this->$uid.'@'.$this->domain->get_host();
    }
    
    /**
     * Builds a Livefyre User token
     * 
     * @param   int     duration the token will survive
     * @return  string  Livefyre User Token
     */
    public function token( $max_age = 86400 ) {
        $domain_key = $this->domain->get_key();
        assert('$domain_key != null /* Domain key is necessary to generate token */');
        return Livefyre_Token::from_user($this, $max_age);
    }
    
    /**
     * Builds the JSON encoded authentication object
     * 
     * @param   int     duration the token will survive
     * @return  string  Livefyre Site Identifier
     */
    public function auth_json( $max_age = 86400 ) {
        return json_encode( 
            array(
                "token" => $this->token( $max_age ),
                "profile" => array(
                    "display_name" => $this->get_display_name()
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
    public function push( $user_data ) {
        $post_data = array( 'data' => json_encode( $user_data ) );
        $token_base64 = $this->token();
        $domain = $this->get_domain( );
        $remote_url = "http://{$domain->get_host()}/profiles/?actor_token={$token_base64}&id={$user_data['id']}";
        $result = $domain->http->request($remote_url, array('method' => 'POST', 'data' => $post_data));
        if (is_array( $result ) && isset($result['response']) && $result['response']['code'] == 200) {
            return $result['body'];
        } else {
            return false;
        }
    }
}

?>
