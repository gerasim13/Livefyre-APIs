<?php

require_once dirname(__FILE__) . '/JWT.php';

/**
 * Livefyre Class representing a Livefyre Converstaion
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_Conversation 
{

    /**
     * Livefyre Conversation Identifier
     *
     * @var string
     */
    private $_articleId;

    /**
     * Livefyre Conversation variable name
     *
     * @var string
     */
    private $_convName;

    /**
     * Livefyre Article title
     *
     * @var string
     */
    private $_title;

    /**
     * Livefyre Article tags
     *
     * @var array
     */
    private $_tags;

    /**
     * Livefyre Target Element
     *
     * @var string
     */
    private $_el;

    /**
     * Livefyre Source URL
     *
     * @var string
     */
    private $_url;
    

    /**
     * @param   string  Livefyre target element
     * @param   string  Unique article ID
     * @param   string  Name of the Covnersation JS variable
     * @param   array   Article tags
     * @param   string  Source URL of the Conversation
     * @param   string  Article title
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct( $el, $articleId, $convName, 
        $tags = null, $url, $title
    ) {
        $this->setElememt($el);
        $this->setArticleId($articlIid);
        $this->setConvName($convName);
        $this->setTags($tags);
        $this->setURL($url);
        $this->setTitle($title);
        if ( defined('LF_DEFAULT_HTTP_LIBRARY') ) {
            $httplib = LF_DEFAULT_HTTP_LIBRARY;
            $this->http = new $httplib;
        } else {
            require_once dirname(__FILE__) . '/Http.php';
            $this->http = new Livefyre_http; 
        }
    }

    /**
     * Wrapper for deprecated method
     * 
     * @deprecated
     */
    public function get_article_id()
    {
        return $this->getArticleId();
    }

    /**
     * Getter for Livefyre Article Identifier
     * 
     * @return  string  Livefyre Article Identifier
     */
    public function getArticleId()
    {
        return $this->_articleId;
    }

    /**
     * Setter for Conversation Identifier
     * 
     * @param   string  Id to set Conversation Identifier to
     */
    public function setURL( $url )
    {
        $this->_url = $id;
    }

    /**
     * Getter for Livefyre Article Identifier
     * 
     * @return  string  Livefyre Article Identifier
     */
    public function getURL()
    {
        return $this->_url;
    }

    /**
     * Setter for Conversation Identifier
     * 
     * @param   string  Id to set Conversation Identifier to
     */
    public function setArticleId( $id )
    {
        $this->_id = $id;
    }

    /**
     * Getter for Conversation Identifier
     * 
     * @return  string  Livefyre Conversation Identifier
     */
    public function getArticleId( $id )
    {
        return $this->_id;
    }
    
    /**
     * Getter for the Conversation Name
     * 
     * @return  string  Conversation Name
     */
    public function getConvName()
    {
        return $this->_convName;
    }

    /**
     * Setter for Conversation name
     * 
     * @param   string  Conversation name to set
     */
    public function setConvName( $convName )
    {
        $this->_convName = $convName;
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
    public function getElement()
    {
        return $this->_el;
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
    public function setElememt( $element )
    {
        $this->_el = $element;
    }

    /**
     * Getter for the Livefyre Article Title
     * 
     * @return  string     Livefyre Article Title
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * Setter for Livefyre Article Title
     * 
     * @param   string  Title to set the Conversation to
     */
    public function setTitle( $title )
    {
        $this->_title = $title;
    }

    /**
     * Getter for the Livefyre Article tags
     * 
     * @return  string[]   Livefyre Article tags
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * Setter for Livefyre Article tags
     * 
     * @param   string[]    Array to set the tags to
     */
    public function setTags( $tags )
    {
        if (!is_array($tags)) {
            return "Tags must be an array of strings";
        }
        $this->_tags = $tags;
    }

    /**
     * Sets the Javascript AuthDelegate variable for authentication
     * 
     * @param   string  Name of the Javascipt variable containing
     *                  the code to run authentication
     * @param   string  Code snippet that handles authentication    
     */
    public function addJSDelegate( $delegateName, $code )
    {
        $this->delegates[ $delegateName ] = $code;
    }

    /**
     * Wrapper for out of date method
     *
     * @deprecated
     */
    public function add_js_delegate( $delegate_name="authDelegate", $code )
    {
        $this->addJSDelegate( $delegate_name, $code );
    }
    
    /**
     * Old way of handling authDelegates for Version 1
     * Deprecated
     * 
     * @deprecated
     * @param   string[]    Array to set the tags to
     */
    public function renderJSDelegates()
    {
        $strOut = '';
        if ( $this->delegates ) {
            $str_out = "var livefyreConvDelegates = {\n";
            foreach ($this->delegates as $handler => $code) {
                $strOut .= "    handle_$handler: " . $code . ", \n";
            }
            $strOut .= "}\nLF.ready( function() 
                { LF.Dispatcher.addListener(livefyreConvDelegates); } )";
        }
        return $strOut;
    }

    /**
     * Wrapper for out of date method
     *
     * @deprecated
     */
    public function render_js_delegate()
    {
        return $this->renderJSDelegate();
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
    public function toInitJS( $host, $siteId, $siteKey, $user = null, 
        $displayName = null, $backplane = false, $jqueryReady = false
    ) {
        /*
            **DEPRECATED**
            Please use toInitJSv3() if you are on Livefyre comments V3
        */
        $networkName = $host;
        $site_id = $siteId;
        $site_key = $siteKey;
        $config = array(
            'site_id' => $siteId,
            'article_id' => $this->getArticleId()
        );
        $buildsToken = true;
        if ( $networkName != LF_DEFAULT_PROFILE_DOMAIN ) {
            $config[ 'domain' ] = $networkName;
        } else {
            // nobody but Livefyre can build tokens for livefyre.com profiles
            $buildsToken = false;
        }
        $articleURL = $this->getURL();
        $articleTitle = $this->getTitle();
        if ( !empty($siteKey) && !empty($articleURL) && !empty($articleTitle) ) {
            // Produce a conv meta checksum if we have enough data
            $sigFields = array(
                $config['articleId'],
                $articleURL,
                $articleTitle,
                $siteKey
            );
            $config['conv_meta'] = array(
                'article_url' => $articleURL,
                'title' => $articleTitle,
                'sig' => md5(implode(',', $sigFields))
            );
        }
        if ( $backplane ) {
            $addBackplane = 'if ( typeof(Backplane) != \'undefined\' ) 
                { lf_config.backplane = Backplane; };';
        } else {
            $addBackplane = '';
        }
        $loginJS = '';
        if ( $user && $buildsToken ) {
            $loginJSON = array( 
                'token' => $user->token( ), 
                'profile' => array(
                    'display_name' => $displayName
                )
            );
            $loginJSONStr = json_encode( $loginJSON );
            $loginJS = "LF.ready( function() {LF.login($login_json_str);} );";
        }
        return '' . ($jqueryReady ? 'jQuery(function(){' : '') . '
                var lf_config = ' . json_encode( $config ) . ';
                ' . $addBackplane . '
                var conv = LF(lf_config);
                ' . $loginJS . '
                ' . $this->renderJSDelegates() . '
                ' . ($jqueryReady ? '});' : '') . '
            ';
    }

    /**
     * Wrapper for out of date method
     *
     * @deprecated
     */
    public function to_initjs( $user = null, $display_name = null, $backplane = false, $jquery_ready = false, $include_source = true )
    {
        return $this->toInitJS( $user = null, $display_name = null, $backplane = false, $jquery_ready = false, $include_source = true );

    }
    
    /**
     * Builds the Livefyre Collection Meta object used to store collection data
     * 
     * @param   string  Site key
     * @return  string  JWT string representing the conversation data
     */
    public function collectionMeta( $siteKey )
    {

        $collectionMeta = array("title" => $this->getTitle(),
                "url" => $this->getURL(),
                "tags" => implode(",", $this->getTags())
            );
        $jwtString = JWT::encode( $collectionMeta, $siteKey );
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
    public function getConvConfig( $siteKey, $siteId )
    {

        $collectionMeta = $this->collectionMeta( $siteKey );
        return array(
            'collectionMeta' => $collectionMeta,
            'checksum' => md5( $collectionMeta ),
            'siteId' => $siteId,
            'articleId' => $this->_articleId,
            'el' => $this->_el
        );
    }
    
    /**
     * Grabs HTML version of the widget for caching and speed purposes
     * 
     * Contains all the information to load a site into the Livefyre 
     * application. This is used to cache a version of the plugin for better
     * loading times. The stream will appear on the
     * page statically and then, when the Javascript loads, will become live.
     *
     * @param   string  Site key
     * @param   string  Site ID
     * @return  string  HTML representation of the Conversation
     */
    public function toHTML( $host, $siteId )
    {

        $articleIdB64 = urlencode( base64_encode( $this->_articleId ) );
        $url = "http://bootstrap.$host
            /api/v1.1/public/bootstrap/html/$site_id/$article_id_b64.html";
        $result = $this->http->request($url, array('method' => 'GET'));
        if (is_array( $result )
            && isset($result['response'])
            && $result['response']['code'] == 200) {
            return $result['body'];
        } else {
            return false;
        }
    }
}

?>
