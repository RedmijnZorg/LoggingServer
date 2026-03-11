<?php
/**
Verwerken van logins via webpagina en SSO
**/
class LoginOperations {
    private $database;
    private $userOperations;

    /**
     * @param mysqli $database
     */
    public function __construct(mysqli $database) {
        $this->database = $database;
        $this->userOperations = new UserOperations($this->database);
    }

    /**
     * Inloggen met e-mail en wachtwoord
     *
     * @param string $email
     * @param string $password
     * @return array|false
     */
    public function login(string $email, string $password) {
    
    	// e-mail voorbereiden voor de database
        $email = $this->database->real_escape_string($email);
        
        // wachtwoord hashen in sha512
        $password = hash('sha512', $password);
        
        // Gebruiker opzoeken, daarbij een vergrendeld account negeren
        $finduser = $this->database->query("SELECT `userid` FROM `users` WHERE `email` = '".$email."' AND `password` = '$password' AND `locked` = '0'");
        // Is er een resultaat gevonden? 
        if ($finduser->num_rows != 0) {
        	// Zo ja, haal de gegevens op
            $userDetails = $finduser->fetch_assoc();
            $returnArray = $this->userOperations->getUserDetails($userDetails['userid']);
            $userid = $this->database->real_escape_string($returnArray['userid']);
            $timestamp = time();
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