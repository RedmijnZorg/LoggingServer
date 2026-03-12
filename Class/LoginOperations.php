<?php
/**
Verwerken van logins via webpagina en SSO
**/
class LoginOperations {
    private $database;
    private $userOperations;
    private $salt;
    private $cryptoService;

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
     * @param CryptoService $cryptoService
     * @return void
     */
    public function loadCryptoService(CryptoService $cryptoService) {
        $this->cryptoService = $cryptoService;
        $this->userOperations->loadCryptoService($cryptoService);
    }

    /**
     * Inloggen met e-mail en wachtwoord
     *
     * @param string $email
     * @param string $passwordinput
     * @return array|false
     */
    public function login(string $email, string $passwordinput) {
        // wachtwoord hashen in sha512
        $password = hash('sha512', $passwordinput);
        
        // Voor overgang ook een wachtwoord met salt maken
        $passwordSalt = hash('sha512', $passwordinput.$this->salt);
        
        // Gebruikers mmet dit wachtwoord opzoeken, daarbij een vergrendeld account negeren
        $finduser = $this->database->query("SELECT `userid`,`email`,`salted` FROM `users` WHERE (`password` = '$password' OR `password` = '$passwordSalt') AND `locked` = '0'");
        
        // Er vanuit gaan dat er niets is gevonden
        $userfound = false;
        
        // Is er een resultaat gevonden? 
        if ($finduser->num_rows != 0) {
        	// Zo ja, haal de gegevens op
            while($userDetails = $finduser->fetch_assoc()) {
            		// Zoek tussen de resultaten of ergens een matchend mailadres is gevonden
            		$userData = $this->userOperations->getUserDetails($userDetails['userid']);
            		if(strtolower($userData['email']) == strtolower($email)) {
            				// Zo ja? Registreer een match en bewaar de gegevens
            				$userfound = true;
            				$returnArray = $userData;
            				$userid = $this->database->real_escape_string($userData['userid']);
            				break;
            		}
            }
            
            // Zo niet, geef 'false' terug
            if($userfound == false) {
                return false;
            }
           
            $timestamp = time();
            
            // Voor overgang niet-gehashte wachtwoorden omzetten naar gehashte
            if($userDetails['salted'] == '0') {
            		$this->database->query("UPDATE `users` SET `password` = '$passwordSalt', `salted` = '1' WHERE `userid` = '$userid'");
            }
            // Stel de laatste login in op nu
            $this->database->query("UPDATE `users` SET `lastlogin` = '$timestamp', `failedlogins` = '0' WHERE `userid` = '$userid'");
            
            // Nogmaals controleren op een vergrendeling. Zo ja, geef 'false' terug
            if($returnArray['locked'] == 1) {
                return false;
            }
            return $returnArray;
        } else {
        	// Is de combinatie e-mail en wachtwoord onjuist, maar bestaat het mailadres wel?
        	// Registreer een mislukte loginpoging op dat mailadres
        	
        	// Alle gebruikers die niet geblokkeerd zijn ophalen
            $findusers = $this->database->query("SELECT `userid` FROM `users` WHERE `locked` = '0'");
            
            // Zijn er gebruikers gevonden?
            if ($findusers->num_rows != 0) {
            
            	// Ga door alle gebruikers
                while($userDetails = $findusers->fetch_assoc()) {
                		// Zoek een gebruiker met dit mailadres
                		$useridFound = $userDetails['userid'];
                		$userData = $this->userOperations->getUserDetails($userDetails['userid']);
                		// Gevonden? Verhoog het aantal mislukte logins
                		if(strtolower($userData['email']) == strtolower($email)) {
                				$this->userOperations->increaseFailedLogins($useridFound);
                				break;
						}
                }
            }
            return false;
        }
    }

}