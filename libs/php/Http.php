<?php

/**
 * Livefyre Class to wrap http calls
 *
 * @author     Livefyre Inc <a href="http://www.livefyre.com">Livefyre</a>
 * @author     Mike Soldner, Derek Chinn
 */
class Livefyre_http {

    /**
     * Builds a Livefyre Http class
     */
    public function __construct() {
        $this->defaultContentType = 'application/x-www-form-urlencoded';
    }

    /**
     * Decides which http call should be made
     * 
     * @param   string      URL to make the request
     * @param   string[]    Arguments to pass to the request
     * @return  string      The return information from the request
     */
    public function request($url, $args = array()) {
        /* valid $args members (all optional):
            method: HTTP method
            data: associative array of "form" data
            timeout: time to wait for a response, in seconds
        */
        if ( !isset($args['method']) ) {
            $args['method'] = isset($args['data']) ? 'POST' : 'GET';
        }
        $result = array( 'response' => false,
                         'body' => false);
        $methodName = $this->hasCurl() ? 'curl_request' : 'gfc_request';
        return $this->$methodName($url, $args, $result);
    }

    /**
     * Check to see if curl is defined before using it
     * 
     * @return  bool    Whether curl is defined or not
     */
    private function hasCurl() {
        return function_exists('curl_init');
    }

    /**
     * Uses curl to make HTTP request 
     *
     * @param   string      URL to ping
     * @param   string[]    Arguments to pass on to the request
     * @param   string[]    Array to add results to
     * @return  string[]    HTTP results array
     */
    private function curlRequest($url, $args = array(), &$result) {
        if ( ! isset( $args[ 'timeout' ] ) ) {
            $args[ 'timeout' ] = 5;
        }
        $ch = curl_init($url); 
        if ( $args['method'] == 'POST' ) {
            curl_setopt_array($ch, array(
                CURLOPT_POST            => 1,
                CURLOPT_POSTFIELDS      => http_build_query($args['data']),
                CURLOPT_HTTPHEADER      => array("Content-Type: $this->default_content_type")
            ));
        }
        curl_setopt_array($ch, array(
            CURLOPT_TIMEOUT         => $args[ 'timeout' ],
            CURLOPT_RETURNTRANSFER  => true
        ));
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $result['response'] = array( 'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE) );
        $result['body'] = $response;
        return $result;
    }

    /**
     * Uses gfc to make HTTP request 
     *
     * @param   string      URL to ping
     * @param   string[]    Arguments to pass on to the request
     * @param   string[]    Array to add results to
     * @return  string[]    HTTP results array
     */
    private function gfcRequest($url, $args = array(), &$result) {
        if ( $args['method'] == 'POST' ) {
            $dataURL = http_build_query($args['data']);
            $dataLen = strlen($dataURL);
            $result['body'] = file_get_contents(
                $url, false, 
                stream_context_create(
                    array(
                        'http'=>array(
                            'method'=>'POST',
                            'header'=>"Connection: close\r\nContent-Length: $dataLen\r\nContent-Type: $this->defaultContentType\r\n",
                            'content'=>$dataURL
                        )
                    )
                )
            );
        } else {
            $result['body'] = file_get_contents($url);
        }
        // we don't have a resp code, so lets fake it!
        $resultCode = $result['body'] ? 200 : 500;
        $result['response'] =  array( 'code' => $resultCode );
        return $result;
    }

}

?>
