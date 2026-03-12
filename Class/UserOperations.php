<?php
/**
Beheer van gebruikers
**/
class UserOperations
{
    private $database;
    private $cryptoService;
    private $salt;

    /**
     * @param mysqli $database
     */
    public function __construct(mysqli $database) {
        $this->database = $database;
    }
    
     /**
     * Salt voor wachtwoord instellen
     *
     * @param string $salt
     */
    public function setSalt(string $salt) {
        $this->salt = $salt;
    }
    
     /**
     * @param CryptoService $cryptoService
     * @return void
     */
    public function loadCryptoService(CryptoService $cryptoService) {
        $this->cryptoService = $cryptoService;
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
        if($userDetails['fullname'] == "") {
        	$returnArray['fullname'] = "";
        	} else {
            $returnArray['fullname'] = $this->cryptoService->decryptData($userDetails['fullname']);
        }
        
        if($userDetails['email'] == "") {
        	$returnArray['email'] = "";
        	} else {
            $returnArray['email'] = $this->cryptoService->decryptData($userDetails['email']);
        }
        $returnArray['failedlogins'] = $userDetails['failedlogins'];
        $returnArray['archived'] = $userDetails['archived'];
        $returnArray['locked'] = $userDetails['locked'];
        $returnArray['lasttoken'] = $userDetails['lasttoken'];
        $returnArray['lastlogin'] = $userDetails['lastlogin'];
        $returnArray['changepassword'] = $userDetails['changepassword'];
        if($userDetails['2fa'] == "") {
        	$returnArray['2fa'] = "";
        	} else {
            $returnArray['2fa'] = $this->cryptoService->decryptData($userDetails['2fa']);
        }
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
        $email = $this->database->real_escape_string($this->cryptoService->encryptData($email));
        $fullname = $this->database->real_escape_string($this->cryptoService->encryptData($fullname));
        
        // Wachtwoord hashen in sha512 met salt
        $password = hash('sha512', $password.$this->salt);
        
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
            $querystringappend .= "`fullname` = '" . $this->database->real_escape_string($this->cryptoService->encryptData($fullname)) . "'";
        }
        
		// Is de e-mail niet leeg? Neem die dan mee in de query
        if($email != "") {
            $querystringappend .= ", `email` = '" .$this->database->real_escape_string($this->cryptoService->encryptData($email)) . "' ";
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
        	$this->resetFailedLogins($userid);
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
        
         // Wachtwoord hashen in sha512 met salt
        $password = hash('sha512', $password.$this->salt);
        
        if($requireChange) {
            $updatePassword = $this->database->query("UPDATE `users` SET `password` = '$password',`changepassword` = '1',`resettoken` = '', `salted` = '1' WHERE `userid` = '" . $userid . "'");
        } else {
            $updatePassword = $this->database->query("UPDATE `users` SET `password` = '$password',`changepassword` = '0',`resettoken` = '', `salted` = '1' WHERE `userid` = '" . $userid . "'");
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
        $users = $this->database->query("SELECT `userid` FROM `users`");
        $returnArray = array();
        if($users->num_rows > 0) {
            while($user = $users->fetch_assoc()) {
                $returnArray[] = $this->getUserDetails($user['userid']);
            }
        }
        // Sorteren
        if(count($returnArray) > 0) {
            foreach ($returnArray as $key => $row) {
                $volume[$key]  = $row['fullname'];
            }

            $volume  = array_column($returnArray, 'fullname');

            array_multisort($volume, SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $returnArray);
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
      $resettoken = $this->database->real_escape_string($this->cryptoService->encryptData($resettoken));
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
        if($secret != "") {
        		$secret = $this->database->real_escape_string($this->cryptoService->encryptData($secret));
        }

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
        $resettoken = $this->cryptoService->encryptData($this->cryptoService->generateUUID());
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
     * @return bool
     */
    function findUserByEmail(string $email) {
     	// Alle gebruikers opzoeken
        $finduser = $this->database->query("SELECT `userid` FROM `users`");
        
        // Is er een resultaat gevonden? 
        if ($finduser->num_rows != 0) {
        	// Zo ja, haal de gegevens op
            while($userDetails = $finduser->fetch_assoc()) {
            		// Zoek tussen de resultaten of ergens een matchend mailadres is gevonden
            		$userData = $this->getUserDetails($userDetails['userid']);
            		if(strtolower(trim($userData['email'])) == strtolower(trim($email))) {
            				// zo ja, geef 'true' terug
            				return true;
            		}
            }
        }
		// geen matches, dus geef 'true' terug
		return false;
    }
}
