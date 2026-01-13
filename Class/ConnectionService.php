<?php

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
    public function setVerifyHost($verifyHost){
        curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, $verifyHost);
    }

    /**
     * @param bool $verifyPeer
     * @return bool
     */
    public function setVerifyPeer($verifyPeer){
        return curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, $verifyPeer);
    }

    /**
     * @param $headers
     * @return bool
     */
    public function setHeaders($headers = array()) {
        $headersRebuilt = array();
        foreach ($headers as $key => $value) {
            $headersRebuilt[] = $key.": ".$value;
        }
        return curl_setopt($this->connection, CURLOPT_HTTPHEADER, $headersRebuilt);
    }

    /**
     * @param string $url
     * @param array $params
     * @return bool|string
     */
    public function getRequest($url, $params = array()) {
        if(count($params) > 0) {
            $url .= '?'.http_build_query($params);
        }
        curl_setopt($this->connection, CURLOPT_URL, $url);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->connection, CURLOPT_CUSTOMREQUEST, 'GET');

        return curl_exec($this->connection);
    }
    
    /**
     * @param string $url
     * @param string data
     * @param array $params
     * @return bool|string
     */
    public function postRequest($url, $data, $params = array()) {
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
     * @return mixed
     */
    public function getHTTPcode() {
        return curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
    }
}