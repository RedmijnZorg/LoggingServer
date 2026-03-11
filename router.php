<?php
/** 
Router -- alle verzoeken moeten via dit script geopend worden
**/

// Output buffering gebruiken om problemen met header location te voorkomen
ob_start();

// De router is gebruikt, dus die status registreren
$routerActive = true;

// De request URI bewaren
$request = $_SERVER['REQUEST_URI'];

// Vereisten laden
require_once "secure/includes.php";

// Routes laden
require_once "secure/routes.php";

// Crypto service loaden
$cryptoService = new CryptoService();
$cryptoService->setPrivateKeyLocation($config['crypto']['privatekey']);
$loggingService = new LoggingService($database);
$loggingService->loadCryptoService($cryptoService);


// Eventuele parameters strippen van de request URI
$getparams = explode("?", $request);
if(count($getparams) > 1) {
    $request = $getparams[0];
}

// Er is nog geen bijpassende route gevonden, dus op 'false' zetten
$routeMatched = false;

// Op basis van de bijpassende route wordt het bijpassende bestand geopend
$includefile = "";

// Is de request URI leeg? Gebruik de standaardroute
if($request == "") {
    $includefile = $defaultroute;
}

// Door publieke routes zoeken naar een match
foreach($publicRoutes as $routeRequest => $publicRoute) {
    if($routeRequest == $request) {
    
    	// Is een match gevonden? Kies het bijpassende bestand
        $routeMatched = true;
        $includefile = $publicRoute;
        break;
    }
}

// Is er nog geen match?
if($routeMatched == false) {
	// Door privéroutes zoeken naar een match
    foreach($privateRoutes as $routeRequest => $privateRoute) {
    	// Is een match gevonden? 
        if($routeRequest == $request) {
        	// Het is een privéroute, dus ook de authenticator laden voor toegangscontrole
            require_once "secure/auth.php";
            $routeMatched = true;
            // Kies het bijpassende bestand
            $includefile = $privateRoute;
            break;
        }
    }
}

// Er is nog geen sprake van een API route (zonder opmaak), dus op 'false' zetten
$apiRoute = false;

// Is er nog steeds geen match?
if($routeMatched == false) {
	// Door API routes zoeken naar een match
    foreach($apiRoutes as $routeRequest => $apiRoute) {
        if($routeRequest == $request) {
    		// Is een match gevonden? Kies het bijpassende bestand
            $routeMatched = true;
            $includefile = $apiRoute;
            
            // Er is sprake van een API route, dus op 'true' zetten
            $apiRoute = true;
            break;
        }
    }
}

// Is er een API route gevonden? 
if($apiRoute == true) {
	// Content veranderen naar JSON
    header("content-type: application/json");
    
    // Open het bijpassende bestand nu
    require_once($includefile);
    
    // Geen opmaak nodig, dus we kunnen stoppen
    exit();
}
?>
<!-- Start van HTML -->
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $config['app']['name'];?></title>
    <!-- Vereisten laden -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">
    
    <!-- scripts en stylesheets. Deze laden met een uniek timestamp om cachen te voorkomen -->
    <link rel="stylesheet" href="Assets/global.css?rand=<?php echo uniqid(time());?>">
    <link rel="stylesheet" href="Assets/app.css?rand=<?php echo uniqid(time());?>">
    <script src="Assets/jquery-3.7.1.min"></script>
    <script src="Assets/global.js?rand=<?php echo uniqid(time());?>"></script>
    <script src="Assets/app.js?rand=<?php echo uniqid(time());?>"></script>
    <script src="Assets/header.js?rand=<?php echo uniqid(time());?>"></script>
    <link rel="stylesheet" href="Assets/header.css?rand=<?php echo uniqid(time());?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<!-- De overlay als achtergrond voor meldingen -->
    <div id="overlay"></div>
    
    <!-- Container voor foutmeldingen -->
    <div class="error-container" id="errorbox" style="display: none;">
        <p class="message-title" id="errortitle"></p>
        <p id="errormessage"></p>
        <div class="buttons-container">
            <button type="button" class="button" onclick="hideErrorMessage()">
                OK
            </button>
        </div>
    </div>
    <?php 
    // Menu laden indien gebruiker is ingelogd en 2FA controle is gelukt
    if(isset($_SESSION['user']['userid']) AND $_SESSION['user']['2fapass'] == true) {
    ?>
		<!-- vereisten menu -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <script src="Assets/header.js?rand=<?php echo uniqid(time());?>"></script>
        <link rel="stylesheet" href="Assets/header.css?rand=<?php echo uniqid(time());?>">

		<!-- menu -->
        <header id="header">

            <div class="container">
                <div class="header_main">
                    <div class="header-left">
                        <div class="logo">
                            <img src="https://redmijnzorg.nl/wp-content/uploads/2025/01/Red-mijn-zorg-logo-2048x676.jpeg"
                                 alt="Logo">
                        </div>
                        <nav>
                            <div class="sidebar_top d-lg-none">
                                <div class="close">
                                    <i class="bi bi-x-lg"></i>
                                </div>
                            </div>
                            <ul>
                                <li><a href="/">Logboek</a></li>
                                <li><a href="/bronnen">Bronnen</a></li>
                                <li><a href="/gebruikers">Gebruikers</a></li>
                                <li><a href="/wijzigwachtwoord">Wachtwoord wijzigen</a></li>
                                <li><a href="/logout">Uitloggen</a></li>
                            </ul>
                        </nav>
                        <div class="overlay"></div>
                    </div>
                    <div class="header-right">
                        <div class="header__hamburger d-lg-none my-auto">
                            <div class="sidebar__toggle">
                                <i class="bi bi-list"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

    <?php } ?>


<?php
if($routeMatched == false OR $includefile == "") {
	// Is er geen passende route gevonden? Toon een 404 fout
    require_once $errorDocuments['404'];
} else {
	// Is er een passende route gevonden? Open deze nu
    require_once $includefile;
}

// Sessie vernieuwen indien gebruiker is ingelogd en 2FA controle is gelukt
if(isset($_SESSION['user']['userid']) AND $_SESSION['user']['2fapass'] == true) {
	echo "<script>sessionKeepAlive();</script>";
}
?>
</body>
</html>
