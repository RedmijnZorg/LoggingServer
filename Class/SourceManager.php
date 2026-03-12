<?php
/**
Beheer van log bronnen
**/
class SourceManager
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
	 * @param CryptoService $cryptoService
	 * @return void
	 */ 
	public function loadCryptoService(CryptoService $cryptoService) {
		$this->cryptoService = $cryptoService;
	}
	
    /**
     * Voegt een nieuwe bron toe
     *
     * @param string $name
     * @return false|int
     */
    public function addSource(string $name) {
        $name = $this->database->real_escape_string($this->cryptoService->encryptData($name));
        $token = $this->cryptoService->encryptData($this->cryptoService->generateUUID());

		// Bron toevoegen
        $addsource = $this->database->query("INSERT INTO sources (name, token,lastsignal) VALUES ('$name', '$token','0')");
        if ($addsource) {
        	// Is het gelukt? Geef het nieuwe id terug
            return $this->database->insert_id;
        } else {
        	// Is het niet gelukt? Geef 'false' terug
            return false;
        }
    }

    /**
     * Haalt de details van een bron op
     *     
     * @param int $sourceid
     * @return array|false
     */
    public function getSourceDetails(int $sourceid) {
        $sourceid = $this->database->real_escape_string($sourceid);
        
        // De bron opzoeken
        $getsource = $this->database->query("SELECT * FROM sources WHERE sourceid = '$sourceid'");
        if($getsource->num_rows != 0) {
        	// Is de bron gevonden? Geef de details terug
            $sourcedetails = $getsource->fetch_assoc();
            
            // Data ontsleutelen
            $sourcedetails['name'] = $this->cryptoService->decryptData($sourcedetails['name']);
            $sourcedetails['token'] = $this->cryptoService->decryptData($sourcedetails['token']);
            
            return $sourcedetails;
        } else {
        	// Is de bron niet gevonden? Geef 'false' terug
            return false;
        }
    }
    
    /**
     * Zoekt een bron op basis van een token
     *     
     * @param $token
     * @return array|false
     */
    public function getSourceByToken(string $token) {
        $token = $this->database->real_escape_string($token);

        // Zoek de app die bij dit token past
        $getsources = $this->database->query("SELECT `sourceid` FROM sources");
        if($getsources->num_rows != 0) {
            while($sourceDetails = $getsources->fetch_assoc()) {
            	$detailsFound = $this->getSourceDetails($sourceDetails['sourceid']);
            	 // Is de bron gevonden? Geef de details terug
            	if($detailsFound['token'] == $token) {
            		return $detailsFound;
            	}
            }
            // Is de bron niet gevonden? Geef 'false' terug
            return false;
        } else {
        	// Is de bron niet gevonden? Geef 'false' terug
            return false;
        }
    }

    /**
     * Haalt alle bronnen op
     *
     * @return array
     */
    public function getAllsources() {
    	// Alle bronnen ophalen
        $getsources = $this->database->query("SELECT `sourceid` FROM sources");
        $returnArray = array();

        if($getsources->num_rows != 0) {
        	// Voeg de details toe aan de array
            while($sourceDetails = $getsources->fetch_assoc()) {
                $returnArray[] = $this->getSourceDetails($sourceDetails['sourceid']);
            }
        }
        
        // Sorteren
        if(count($returnArray) > 0) {
            foreach ($returnArray as $key => $row) {
                $volume[$key]  = $row['name'];
            }

            $volume  = array_column($returnArray, 'name');

            array_multisort($volume, SORT_ASC, SORT_NATURAL|SORT_FLAG_CASE, $returnArray);
        }
        
        return $returnArray;
    }

    /**
     * Verwijdert een bron
     *
     * @param int $sourceid
     * @return bool|mysqli_result
     */
    public function deleteSource(int $sourceid) {
        $sourceid = $this->database->real_escape_string($sourceid);
        return $this->database->query("DELETE FROM sources WHERE sourceid = '$sourceid'");
    }

    /**
     * Haalt de token uit de headers en zoekt de bijbehorende bron op
     *
     * @return array|false
     */
    public function getSourceFromHeaders() {
        $headers = getallheaders();
        
        // Zoeken naar de header 'X-APP-TOKEN'
        if(isset($headers['X-APP-TOKEN'])) {
        	// Is de header gevonden? Zoek de bron op basis van deze waarde en geef het resultaat
            $token = $headers['X-APP-TOKEN'];
            return $this->getSourceByToken($token);
        } else
        	// Is de header niet gevonden? Geef 'false' terug
            return false;
    }
    
    /**
     * Registreert het laaste signaal van deze bron
     *
     * @param int $sourceid
     * @return bool
     */
    public function updateSourceSignal(int $sourceid) {
        $sourceid = $this->database->real_escape_string($sourceid);
        $timestamp = time();
        return $this->database->query("UPDATE sources SET lastsignal = '$timestamp' WHERE sourceid = '$sourceid'");
    }

}