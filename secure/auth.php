<?php
/** Authenticatie **/

// Sessie starten
session_start();

// Is er een app token meegestuurd? Bewaar die token in de sessie
if(isset($_GET['apptoken']) AND $_GET['apptoken'] != ""){
    $_SESSION['apptoken'] = $_GET['apptoken'];
}

// Is de gebruiker niet ingelogd? Stop en stuur naar loginpagina
if(!isset($_SESSION['user'])) {
   header("Location: /login");
    exit();
}

// Gegvens van gebruiker ophalen
$userIDlogin = $_SESSION['user']['userid'];
$userOperations = new UserOperations($database);
$userOperations->loadCryptoService($cryptoService);
$userDetails = $userOperations->getUserDetails($userIDlogin);

// Is de gebruiker niet bekend of verwijderd? Stop en stuur naar loginpagina
if(!isset($userDetails['userid'])) {
    header("Location: /login");
    exit();
}

// Moet de gebruiker zijn wachtwoord wijzigen? Stop en stuur naar de wijzigingspagina. Negeer dit op de wijzigingspagina
if($request != "/wijzigwachtwoord" AND $_SESSION['user']['changepassword'] == 1) {
    header("Location: /wijzigwachtwoord");
    exit();
}

// Hoeft de gebruiker zijn wachtwoord niet te wijzigen? Ga dan door
if($_SESSION['user']['changepassword'] != 1) {
	// Heeft de gebruiker geen 2FA secret? Stop en stuur naar de 2FA instelpagina. Negeer dit op de instelpagina
    if ($request != "/koppelen" AND $_SESSION['user']['2fa'] == "") {
        header("Location: /koppelen");
        exit();
    }
    
    // Heeft de gebruiker een 2FA secret, maar heeft de 2FA controle niet afgerond? Stop en stuur naar de 2FA controlepagina
    // Negeer dit op de instelpagina en controlepagina
    if($_SESSION['user']['2fa'] != "") {
        if ($_SESSION['user']['2fapass'] != true and $request != "/koppelen" and $request != "/controle") {
            header("Location: /controle");
            exit();
        }
    }
}
