<?php
header('Content-type: application/json');
ini_set('display_errors', 0);
require_once(__dir__."/../secure/includes.php");

$sourceManager = new SourceManager($database);
$sourceDetails = $sourceManager->getSourceFromHeaders();
if($sourceDetails == false) {
    http_response_code(403);
    echo json_encode(array("error" => "App token invalid."));
    exit();
}

$logdata = file_get_contents('php://input');
if($logdata == "") {
    http_response_code(500);
    echo json_encode(array("error" => "Contents missing."));
    exit();
}
$logarray = json_decode($logdata);
$passedarray = array();
$failedarray = array();
$processedamount = 0;
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
				if($additem) {
						$passedarray[]['logID'] = $log->logID;
					} else {
						$passedarray[]['logID'] = $log->logID;
				}
				$processedamount++;
}
$returnArray = array(
	'amountProcessed' => $processedamount, 
	'amountPassed' => count($passedarray),
	'amountFailed' => count($failedarray),
	'logsPassed' => $passedarray, 
	'logsFailed' => $failedarray
	);
echo json_encode($returnArray);