<?php

require_once dirname(__FILE__) . '/Domain.php';
require_once dirname(__FILE__) . '/Site.php';
require_once dirname(__FILE__) . '/Conversation.php';

/**
 * Livefyre Wrapper Class to help get widgets onto the page.
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre {

    /**
     * The top level class in a Livefyre Instance
     *
     * @var Domain
     */
    public $domain;

    /**
     * The class representing a user's site
     *
     * @var Site
     */
    public $site;

    /**
     * The class that holds collection data
     *
     * @var Conversation[]
     */
    public $convs;

    /**
     * The name of the onload function to call on widget loads
     *
     * @var string
     */
    public $onload;

    /**
     * The version of the embed code to use on the page
     *
     * @var int
     */
    public $version;


    /**
     * Class construction function.
     *
     * Builds the Livefyre Class. The only necessary information needed is the site configuration
     * array. Without the domain configuration, the basic livefyre.com network will be used. Also,
     * misconfigured information will be returned from function calls requiring conversation data.
     *
     * @param string[]      The array containing the configuration
     *                      for a Livefyre Domain.
     * @param string[]      The array containing the configuration
     *                      for a Livefyre Site.
     * @param string[][]    An array of arrays that represent the
     *                      configuration for various collections
     *                      within a page.
     * @param string        String representing the name of the 
     *                      Javascript variable that is called
     *                      on widget load
     * @param int           An int telling what version of the
     *                      widget to run
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct( $domain_config, $site_config, $conv_configs, $onload = null, $version = 3 ) {
        if ( isset( $site_config['site_id'] ) && isset( $site_config['site_key'] ) )  {
            $this->site = new Livefyre_Site( $site_config['site_id'], $site_config['site_key'] );
        }
        else {
            echo 'A site must be created with a valid Site ID and Site Key.';
            return;
        }
        if( isset( $domain_config['network'] ) && isset( $domain_config['key'] ) ) {
            $this->domain = new Livefyre_Domain( $domain_config['network'],  $domain_config['key'], $options = $domain_config['options'] );    
        }
        else {
            $this->domain = new Livefyre_Domain( 'livefyre.com', $options = $domain_config['options'] );
        }
        if ( isset( $conv_configs ) ) {
            $this->convs = array();
            foreach ( $conv_configs as $cur_conv ) {
                $conv = new Livefyre_Conversation(
                    $el = $cur_conv['el'],
                    $article_id = $cur_conv['article_identifier'],
                    $conv_name = $cur_conv['conv_name'],
                    $tags = $cur_conv['tags'],
                    $url = $cur_conv['url'],
                    $title = $cur_conv['title']
                );
                array_push( $this->convs, $conv );
            }
        }
        $this->onload = $onload;
        $this->version = $version;
    }

    /**
     * Setter for the domain variable
     * 
     * @param   Domain  The domain object to set
     */
    public function set_domain( $domain ) {
        $this->domain = $domain;
    }

    /**
     * Getter for the domain variable
     *
     * @return  Domain  The domain object contained in the Livefyre
     *                  class
     */
    public function get_domain() {
        return $this->domain;
    }

    /**
     * Setter for the site variable
     * 
     * @param   Site    The site object to set
     */
    public function set_site( $site ) {
        $this->site = $site;
    }

    /**
     * Getter for the domain variable
     *
     * @return  Site    The site object contained in the Livefyre
     *                  class
     */
    public function get_site() {
        return $this->site;
    }

    /**
     * Setter for the onload variable
     * 
     * @param   string  The onload string to set
     */
    public function set_onload( $onload ) {
        $this->onload = $onload;
    }

    /**
     * Getter for the domain variable
     *
     * @return  string  The string representing the Javascript
     *                  onload function name
     */
    public function get_onload() {
        return $this->onload;
    }

    /**
     * Adds a conversation to the list
     * 
     * @param   Conversation    The conversation to add to the list
     */
    public function add_conv( $conv ) {
        array_push( $this->convs, $conv );
    }

    /**
     * Get function to grab the conversation from the list
     * 
     * @param   string  The article identifier of the conversation
     *                  to grab from the list
     * @return  Conversation    A conversation that matches the article ID passed in
     */
    public function get_conv( $article_id ) {
        foreach ( $this->convs as $conv ) {
            if ( $conv->get_article_id() == $article_id ) {
                return $conv;
            }
        }
        return null;
    }

    /**
     * Creates a string representation of the Livefyre Widget's global
     * configuration
     * 
     * @return  string  The string representing global configuration
     *                  options for the Livefyre Widget
     */
    public function get_global_config() {

        $output = '{network: "' . $this->domain->get_host() . '"';
        $authDelegate = $this->domain->get_authDelegate();
        if ( isset( $authDelegate ) ) {
            $output .= ', authDelegate: ' . $authDelegate;
        }
        $strings = $this->domain->get_strings();
        if ( isset( $strings ) ) {
            $output .= ', strings: ' . $strings;
        }
        return $output . "}";

    }

    /**
     * Creates a string representation of the Javascript variables that represent
     * the Conversation class
     *
     * Each Conversation is represented by a Javascript variable that contains
     * vital Conversation data. This data includes the conversations meta data,
     * site information, article identifier, etc. This data is stored into a variable
     * that is referenced by Livefyre's load method.
     * 
     * @return  string  The string representing all conversations as javascript variables
     */
    public function declare_configs() {

        $output = '';
        foreach ( $this->convs as $conv ) {
            $output .= 'var ' . $conv->get_conv_name() . ' = ' . json_encode( $conv->get_conv_config( $this->site->get_key(), $this->site->get_id() ) );
        }
        return $output;

    }

    /**
     * Calls correct function to get the string representation of the Javascipt call to instantiate the widget
     *
     * Decides based on the version what widget we display to the page. To display a old Version 1
     * widget, the Livefyre class must know about the version. Everything else will be defaulted to
     * the most recent Livefyre Widget, Version 3.
     * 
     * @return  string  The string representing the javascipt loading code
     */
    public function livefyre_embed_code() {

        if ( $this->version < 3 ) {
            if ( !isset( $this->convs[0] ) ) {
                return "Unable to produce a V1 conversation. No conversations exist.";
            }
            return $this->livefyre_embed_code_v1( $this->convs[0] );
        }
        return $this->livefyre_embed_code_v3();

    }

    /**
     * Creates the string representing the Javescript load call for a Version 1 widget
     * 
     * @return  string  The string representing the javascript loading code for Version 1
     */
    public function livefyre_embed_code_v1( $conv ) {

        return $conv->to_initjs( $this->domain->get_host(), $this->site->get_id(), $this->site->get_key, 
            $user = null, $display_name = null, $backplane = false, $jquery_ready = false, $include_source = true
        );
    
    }

    /**
     * Creates the string representing the Javescript load call for a Version 3 widget
     * 
     * @return  string  The string representing the javascript loading code for Version 3
     */
    public function livefyre_embed_code_v3() {

        $output = '';
        $output .= 'fyre.conv.load(' . $this->get_global_config() . ', ' . $this->livefyre_conv_names();
        if ( isset( $this->onload ) ) {
            $output .= ', ' . $this->onload;
        }
        return $output . ');';

    }

    /**
     * Creates the string representing the conversations names to be used in the load call
     * 
     * @return  string  The string representation of a list of conversation names
     */
    public function livefyre_conv_names() {

        $output = '[';
        foreach ( $this->convs as $conv ) {
            $output .= $conv->get_conv_name() . ', ';
        }
        return substr($output, 0, ( strlen( $output ) - 2 ) ) . ']';

    }

    /**
     * Creates the user token for a given user
     *
     * @param   string  A string of a user id to be used in the token
     * @param   string  A string of the username to be displayed on the widget
     * @param   string  A string of the epoch time that the token should no longer
     *                  be valid
     * 
     * @return  string  The string representing the javascript loading code for Version 1
     */
    public function generate_user_token( $u_id, $u_display_name, $duration ) {
        
        $user = $this->domain->user( $u_id, $u_display_name );
        return $user->token( $duration );

    }

    /**
     * Fetches the HTML representation of the conversation
     *
     * Used for page caching reasons and load performance.
     *
     * @param   string  The site id of the conversation HTML requested
     * @param   string  The article id of the conversation HTML requested
     * 
     * @return  string  The HTML code representing the conversation
     */
    public function generate_bootstrap_html( $site_id, $article_id ) {
        if( !isset( $site_id ) ) {
            $site_id = $this->site->get_id();
        }
        // Check for null
        $conv = $this->get_conv( $article_id );
        if( !isset( $conv ) ) {
            return 'Cannot find conversation with that article ID.';
        }    
        return $conv->to_html( $this->domain->get_host(), $site_id );
    }

}

?>