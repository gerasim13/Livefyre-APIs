<?php

/**
 * Livefyre Class representing a Livefyre Site
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_Site {

    /**
     * Livefyre Site Identifier
     *
     * @var string
     */
    private $id;

    /**
     * Livefyre Site Key
     *
     * @var string
     */
    private $key;
    
    /**
     * @param string    Site identifier
     * @param string    Site hash key
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct($id, $key) {
        $this->id = $id;
        $this->key = $key;
    }
    
    /**
     * Getter for the Livefyre Site Identifier
     * 
     * @return  string  Livefyre Site Identifier
     */
    public function get_id() {
        return $this->id;
    }

    /**
     * Setter for Site Identifier
     * 
     * @param   string  Id to set Site Identifier to
     */
    public function set_id( $id ) {
        $this->id = $id;
    }
    
    /**
     * Getter for the Livefyre Site Key
     * 
     * @return  string  Livefyre Site key
     */
    public function get_key() {
        return $this->key;
    }

    /**
     * Setter for the Site Key
     * 
     * @param   string  Key to set the Site Key to
     */
    public function set_key( $key ) {
        $this->key = $key;
    }
}

?>