<?php

require_once dirname(__FILE__) . '/JWT.php';

/**
 * Livefyre Class representing a Livefyre Converstaion
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_Conversation {

    /**
     * Livefyre Conversation Identifier
     *
     * @var string
     */
    private $article_id;

    /**
     * Livefyre Conversation variable name
     *
     * @var string
     */
    private $conv_name;

    /**
     * Livefyre Article title
     *
     * @var string
     */
    private $title;

    /**
     * Livefyre Article tags
     *
     * @var string
     */
    private $tags;
    

    /**
     * @param string    site identifier
     * @param string    site hash key
     * @param string    site hash key
     * @param string    site hash key
     * @param string    site hash key
     * @param string    site hash key
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct( $el, $article_id, $conv_name, $tags = null, $url, $title ) {
        $this->el = $el;
        $this->article_id = $article_id;
        $this->conv_name = $conv_name;
        $this->tags = $tags;
        $this->title = $title;
        if ( defined('LF_DEFAULT_HTTP_LIBRARY') ) {
            $httplib = LF_DEFAULT_HTTP_LIBRARY;
            $this->http = new $httplib;
        }
        else {
            require_once dirname(__FILE__) . '/Http.php';
            $this->http = new Livefyre_http; 
        }
    }

    /**
     * Getter for the Livefyre Conversation Identifier
     * 
     * @return  string     Livefyre Conversation Identifier
     */
    public function get_article_id() {
        return $this->article_id;
    }

    /**
     * Setter for Conversation Identifier
     * 
     * @param   string  Id to set Conversation Identifier to
     */
    public function set_article_id( $id ) {
        $this->id = $id;
    }
    
    /**
     * Getter for the Livefyre Site Identifier
     * 
     * @return  string     Livefyre Site Identifier
     */
    public function get_conv_name() {
        return $this->conv_name;
    }

    /**
     * Setter for Conversation name
     * 
     * @param   string  Conversation name to set
     */
    public function set_conv_name( $conv_name ) {
        $this->conv_name = $conv_name;
    }

    /**
     * Getter for the display element
     *
     * The HTML div for Livefyre to attach to. This is where the 
     * widget will show up on the page.
     * Example: 
     *  element:    livefyre
     *  page div:   <div>livefyre</div>
     * 
     * @return  string     Livefyre element
     */
    public function get_element() {
        return $this->el;
    }

    /**
     * Setter for the display element
     * 
     * The HTML div for Livefyre to attach to. This is where the 
     * widget will show up on the page.
     * Example: 
     *  element:    livefyre
     *  page div:   <div>livefyre</div>
     *
     * @param   string  Element to set Conversation element to
     */
    public function set_elememt( $element ) {
        $this->el = $element;
    }

    /**
     * Getter for the Livefyre Article Title
     * 
     * @return  string     Livefyre Article Title
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Setter for Livefyre Article Title
     * 
     * @param   string  Title to set the Conversation to
     */
    public function set_title( $title ) {
        $this->title = $title;
    }

    /**
     * Getter for the Livefyre Article tags
     * 
     * @return  string[]   Livefyre Article tags
     */
    public function get_tags() {
        return $this->tags;
    }

    /**
     * Setter for Site Identifier
     * 
     * @param   string[]    Array to set the tags to
     */
    public function set_tags( $tags ) {
        $this->tags = $tags;
    }

    /**
     * Sets the Javascript AuthDelegate variable for authentication
     * 
     * @param   string  Name of the Javascipt variable containing
     *                  the code to run authentication
     * @param   string  Code snippet that handles authentication    
     */
    public function add_js_delegate( $delegate_name, $code ) {
        $this->delegates[ $delegate_name ] = $code;
    }
    
    /**
     * Old way of handling autheDelegates for Version 1
     * Deprecated
     * 
     * @deprecated
     * @param   string[]    Array to set the tags to
     */
    public function render_js_delegates() {
        $str_out = '';
        if ( $this->delegates ) {
            $str_out = "var livefyreConvDelegates = {\n";
            foreach ($this->delegates as $handler => $code) {
                $str_out .= "    handle_$handler: " . $code . ", \n";
            }
            $str_out .= "}\nLF.ready( function() { LF.Dispatcher.addListener(livefyreConvDelegates); } )";
        }
        return $str_out;
    }

    /**
     * Outputs the necessary code to generate a Livefyre widget on the page.
     * Should be broken up more, but this version has been deprecated.
     * 
     * @deprecated
     * @param   string  Domain/Network name
     * @param   string  Site ID
     * @param   string  Site Key
     * @param   User    Livefyre user
     * @param   string  Livefyre user display name
     * @param   string  Use backplane flag
     * @param   string  jQuery loaded on the page flag
     */
    public function to_initjs( $host, $site_id, $site_key, $user = null, $display_name = null, $backplane = false, $jquery_ready = false) {
        /*
            **DEPRECATED**
            Please use to_initjs_v3() if you are on Livefyre comments V3
        */
        $network_name = $host;
        $site_id = $site_id;
        $site_key = $site_key;
        $config = array(
            'site_id' => $site_id,
            'article_id' => $this->article_id
        );
        $builds_token = true;
        if ( $network_name != LF_DEFAULT_PROFILE_DOMAIN ) {
            $config[ 'domain' ] = $network_name;
        } else {
            // nobody but Livefyre can build tokens for livefyre.com profiles
            $builds_token = false;
        }
        $article_url = $this->url;
        $article_title = $this->title;
        if ( !empty($site_key) && !empty($article_url) && !empty($article_title) ) {
            // Produce a conv meta checksum if we have enough data
            $sig_fields = array($config['article_id'], $article_url, $article_title, $site_key);
            $config['conv_meta'] = array(
                'article_url' => $article_url,
                'title' => $article_title,
                'sig' => md5(implode(',', $sig_fields))
            );
        }
        if ( $backplane ) {
            $add_backplane = 'if ( typeof(Backplane) != \'undefined\' ) { lf_config.backplane = Backplane; };';
        } else {
            $add_backplane = '';
        }
        $login_js = '';
        if ( $user && $builds_token ) {
            $login_json = array( 'token' => $user->token( ), 'profile' => array('display_name' => $display_name) );
            $login_json_str = json_encode( $login_json );
            $login_js = "LF.ready( function() {LF.login($login_json_str);} );";
        }
        return '' . ($jquery_ready ? 'jQuery(function(){' : '') . '
                var lf_config = ' . json_encode( $config ) . ';
                ' . $add_backplane . '
                var conv = LF(lf_config);
                ' . $login_js . '
                ' . $this->render_js_delegates() . '
                ' . ($jquery_ready ? '});' : '') . '
            ';
    }
    
    /**
     * Builds the Livefyre Collection Meta object used to store collection data
     * 
     * @param   string  Site key
     * @return  string  JWT string representing the conversation data
     */
    public function collection_meta( $site_key ) {

        $collectionMeta = array("title" => $this->title,
                "url" => $this->url,
                "tags" => $this->tags
            );
        $jwtString = JWT::encode( $collectionMeta, $site_key );
        return $jwtString;
    }

    /**
     * Builds the Javascript information for the applications fyre.conv.load method
     * 
     * Contains all the information to load a site into the Livefyre application
     *
     * @param   string  Site key
     * @param   string  Site ID
     * @return  string  Array of collection information for Conversation loading
     */
    public function get_conv_config( $site_key, $site_id ) {

        $collectionMeta = $this->collection_meta( $site_key );
        return array(
            'collectionMeta' => $collectionMeta,
            'checksum' => md5( $collectionMeta ),
            'siteId' => $site_id,
            'articleId' => $this->article_id,
            'el' => $this->el
        );
    }
    
    /**
     * Grabs HTML version of the widget for caching and speed purposes
     * 
     * Contains all the information to load a site into the Livefyre application. This is used
     * to cache a version of the plugin for better loading times. The stream will appear on the
     * page statically and then, when the Javascript loads, will become live.
     *
     * @param   string  Site key
     * @param   string  Site ID
     * @return  string  HTML representation of the Conversation
     */
    public function to_html( $host, $site_id ) {

        $article_id_b64 = urlencode( base64_encode( $this->article_id ) );
        $url = "http://bootstrap.$host/api/v1.1/public/bootstrap/html/$site_id/$article_id_b64.html";
        $result = $this->http->request($url, array('method' => 'GET'));
        if (is_array( $result ) && isset($result['response']) && $result['response']['code'] == 200) {
            return $result['body'];
        } else {
            return false;
        }
    }
}

?>
