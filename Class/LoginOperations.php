<?php

class LoginOperations {
    private $database;
    private $userOperations;

    /**
     * @param mysqli $database
     */
    public function __construct($database) {
        $this->database = $database;
        $this->userOperations = new UserOperations($this->database);
    }

    /**
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function login($email, $password) {
        $email = $this->database->real_escape_string($email);
        $password = hash('sha512', $password);
        $finduser = $this->database->query("SELECT `userid` FROM `users` WHERE `email` = '".$email."' AND `password` = '$password' AND `locked` = '0'");
        if ($finduser->num_rows != 0) {
            $userDetails = $finduser->fetch_assoc();
            $returnArray = $this->userOperations->getUserDetails($userDetails['userid']);
            $userid = $this->database->real_escape_string($returnArray['userid']);
            $timestamp = time();
            $this->database->query("UPDATE `users` SET `lastlogin` = '$timestamp' WHERE `userid` = '$userid'");
            if($returnArray['locked'] == 1) {
                return false;
            }
            return $returnArray;
        } else {
            $finduser = $this->database->query("SELECT `userid`,`failedlogins` FROM `users` WHERE `email` = '".$email."' AND `locked` = '0'");
            if ($finduser->num_rows != 0) {
                $userDetails = $finduser->fetch_assoc();
                $useridFound = $userDetails['userid'];
                $this->userOperations->increaseFailedLogins($useridFound);
            }
            return false;
        }
    }

    /**
     * @param int $userid
     * @return string
     */
    public function setLoginToken($userid) {
        $cryptoService = new CryptoService();
        $token = $cryptoService->generateUUID();
        $this->database->query("INSERT INTO `logintokens` (`userid`, `token`) VALUES ('$userid', '$token')");
        return $token;
    }

    /**
     * @param string $token
     * @return int
     */
    public function matchLoginToken($token) {
        $token = $this->database->real_escape_string($token);
        $finduser = $this->database->query("SELECT * FROM `logintokens` WHERE `token` = '$token'");
        if ($finduser->num_rows != 0) {
            $userDetails = $finduser->fetch_assoc();
            $this->deleteLoginToken($token);
            return $userDetails['userid'];
        } else {
            return 0;
        }
    }

}