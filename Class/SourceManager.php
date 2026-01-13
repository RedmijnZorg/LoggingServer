<?php

class SourceManager
{

    private $database;

    /**
     * @param mysqli $database
     */
    public function __construct($database) {
        $this->database = $database;
    }

    /**
     * @param string $name
     * @return false|int
     */
    public function addSource($name) {
        $name = $this->database->real_escape_string($name);
        $cryptoService = new CryptoService();
        $token = $cryptoService->generateUUID();

        $addsource = $this->database->query("INSERT INTO sources (name, token) VALUES ('$name', '$token')");
        if ($addsource) {
            return $this->database->insert_id;
        } else {
            return false;
        }
    }

    /**
     * @param int $sourceid
     * @return array|false
     */
    public function getSourceDetails($sourceid) {
        $sourceid = $this->database->real_escape_string($sourceid);
        $getsource = $this->database->query("SELECT * FROM sources WHERE sourceid = '$sourceid'");
        if($getsource->num_rows != 0) {
            $sourcedetails = $getsource->fetch_assoc();
            return $sourcedetails;
        } else {
            return false;
        }
    }

    /**
     * @param $token
     * @return array|false
     */
    public function getSourceByToken($token) {
        $token = $this->database->real_escape_string($token);
        $getsource = $this->database->query("SELECT `sourceid` FROM sources WHERE token = '$token'");
        if($getsource->num_rows != 0) {
            $sourceDetails = $getsource->fetch_assoc();
            return $this->getSourceDetails($sourceDetails['sourceid']);
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAllsources() {
        $getsources = $this->database->query("SELECT `sourceid` FROM sources ORDER BY name ASC");
        $returnArray = array();

        if($getsources->num_rows != 0) {
            while($sourceDetails = $getsources->fetch_assoc()) {
                $returnArray[] = $this->getSourceDetails($sourceDetails['sourceid']);
            }
        }
        return $returnArray;
    }

    /**
     * @param int $sourceid
     * @return bool|mysqli_result
     */
    public function deleteSource($sourceid) {
        $sourceid = $this->database->real_escape_string($sourceid);
        return $this->database->query("DELETE FROM sources WHERE sourceid = '$sourceid'");
    }

    /**
     * @return array|false
     */
    public function getSourceFromHeaders() {
        $headers = getallheaders();
        if(isset($headers['X-APP-TOKEN'])) {
            $token = $headers['X-APP-TOKEN'];
            return $this->getSourceByToken($token);
        } else
            return false;
    }

}