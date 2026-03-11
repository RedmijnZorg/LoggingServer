<?php
/**
Maakt verbindingen via cURL
**/
class ConnectionService
{

    protected $connection;

    public function __construct() {
        $this->connection = curl_init();
    }

    /**
     * @param bool $verifyHost
     * @return void
     */
    public function setVerifyHost(bool $verifyHost){
    	if($verifyHost) {
        		curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, 2);
    		} else {
        		curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, 0);
    	}
    }

    /**
     * @param bool $verifyPeer
     * @return void
     */
    public function setVerifyPeer(bool $verifyPeer){
    	if($verifyPeer) {
        		curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, 2);
    		} else {
        		curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, 0);
    	}
    }

    /**
     * Headers instellen
     *
     * @param $headers
     * @return bool
     */
    public function setHeaders(array $headers = array()) {
        $headersRebuilt = array();
        foreach ($headers as $key => $value) {
            $headersRebuilt[] = $key.": ".$value;
        }
        return curl_setopt($this->connection, CURLOPT_HTTPHEADER, $headersRebuilt);
    }

    /**
     * Een GET-verzoek uitvoeren
     *
     * @param string $url
     * @param array $params
     * @return bool|string
     */
    public function getRequest(string $url, array $params = array()) {
        if(count($params) > 0) {
            $url .= '?'.http_build_query($params);
        }
        curl_setopt($this->connection, CURLOPT_URL, $url);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, 'GET');

        return curl_exec($this->connection);
    }
    
    /**
     * Een POST-verzoek uitvoeren
     *
     * @param string $url
     * @param string data
     * @param array $params
     * @return bool|string
     */
    public function postRequest(string $url, string $data, array $params = array()) {
        if(count($params) > 0) {
            $url .= '?'.http_build_query($params);
        }
        curl_setopt($this->connection, CURLOPT_URL, $url);
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, 'POST');

        return curl_exec($this->connection);
    }

    /**
     * De laatste HTTP-code opvragen
     *
     * @return mixed
     */
    public function getHTTPcode() {
        return curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
    }
}