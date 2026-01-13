<?php
$publicRoutes = array();
$privateRoutes = array();
$noHTMLRoutes = array();

$defaultroute = "index.php"; // de standaard route indien geen pad opgegeven

/**
 * Foutpagina's opgeven
 */
$errorDocuments['404'] = "ErrorDocuments/404.php";

/**
 * Publieke routes opgeven, die zijn beschikbaar zonder inloggen
 */
$publicRoutes["/login"] = "login.php";
$publicRoutes["/logout"] = "logout.php";
$publicRoutes["/reset"] = "resetpassword.php";
$publicRoutes["/resetrequest"] = "resetrequest.php";

/**
 * Privéroutes opgeven, die zijn alleen beschikbaar bij inloggen
 */
$privateRoutes["/bronnen"] = "sourcemanager.php";
$privateRoutes["/gebruikers"] = "usermanager.php";
$privateRoutes["/wijzigwachtwoord"] = "changepassword.php";
$privateRoutes["/koppelen"] = "2faconfiguration.php";
$privateRoutes["/controle"] = "verify2fa.php";
$privateRoutes["/loadapp"] = "loadApp.php";
$privateRoutes["/"] = "index.php";


/**
 * API routes
 */
$apiRoutes["/ajax/getuserdetails"] = "Ajax/getUserDetails.php";
$apiRoutes["/api/receiver"] = "API/logsReceiver.php";
