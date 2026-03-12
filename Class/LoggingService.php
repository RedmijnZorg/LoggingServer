<?php
/**
Verwerken van logs
**/
class LoggingService
{
    private $database;
    private $cryptoService;
    private $ipAddresse;
    private $userAgent;
    private $referrer;

    /**
     * @param mysqli $database
     */
    public function __construct(mysqli $database) {
        $this->database = $database;
    }
    /**
     * @param CryptoService $cryptoService
     */
    public function loadCryptoService($cryptoService) {
        $this->cryptoService = $cryptoService;
    }
    
    /**
    * Informatie van bezoeker ophalen
    *
    * @return void
    */
    protected function getRemoteInformation() {
    	// IP adres opzoeken
    	if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) { 
              $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
              $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		$client  = @$_SERVER['HTTP_CLIENT_IP'];
		$forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];
	
		if(filter_var($client, FILTER_VALIDATE_IP)) {
				$this->ipAddress = $client;
			} elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
				$this->ipAddress = $forward;
			} else {
			$this->ipAddress = $remote;
		}
		
		// User agent en Referer opzoeken
    	$this->userAgent = $_SERVER['HTTP_USER_AGENT'];
    	$this->referrer = $_SERVER['HTTP_REFERER'];
    }
    
     /**
     * Gebeurtenis toevoegen aan logboek
     *
     * @param string $username
     * @param string $page
     * @param string $event
     * @param string $data
     * @param int $pass
     * @return bool
     */
    public function logEvent($username, $page, $event, $data, $pass) {
    		// Controleer of de 'pass' waarde op 0 of 1 staat
    		$pass = intval($pass);
    		if($pass != 0 AND $pass != 1) {
    				return false;
    		}
    		
    		// Gegevens van bezoeker ophalen
    		$this->getRemoteInformation();
    		
    		$timestamp = time();
    		$ipAddress = $this->cryptoService->encryptData(intval(ip2long($this->ipAddress)));
    		$userAgent = $this->cryptoService->encryptData($this->userAgent);
    		$referrer = $this->cryptoService->encryptData($this->referrer);
    		$username = $this->cryptoService->encryptData($username);
    		$page = $this->cryptoService->encryptData($page);
    		$event = $this->cryptoService->encryptData($event);
    		$data = $this->cryptoService->encryptData($data);
    		
    		// Informatie voorbereiden voor de database
    		$pass = $this->database->real_escape_string($pass);
    		$ipAddress = $this->database->real_escape_string($ipAddress);
    		$userAgent = $this->database->real_escape_string($userAgent);
    		$referrer = $this->database->real_escape_string($referrer);
    		$username = $this->database->real_escape_string($username);
    		$page = $this->database->real_escape_string($page);
    		$event = $this->database->real_escape_string($event);
    		$data = $this->database->real_escape_string($data);
    		$timestamp = $this->database->real_escape_string($timestamp);
    		
    		// Item toevoegen
    		$additem = $this->database->query("INSERT INTO `logging` (
    		`timestamp`,
    		`ip`,
    		`useragent`,
    		`referrer`,
    		`username`,
    		`page`,
    		`event`,
    		`data`,
    		`pass`,
    		`sync`) VALUES (
    		'".$timestamp."',
    		'".$ipAddress."',
    		'".$userAgent."',
    		'".$referrer."',
    		'".$username."',
    		'".$page."',
    		'".$event."',
    		'".$data."',
    		'".$pass."',
    		'0')");
    		return $additem;
    }
    
     /**
     * Ongesyncte logs ophalen
     *
     * @return array
     */
    public function getUnsyncedLogs() {
    		$getitems = $this->database->query("SELECT * FROM `logging` WHERE `sync` = '0' ORDER BY `timestamp` ASC");
    		
    		// Niets gevonden? Geef een lege array terug
    		if($getitems->num_rows == NULL) {
    				return array();
    		}
    		$returnArray = array();
    		
    		// Gevonden? Voeg toe aan de array
    		while($itemDetails = $getitems->fetch_assoc()) {
    				unset($itemDetails['sync']); // sync waarde alleen nodig voor log server
    				$itemDetails['timestamp'] = date(DATE_ATOM,$itemDetails['timestamp']);
    				$itemDetails['ip'] = long2ip(intval($this->cryptoService->decryptData($itemDetails['ip'])));
    				$itemDetails['useragent'] = $this->cryptoService->decryptData($itemDetails['useragent']);
    				$itemDetails['referrer'] = $this->cryptoService->decryptData($itemDetails['referrer']);
    				$itemDetails['username'] = $this->cryptoService->decryptData($itemDetails['username']);
    				$itemDetails['page'] = $this->cryptoService->decryptData($itemDetails['page']);
    				$itemDetails['event'] = $this->cryptoService->decryptData($itemDetails['event']);
    				$itemDetails['data'] = $this->cryptoService->decryptData($itemDetails['data']);
    				
    				$returnArray[] = $itemDetails;
    		}
    		
    		return $returnArray;
    }
    
     /**
     * Log markeren als gesynct
     *
     * @param int $logid
     * @return bool
     */
    public function markLogSynced(int $logid) {
        	$logid = $this->database->real_escape_string(intval($logid));
    		return $this->database->query("UPDATE `logging` SET `sync` = '1' WHERE `logid` = '$logid'");	
    }
    
    /**
     * Logs ophalen die aan een bepaald filter voldoen
     *
     * @param int $yearFilter
     * @param int $monthFilter
     * @param int $dayFilter
     * @param string $eventFilter
     * @param string $textFilter
     * @param int $allowedFilter
     * @param string $sortField
     * @param string $sortDirection
     * @return array
     */
    public function getLogs(int $yearFilter = 0, int $monthFilter = 0, int $dayFilter = 0, string $eventFilter = "", string $textFilter = "", int $allowedFilter = 2, $sortField = "timestamp", $sortDirection = "ASC") {
    		// Oplopend sorteren tenzij specifiek 'DESC' is opgegeven
    		if($sortDirection == "DESC") {
    				$sortDirection = "DESC";
    			} else {
    				$sortDirection = "ASC";
    		}
    		
    		// Sorteren op 'timestamp' tenzij 'ip','pass' of 'sync' is opgegeven
    		if($sortField == "ip") {
    				$sortField = "ip";		
    			} elseif($sortField == "pass") {
    				$sortField = "pass"; 
    		
    			} elseif($sortField == "sync") {
    				$sortField = "sync"; 
    		
    			} else {
    				$sortField = "timestamp"; 
    		}
    		
    		// Standaardwaarden voor timestamps instellen
    		$timestart = 0;
    		$timeend = 0;
    		
    		// Is een jaar opgegeven, maar geen maand?
    		// Maak timestamps van 1 januari t/m 31 december
    		if($yearFilter != 0 AND $monthFilter == 0) {
    			$timestart = strtotime($yearFilter."-01-01 00:00:00");
    			$timeend = strtotime($yearFilter."-12-31 23:59:59");
    			// Is een jaar en maand opgegeven, maar geen dag?
    			// Maak timestamps van de 1e t/m de laatste dag van die maand
    		} elseif($yearFilter != 0 AND $monthFilter != 0 AND $dayFilter == 0) {
    			$timestart = strtotime($yearFilter."-".$monthFilter."-01 00:00:00");
    			$lastDay = date('Y-m-t', strtotime($yearFilter."-".$monthFilter."-01"));
				$timeend = strtotime($lastDay." 23:59:59");
				// Is een jaar, maand en dag opgegeven?
    			// Maak timestamps van 0:00:00 t/m 23:59:59 van die dag
    		} elseif($yearFilter != 0 AND $monthFilter != 0 AND $dayFilter != 0) {
				$timestart = strtotime($yearFilter."-".$monthFilter."-".$dayFilter." 00:00:00");
				$timeend = strtotime($yearFilter."-".$monthFilter."-".$dayFilter." 23:59:59");
			}    		
			
    		if($timestart > 0) {
    				// Zijn timestamps opgegeven? Gebruik die in de zoekopdracht
    		 		$getitems = $this->database->query("SELECT * FROM `logging` WHERE `timestamp` >= '$timestart' AND `timestamp` <= '$timeend' ORDER BY `".$sortField."` ".$sortDirection);
    			} else {
    				// Zijn geen timestamps opgegeven? Zoek dan op alles
    		 		$getitems = $this->database->query("SELECT * FROM `logging` ORDER BY `".$sortField."` ".$sortDirection);
    		}

			// Geen resultaten? Geef een lege array terug
    		if($getitems->num_rows == NULL) {
    			return array();
    		}
    		$returnArray = array();    		    		    		
			// Resultaten gevonden? Pas verdere filters toe
    		while($itemDetails = $getitems->fetch_assoc()) {
    				// Filteren op gelukte gebeurtenissen indien ingesteld
    				if($allowedFilter == 0 OR $allowedFilter == 1) {
    					if($itemDetails['pass'] != $allowedFilter) {
    							continue;
    					}
    				}
    				
    				// Timestamp omzetten in ATOM formaat
    				$itemDetails['timestamp'] = date(DATE_ATOM,$itemDetails['timestamp']);
    				
    				// IP adres converteren vanuit long
    				$itemDetails['ip'] = long2ip(intval($this->cryptoService->decryptData($itemDetails['ip'])));
    				
    				$itemDetails['useragent'] = $this->cryptoService->decryptData($itemDetails['useragent']);
    				$itemDetails['referrer'] = $this->cryptoService->decryptData($itemDetails['referrer']);
    				$itemDetails['username'] = $this->cryptoService->decryptData($itemDetails['username']);
    				$itemDetails['page'] = $this->cryptoService->decryptData($itemDetails['page']);
    				$itemDetails['event'] = $this->cryptoService->decryptData($itemDetails['event']);
    				$itemDetails['data'] = $this->cryptoService->decryptData($itemDetails['data']);
    				
    				// Filteren op gebeurtenis indien ingesteld
    				if($eventFilter != "" AND $itemDetails['event'] != $eventFilter) {
    						continue;
    				}
    				
    				
    				// Filteren op tekst indien ingesteld
    				if($textFilter != "") {
    						$matchFound = false;
    						// Tekst zoeken in IP adres
    						if(stristr($itemDetails['ip'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in useragent
    						if(stristr($itemDetails['useragent'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in referen
    						if(stristr($itemDetails['referrer'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in gebruikersnaam
    						if(stristr($itemDetails['username'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in pagina
    						if(stristr($itemDetails['page'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in gebeurtenis
    						if(stristr($itemDetails['event'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Tekst zoeken in IP data
    						if(stristr($itemDetails['data'],$textFilter)) {
    								$matchFound = true;
    						}
    						// Geen match gevonden? Negeer deze log
    						if($matchFound == false) {
    								continue;
    						}
    				}
    				
    				// Match gevonden? Voeg toe aan array
    				$returnArray[] = $itemDetails;
    		}
    		return $returnArray;
    }
    
    /**
    * Logs verwijderen die ouder zijn dan x dagen
    *
    * @param int $retentionDays
    * @return int
    */
    function cleanLogs(int $retentionDays) {
    	// Dagen omzetten in seconden, en die van het huidige timestamp aftrekken
    	$retentionDays = $this->database->real_escape_string(intval($retentionDays));
    	$timestampRetention = time() - ($retentionDays * 86400);
    	
    	// Logs zoeken die ouder zijn dan x dagen
    	$findlogs = $this->database->query("SELECT `logid` FROM `logging` WHERE `timestamp` <= '$timestampRetention'");
    	if($findlogs->num_rows == NULL) {
    			// Indien niet gevonden, geef 0 terug
    			return 0;
    		} else {
    			$cleanLogs = $this->database->query("DELETE FROM `logging` WHERE `timestamp` <= '$timestampRetention'");
    			if($cleanLogs) {
    					// Is het gelukt? Geef het aantal gevonden items terug
    					return $findlogs->num_rows;
    				} else {
    					// Is het niet gelukt? Geef dan 0 terug
    					return 0;
    			}
    	}
    }
}
