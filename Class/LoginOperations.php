<?php
/**
Verwerken van logins via webpagina en SSO
**/
class LoginOperations {
    private $database;
    private $userOperations;
    private $salt;

    /**
     * @param mysqli $database
     */
    public function __construct(mysqli $database) {
        $this->database = $database;
        $this->userOperations = new UserOperations($this->database);
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
     * Inloggen met e-mail en wachtwoord
     *
     * @param string $email
     * @param string $passwordinput
     * @return array|false
     */
    public function login(string $email, string $passwordinput) {
    
    	// e-mail voorbereiden voor de database
        $email = $this->database->real_escape_string($email);
        
        // wachtwoord hashen in sha512
        $password = hash('sha512', $passwordinput);
        
        // Voor overgang ook een wachtwoord met salt maken
        $passwordSalt = hash('sha512', $passwordinput.$this->salt);
        
        
        // Gebruiker opzoeken, daarbij een vergrendeld account negeren
        $finduser = $this->database->query("SELECT `userid`,`salted` FROM `users` WHERE `email` = '".$email."' AND (`password` = '$password' OR `password` = '$passwordSalt') AND `locked` = '0'");
        // Is er een resultaat gevonden? 
        if ($finduser->num_rows != 0) {
        	// Zo ja, haal de gegevens op
            $userDetails = $finduser->fetch_assoc();
            $returnArray = $this->userOperations->getUserDetails($userDetails['userid']);
            $userid = $this->database->real_escape_string($returnArray['userid']);
            $timestamp = time();
            // Voor overgang niet-gehashte wachtwoorden omzetten naar gehashte
            if($userDetails['salted'] == '0') {
            		$this->database->query("UPDATE `users` SET `password` = '$passwordSalt', `salted` = '1' WHERE `userid` = '$userid'");
            }
            // Stel de laatste login in op nu
            $this->database->query("UPDATE `users` SET `lastlogin` = '$timestamp' WHERE `userid` = '$userid'");
            // Nogmaals controleren op een vergrendeling. Zo ja, geef 'false' terug
            if($returnArray['locked'] == 1) {
                return false;
            }
            return $returnArray;
        } else {
        	// Is de combinatie e-mail en wachtwoord onjuist, maar bestaat het mailadres wel?
        	// Registreer een mislukte loginpoging op dat mailadres
            $finduser = $this->database->query("SELECT `userid`,`failedlogins` FROM `users` WHERE `email` = '".$email."' AND `locked` = '0'");
            if ($finduser->num_rows != 0) {
                $userDetails = $finduser->fetch_assoc();
                $useridFound = $userDetails['userid'];
                $this->userOperations->increaseFailedLogins($useridFound);
            }
            return false;
        }
    }

}