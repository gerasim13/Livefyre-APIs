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
    private $_id;

    /**
     * Livefyre Site Key
     *
     * @var string
     */
    private $_key;
    
    /**
     * @param string    Site identifier
     * @param string    Site hash key
     *
     * @access public
     * @since Method available since Release 2.0.0
     */
    public function __construct($id, $key) {
        $this->_id = $id;
        $this->_key = $key;
    }
    
    /**
     * Getter for the Livefyre Site Identifier
     * 
     * @return  string  Livefyre Site Identifier
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Setter for Site Identifier
     * 
     * @param   string  Id to set Site Identifier to
     */
    public function setId( $id ) {
        $this->_id = $id;
    }
    
    /**
     * Getter for the Livefyre Site Key
     * 
     * @return  string  Livefyre Site key
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     * Setter for the Site Key
     * 
     * @param   string  Key to set the Site Key to
     */
    public function setKey( $key ) {
        $this->_key = $key;
    }
}

?>