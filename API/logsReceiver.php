<?php
// Geen foutmeldingen tonen
header('Content-type: application/json');
ini_set('display_errors', 0);

// Vereisten laden
require_once(__dir__."/../secure/includes.php");

// Bijbehorende bron opzoeken
$sourceManager = new SourceManager($database);
$loggingService = new LoggingService($database);
$loggingService->loadCryptoService($cryptoService);
$sourceDetails = $sourceManager->getSourceFromHeaders();
if($sourceDetails == false) {
	// Is de bron niet gevonden? Dan is de source token ongeldig.
    http_response_code(403);
    echo json_encode(array("error" => "Source token invalid."));
    exit();
}

// Payload van log client openen
$logdata = file_get_contents('php://input');
if($logdata == "") {
	// Is de payload leeg? Stop met code 500
    http_response_code(500);
    echo json_encode(array("error" => "Contents missing."));
    exit();
}

// Payload openen in json
$logarray = json_decode($logdata);

// Gelukte items
$passedarray = array();

// Mislukte items
$failedarray = array();

// Verwerkte items
$processedamount = 0;

// Signaal van bron bijwerken
$sourceManager->updateSourceSignal($sourceDetails['sourceid']);
// Ieder item verwerken
foreach($logarray as $log) {
		    $additem = $loggingService->logEvent(
		    	$sourceDetails['sourceid'], 
		    	$log->timestamp, 
		    	$log->ipAddress, 
		    	$log->userAgent, 
		    	$log->referrer,
		    	$log->username, 
		    	$log->page, 
		    	$log->event, 
		    	$log->data, 
		    	$log->pass);
		    	
		    	// Voeg het item toe aan gelukte of mislukte items op basis van resultaat
				if($additem) {
						$passedarray[]['logID'] = $log->logID;
					} else {
						$failedarray[]['logID'] = $log->logID;
				}
				$processedamount++;
}

// Array maken van resultaten
$returnArray = array(
	'amountProcessed' => $processedamount, 
	'amountPassed' => count($passedarray),
	'amountFailed' => count($failedarray),
	'logsPassed' => $passedarray, 
	'logsFailed' => $failedarray
	);
	
// Resultaten tonen in JSON
echo json_encode($returnArray);