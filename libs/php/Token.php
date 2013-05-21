<?php

define('LFTOKEN_MAX_AGE', 86400);
require_once dirname(__FILE__) . '/JWT.php' ;

class Livefyre_Token {

    /**
     * Creates a token for the given Livefyre User
     * 
     * @param   User    User to create the token for
     * @param   int     Maximum age for the token's life span    
     * @return  string  JWT encoded Livefyre token
     */
    static function from_user($user, $max_age=LFTOKEN_MAX_AGE) {
        $secret = $user->get_domain()->get_key();
        $args = array(
            'domain' => $user->get_domain()->get_host(), 
            'user_id' => $user->get_uid(),
            'expires' => time() + $max_age
        );
        $dname = $user->get_display_name();
        if (!empty($dname)) {
            $args['display_name'] = $dname;
        }
        return JWT::encode($args, $secret);
    }

    /**
     * Convert binary hash to BASE64 string
     * 
     * @param   string  Key to hash with
     * @param   string  Data to hash    
     * @return  string  Base64 string of hashed data
     */
    function getHmacsha1Signature($key, $data) {
        return base64_encode($this->hmacsha1($key, $data));
    }

    /**
     * Encrypt a base string w/ HMAC-SHA1 algorithm
     * 
     * @param   string  The key to hash with
     * @param   string  The data to hash    
     * @return  string  HMAC-SHA1'ed string
     */
    function hmacsha1($key,$data) {
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize) {
            $key=pack('H*', $hashfunc($key));
        }
        $key=str_pad($key,$blocksize,chr(0x00));
        $ipad=str_repeat(chr(0x36),$blocksize);
        $opad=str_repeat(chr(0x5c),$blocksize);
        $hmac = pack( 'H*',$hashfunc( ($key^$opad).pack( 'H*',$hashfunc( ($key^$ipad).$data ) ) ) );
        return $hmac;
    }

    /**
     * XORs the 2 arrays
     * 
     * @param   array   User to create the token for
     * @param   array   Maximum age for the token's life span    
     * @return  string  List of results of the XOR
     */
    private function xor_these($first, $second) {
        $results=array();
        for ($i=0; $i < strlen($first); $i++) {
            array_push($results, $first[$i]^$second[$i]);
        }
        return implode($results);
    }

    /**
     * Checks for commas
     * 
     * @param   string  String to check for commas  
     * @return  bool    Whether or not the strings has commas
     */
    function Livefyre_hasNoComma($str) {
        return !preg_match('/\,/', $str);
    }

    /**
     * Create the right data input for Livefyre authorization
     * 
     * @param   string  Current time
     * @param   string  Token's duration
     * @param   array   List of token args
     * @return  string  Livefyre token data
     */
    function lftokenCreateData($now, $duration, $args=array()) {
        $filtered_args = array_filter($args, 'Livefyre_hasNoComma');
        if (count($filtered_args)==0 or count($args)>count($filtered_args)) {
            return -1;
        }

        array_unshift($filtered_args, "lftoken", $now, $duration);
        $data=implode(',',$filtered_args);
        return $data;
    }

    /**
     * Create a signed token from data
     * 
     * @param   string  Data to wrap up in the token
     * @param   string  Key to hash with
     * @return  string  Livefyre user token ecoded with base64
     */
    function lftokenCreateToken($data, $key) {
        //Create a signed token from data
        $clientkey = $this->hmacsha1($key,"Client Key");
        $clientkey_sha1 = sha1($clientkey, true);
        $temp = $this->hmacsha1($clientkey_sha1,$data);
        $sig = $this->xor_these($temp,$clientkey);
        $base64sig = base64_encode($sig);
        return base64_encode(implode(",",array($data,$base64sig)));
    }
}

?>
