<?php

class UserOperations
{
    private $database;
    private $cryptoService;

    /**
     * @param mysqli $database
     */
    public function __construct($database) {
        $this->database = $database;
    }

    /**
     * @param int $userid
     * @return array
     */
    public function getUserDetails($userid) {
        $userid = $this->database->real_escape_string($userid);
        $getuser = $this->database->query("SELECT * FROM users WHERE `userid` = '" . $userid . "'");
        if ($getuser->num_rows == 0) {
            return false;
        }
        $userDetails = $getuser->fetch_assoc();
        $returnArray = array();
        $useridFound = $userDetails['userid'];
        $returnArray['userid'] = $useridFound;
        $returnArray['fullname'] = $userDetails['fullname'];
        $returnArray['email'] = $userDetails['email'];
        $returnArray['failedlogins'] = $userDetails['failedlogins'];
        $returnArray['archived'] = $userDetails['archived'];
        $returnArray['locked'] = $userDetails['locked'];
        $returnArray['lasttoken'] = $userDetails['lasttoken'];
        $returnArray['lastlogin'] = $userDetails['lastlogin'];
        $returnArray['changepassword'] = $userDetails['changepassword'];
        $returnArray['2fa'] = $userDetails['2fa'];
        return $returnArray;
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $fullname
     *
     * @return int|bool
     */
    public function addUser($email, $password, $fullname) {
        $email = $this->database->real_escape_string($email);
        $password = hash('sha512', $password);
        $findMatchingUser = $this->database->query("SELECT `userid` FROM users WHERE `email` = '" . $email . "'");
        if($findMatchingUser->num_rows > 0) {
            return false;
        } else {
            $adduser = $this->database->query("INSERT INTO `users` (`password`,`email`,`fullname`,`changepassword`,`locked`) VALUES('$password','$email','$fullname','1','0')");
            if($adduser) {
                return $this->database->insert_id;
            } else {
                return false;
            }
        }
    }

    /**
     * @param int $userid
     * @param string $fullname
     * @param string $email
     * @return bool
     */
    public function updateUser($userid, $fullname,$email) {
        $querystring = "UPDATE `users` SET ";
        $querystringappend = "";
        $userid = $this->database->real_escape_string(json_encode(intval($userid)));

        if($fullname != "") {
            $querystringappend .= "`fullname` = '" . $fullname . "'";
        }
        if($email != "") {
            $querystringappend .= ", `email` = '" .$this->database->real_escape_string($email) . "' ";
        }
        if(substr($querystringappend, 0,2) == ", ") {
            $querystringappend = substr($querystringappend, 2, strlen($querystringappend) - 2);
        }
        $querystring .= $querystringappend." WHERE `userid` = '" . $userid . "'";
        $updateuser = $this->database->query($querystring);
        if($updateuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function deleteUser($userid) {
        $userid = $this->database->real_escape_string($userid);
        $deleteuser = $this->database->query("DELETE FROM `users` WHERE `userid` = '" . $userid . "'");
        if($deleteuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function lockUser($userid)
    {
        $userid = $this->database->real_escape_string($userid);
        $lockuser = $this->database->query("UPDATE `users` SET `locked` = '1' WHERE `userid` = '" . $userid . "'");
        if($lockuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function unlockUser($userid){
        $userid = $this->database->real_escape_string($userid);
        $unlockuser = $this->database->query("UPDATE `users` SET `locked` = '0' WHERE `userid` = '" . $userid . "'");
        if($unlockuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @param string $password
     * @param bool $requireChange
     * @return bool
     */
    public function changePassword($userid, $password, $requireChange = false) {
        $userid = $this->database->real_escape_string($userid);
        $password = hash('sha512', $password);
        if($requireChange) {
            $updatePassword = $this->database->query("UPDATE `users` SET `password` = '$password',`changepassword` = '1',`resettoken` = '' WHERE `userid` = '" . $userid . "'");
        } else {
            $updatePassword = $this->database->query("UPDATE `users` SET `password` = '$password',`changepassword` = '0',`resettoken` = '' WHERE `userid` = '" . $userid . "'");
        }
        if($updatePassword) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function increaseFailedLogins($userid) {
        $userid = $this->database->real_escape_string($userid);
        $getuser = $this->database->query("SELECT `failedlogins` FROM `users` WHERE `userid` = '" . $userid . "'");
        if($getuser->num_rows > 0) {
            $userdetails = $getuser->fetch_assoc();
            $failedloginsFound = $userdetails['failedlogins'];
            $failedlogins = $failedloginsFound+1;
            if($failedlogins >= 10) {
                $this->lockUser($userid);
                $updateFailedLogins = true;
            } else {
                $updateFailedLogins = $this->database->query("UPDATE `users` SET `failedlogins` = '$failedlogins' WHERE `userid` = '" . $userid . "'");
            }
            if($updateFailedLogins) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return mixed
     */
    public function resetFailedLogins($userid) {
        $userid = $this->database->real_escape_string($userid);
        return $this->database->query("UPDATE `users` SET `failedlogins` = '$failedlogins' WHERE `userid` = '" . $userid . "'");
    }

    /**
     * @param int $userid
     * @return bool
     */
    public function updateLastLogin($userid) {
        $userid = $this->database->real_escape_string($userid);
        $timestamp = time();
        $updateLastLogin = $this->database->query("UPDATE `users` SET lastlogin = '$timestamp' WHERE `userid` = '" . $userid . "'");
        if($updateLastLogin) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAllUsers() {
        $users = $this->database->query("SELECT `userid` FROM `users` ORDER BY `fullname` ASC");
        $returnArray = array();
        if($users->num_rows > 0) {
            while($user = $users->fetch_assoc()) {
                $returnArray[] = $this->getUserDetails($user['userid']);
            }
        }
        return $returnArray;
    }

    /**
     * @param string $resettoken
     * @return array|false
     */
    public function getUserByResetToken($resettoken = "")
    {
      if($resettoken == "") {
          return false;
      }
      $resettoken = $this->database->real_escape_string($resettoken);
      $finduser = $this->database->query("SELECT `userid` FROM `users` WHERE `resettoken` = '" . $resettoken . "'");
      if($finduser->num_rows > 0) {
          $userdetails = $finduser->fetch_assoc();
          return $this->getUserDetails($userdetails['userid']);
      } else {
          return false;
      }
    }

    /**
     * @param int $userid
     * @param string $secret
     * @return mixed
     */
    public function setUser2FASecret($userid, $secret = "") {
        $userid = $this->database->real_escape_string($userid);
        $secret = $this->database->real_escape_string($secret);

        $setSecret = $this->database->query("UPDATE `users` SET `2fa` = '$secret' WHERE `userid` = '" . $userid . "'");
        return $setSecret;

    }

    /**
     * @param int $userid
     * @return string|bool
     */
    public function assignResetToken($userid) {
        $userid = $this->database->real_escape_string($userid);
        $cryotoservice = new CryptoService();
        $resettoken = $cryotoservice->generateUUID();
        $assign = $this->database->query("UPDATE `users` SET `resettoken` = '$resettoken' WHERE `userid` = '" . $userid . "'");
        if($assign) {
            return $resettoken;
        } else {
            return false;
        }
    }

    /**
     * @param string $email
     * @return false|mixed
     */
    public function findUserByEmail($email) {
        $email = $this->database->real_escape_string($email);
        $getuser = $this->database->query("SELECT `userid` FROM `users` WHERE `email` = '" . $email . "'");
        if($getuser->num_rows > 0) {
            $userdetails = $getuser->fetch_assoc();
            $userid = $userdetails['userid'];
            return $userid;;
        } else {
            return false;
        }
    }
}
