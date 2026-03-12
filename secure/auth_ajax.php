<?php
/** Authenticatie voor AJAX **/

// Geen foutmeldingen tonen
ini_set('display_errors', 0);

// Sessie starten
session_start();

// Gegevens van gebruiker ophalen
$userIDlogin = $_SESSION['user']['userid'];
$userOperations = new UserOperations($database);
$userOperations->loadCryptoService($cryptoService);
$userDetails = $userOperations->getUserDetails($userIDlogin);

// Is deze gebruiker niet ingelogd of verwijderd? Stop met 403 error
if(!isset($userDetails['userid'])) {
    http_response_code(403);
    exit();
}

// Moet deze gebruiker zijn wachtwoord eerst wijzigen? Stop met 403 error
if($_SESSION['user']['changepassword'] == 1) {
    http_response_code(403);
    exit();
}

// Heeft deze gebruiker geen 2FA controle afgelegd? Stop met 403 error
if($_SESSION['user']['2fapass'] != true) {
    http_response_code(403);
    exit();
}