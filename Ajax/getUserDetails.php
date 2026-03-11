<?php
// Geen foutmeldingen tonen
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Dit mag alleen gebruikt worden door beheerders
$admintool = true;

// Vereisten laden
require_once(__dir__ . "/../secure/includes.php");

// AJAX authenticatie laden
require_once(__dir__ . "/../secure/auth_ajax.php");

if(isset($_GET['userid']) AND $_GET['userid'] != ""){
	// Is er een gebruiker opgegeven?
    $userid = $_GET['userid'];
    
    // Zoek gebruiker op
    $userOperations = new UserOperations($database);
    $userdetails = $userOperations->getUserDetails($userid);
    
    if($userdetails == false){
    	// Is de gebruiker niet gevonden? Toon 404 error
        http_response_code(404);
        exit();
    } else {
    	// Is de gebruiker gevonden? Toon details gebruiker in JSON
        echo json_encode($userdetails);
    }
} else {
	// Is er geen gebruiker opgegeven?  Toon 404 error
    http_response_code(404);
    exit();
}