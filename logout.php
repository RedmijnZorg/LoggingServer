<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /logout");
}

// Cookiedomein instellen
ini_set('session.cookie_domain',$config['app']['cookiedomain']);

// Sessie starten
session_start();

// Laatste locatie bewaren
$returnURL = "";
if(isset($_SESSION['app']['requesturl']) AND  $_SESSION['app']['requesturl'] != "" AND $request != "/wijzigwachtwoord" AND $request != "/controle" AND $request != "/koppelen") {
    $returnURL = $_SESSION['app']['requesturl'];
}

// Sessie vernietigen
session_destroy();

// Is de laatste locatie bekend? Ga daar naar terug. Anders naar de login pagina
if($returnURL != "") {
    header("Location: ".$returnURL);
} else {
    header("location: /login");
}
