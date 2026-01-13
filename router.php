<?php
ob_start();
session_start();
$routerActive = true;
$request = $_SERVER['REQUEST_URI'];

require_once "secure/includes.php";
require_once "secure/routes.php";

$cryptoService = new CryptoService();
$cryptoService->setPrivateKeyLocation($config['crypto']['privatekey']);
$loggingService = new LoggingService($database);
$loggingService->loadCryptoService($cryptoService);

$getparams = explode("?", $request);
if(count($getparams) > 1) {
    $request = $getparams[0];
}

$routeMatched = false;

$includefile = "";
if($request == "") {
    $includefile = $defaultroute;
}

foreach($publicRoutes as $routeRequest => $publicRoute) {
    if($routeRequest == $request) {
        $routeMatched = true;
        $includefile = $publicRoute;
        break;
    }
}

if($routeMatched == false) {
    foreach($privateRoutes as $routeRequest => $privateRoute) {
        if($routeRequest == $request) {
            $routeMatched = true;
            require_once("secure/auth.php");
            $includefile = $privateRoute;
            break;
        }
    }
}
$apiRoute = false;

if($routeMatched == false) {
    foreach($apiRoutes as $routeRequest => $apiRoute) {
        if($routeRequest == $request) {
            $routeMatched = true;
            $includefile = $apiRoute;
            $apiRoute = true;
            break;
        }
    }
}
?>
<?php
if($apiRoute == true) {
    if(file_exists($includefile)) {
        require_once($includefile);
        exit();
    } else {
        require_once $errorDocuments['404'];
        exit();
    }
}
?>
<!DOCTYPE HTML>
<html lang="nl">
<head>
    <meta charset=UTF-8>
    <title><?php echo $config['app']['name'];?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Assets/global.css?rand=<?php echo uniqid(time());?>">
    <link rel="stylesheet" href="Assets/app.css?rand=<?php echo uniqid(time());?>">
    <script src="Assets/jquery-3.7.1.min"></script>
     <script src="Assets/header.js?rand=<?php echo uniqid(time());?>"></script>
    <link rel="stylesheet" href="Assets/header.css?rand=<?php echo uniqid(time());?>">
    <script src="Assets/global.js?rand=<?php echo uniqid(time());?>"></script>
    <script src="Assets/app.js?rand=<?php echo uniqid(time());?>"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div id="overlay"></div>
    <div class="error-container" id="errorbox" style="display: none;">
        <p class="message-title" id="errortitle"></p>
        <p id="errormessage"></p>
        <div class="buttons-container">
            <button type="button" class="button" onclick="hideErrorMessage()">
                OK
            </button>
        </div>
    </div>
    <?php if(isset($_SESSION['user']['userid']) AND $_SESSION['user']['2fapass'] == true) {?>

 <header id="header">

        <div class="container">
            <div class="header_main">
                <div class="header-left">
                    <nav>
                        <div class="sidebar_top d-lg-none">
                            <div class="close">
                                <i class="bi bi-x-lg"></i>
                            </div>
                        </div>
                        <ul>
							<li><a href='/'>Logs</a></li>
							<li><a href='/bronnen'>Bronnen</a></li>
							<li><a href='/gebruikers'>Gebruikers</a></li>
							<li><a href='/logout'>Uitloggen</a></li>
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
    <form method='post' id='loginform'>
    	<input type='hidden' name='login' value='1'>
    </form>
<?php
if(isset($_POST['login'])) {
	$appConfiguration = new AppConfiguration($database);
    $apikey = $appConfiguration->getConfiguration("APIKEY_ACCOUNTSERVER");

	$_SESSION['app']['requesturl'] = $request;
    if(isset($_GET)) {
        $getparams = "";
        foreach($_GET as $key => $value) {
            if($getparams != "") {
                $getparams .= "&".urlencode($key)."=".urlencode($value);
            } else {
                $getparams = "?".urlencode($key)."=".urlencode($value);
            }
        }
        $_SESSION['app']['requesturl'].= $getparams;
    }
    
    header("Location: ".$config['accountserver']['url']."?apptoken=".$apikey);
    echo "<a href='".$config['accountserver']['url']."?apptoken=".$apikey."'>Ga verder</a>";
    exit();
}

if($routeMatched == false OR $includefile == "" OR !file_exists($includefile)) {
    require_once $errorDocuments['404'];
} else {
    require_once $includefile;
}
?>

</body>
</html>
