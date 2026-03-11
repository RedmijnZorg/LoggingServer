<?php
/**
Beheer van gebruikers
**/
class UserOperations
{
    private $database;
    private $cryptoService;

    /**
     * @param mysqli $database
     */
    public function __construct(mysqli $database) {
        $this->database = $database;
    }

    /**
     * Details gebruiker ophalen
     *
     * @param int $userid
     * @return array
     */
    public function getUserDetails(int $userid) {
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
     * Gebruiker toevoegen
     *
     * @param string $email
     * @param string $password
     * @param string $fullname
     *
     * @return int|bool
     */
    public function addUser(string $email, string $password, string $fullname) {
        $email = $this->database->real_escape_string($email);
        
        // Wachtwoord hashen in sha512
        $password = hash('sha512', $password);
        $findMatchingUser = $this->database->query("SELECT `userid` FROM users WHERE `email` = '" . $email . "'");
        
        // Is er al een gebruiker met deze gegevens? Geef 'false' terug
        if($findMatchingUser->num_rows > 0) {
            return false;
        } else {
        	// Is er geen match? Voeg dan de gebruiker toe
            $adduser = $this->database->query("INSERT INTO `users` (`password`,`email`,`fullname`,`changepassword`,`locked`) VALUES('$password','$email','$fullname','1','0')");
            if($adduser) {
            	// Is het gelukt? Geef dan het id terug
                return $this->database->insert_id;
            } else {
                // Is het niet gelukt? Geef dan 'false' terug
                return false;
            }
        }
    }

    /**
     * Gebruiker bewerken
     *
     * @param int $userid
     * @param string $fullname
     * @param string $email
     * @return bool
     */
    public function updateUser(int $userid, string $fullname, string $email) {
    	// Query opbouwen
        $querystring = "UPDATE `users` SET ";
        $querystringappend = "";
        $userid = $this->database->real_escape_string(json_encode(intval($userid)));

		// Is de naam niet leeg? Neem die dan mee in de query
        if($fullname != "") {
            $querystringappend .= "`fullname` = '" . $fullname . "'";
        }
        
		// Is de e-mail niet leeg? Neem die dan mee in de query
        if($email != "") {
            $querystringappend .= ", `email` = '" .$this->database->real_escape_string($email) . "' ";
        }
        
        // Query aan elkaar plakken
        if(substr($querystringappend, 0,2) == ", ") {
            $querystringappend = substr($querystringappend, 2, strlen($querystringappend) - 2);
        }
        $querystring .= $querystringappend." WHERE `userid` = '" . $userid . "'";
        
        // Query uitvoeren
        $updateuser = $this->database->query($querystring);
        if($updateuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gebruiker verwijderen
     *
     * @param int $userid
     * @return bool
     */
    public function deleteUser(int $userid) {
        $userid = $this->database->real_escape_string($userid);
        $deleteuser = $this->database->query("DELETE FROM `users` WHERE `userid` = '" . $userid . "'");
        if($deleteuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gebruiker vergrendelen
     *
     * @param int $userid
     * @return bool
     */
    public function lockUser(int $userid) {
        $userid = $this->database->real_escape_string($userid);
        $lockuser = $this->database->query("UPDATE `users` SET `locked` = '1' WHERE `userid` = '" . $userid . "'");
        if($lockuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gebruiker ontgrendelen
     *
     * @param int $userid
     * @return bool
     */
    public function unlockUser(int $userid){
        $userid = $this->database->real_escape_string($userid);
        $unlockuser = $this->database->query("UPDATE `users` SET `locked` = '0' WHERE `userid` = '" . $userid . "'");
        if($unlockuser) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Wachtwoord wijzigen
     *
     * @param int $userid
     * @param string $password
     * @param bool $requireChange
     * @return bool
     */
    public function changePassword(int $userid, string $password, bool $requireChange = false) {
        $userid = $this->database->real_escape_string($userid);
        
        // Wachtwoord opslaan in hash sha512
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
     * Mislukte logins verhogen
     *
     * @param int $userid
     * @return bool
     */
    public function increaseFailedLogins(int $userid) {
        $userid = $this->database->real_escape_string($userid);
        
        // Gebruiker opzoeken
        $getuser = $this->database->query("SELECT `failedlogins` FROM `users` WHERE `userid` = '" . $userid . "'");
        if($getuser->num_rows > 0) {
        	// Is deze gevonden? Neem het aantal mislukte logins en tel 1 op
            $userdetails = $getuser->fetch_assoc();
            $failedloginsFound = $userdetails['failedlogins'];
            $failedlogins = $failedloginsFound+1;
            // Is het aantal mislukte logins 10 of hoger? Vergrendel dan het account
            if($failedlogins >= 10) {
                $this->lockUser($userid);
                $updateFailedLogins = true;
            } else {
            	// Is het onder 10? Registreer het dan in de database
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
     * Mislukte logins resetten
     *
     * @param int $userid
     * @return mixed
     */
    public function resetFailedLogins(int $userid) {
        $userid = $this->database->real_escape_string($userid);
        return $this->database->query("UPDATE `users` SET `failedlogins` = '$failedlogins' WHERE `userid` = '" . $userid . "'");
    }

    /**
     * Laatste login bijwerken
     *
     * @param int $userid
     * @return bool
     */
    public function updateLastLogin(int $userid) {
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
     * Alle gebruikers ophalen
     *
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
     * Gebruiker opzoeken via reset token
     *
     * @param string $resettoken
     * @return array|false
     */
    public function getUserByResetToken(string $resettoken = "")
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
     * Authenticator secret instellen voor gebruiker
     *
     * @param int $userid
     * @param string $secret
     * @return mixed
     */
    public function setUser2FASecret(int $userid, string $secret = "") {
        $userid = $this->database->real_escape_string($userid);
        $secret = $this->database->real_escape_string($secret);

        $setSecret = $this->database->query("UPDATE `users` SET `2fa` = '$secret' WHERE `userid` = '" . $userid . "'");
        return $setSecret;

    }

    /**
     * Reset token toewijzen aan gebruiker
     *
     * @param int $userid
     * @return string|bool
     */
    public function assignResetToken(int $userid) {
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
     * Gebruiker zoeken via e-mail
     *
     * @param string $email
     * @return false|int
     */
    public function findUserByEmail(string $email) {
        $email = $this->database->real_escape_string($email);
        $getuser = $this->database->query("SELECT `userid` FROM `users` WHERE `email` = '" . $email . "'");
        if($getuser->num_rows > 0) {
            $userdetails = $getuser->fetch_assoc();
            $userid = $userdetails['userid'];
            return $userid;
        } else {
            return false;
        }
    }
}
