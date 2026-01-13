<?php
session_start();
if(isset($_GET['apptoken']) AND $_GET['apptoken'] != ""){
    $_SESSION['apptoken'] = $_GET['apptoken'];
}
if(!isset($_SESSION['user'])) {
   header("Location: /login");
    exit();
}
$userIDlogin = $_SESSION['user']['userid'];
$userOperations = new UserOperations($database);
$userDetails = $userOperations->getUserDetails($userIDlogin);
if(!isset($userDetails['userid'])) {
    header("Location: /login");
    exit();
}

if($request != "/wijzigwachtwoord" AND $_SESSION['user']['changepassword'] == 1) {
    header("Location: /wijzigwachtwoord");
    exit();
}

if($_SESSION['user']['changepassword'] != 1) {
    if ($request != "/koppelen" AND $_SESSION['user']['2fa'] == "") {
        header("Location: /koppelen");
        exit();
    }
    if($_SESSION['user']['2fa'] != "") {
        if ($_SESSION['user']['2fapass'] != true and $request != "/koppelen" and $request != "/controle") {
            header("Location: /controle");
            exit();
        }
    }
}
if($request != "/wijzigwachtwoord" and $request != "/koppelen" and $request != "/controle") {
    if (isset($_SESSION['apptoken'])) {
        $loginOperations = new LoginOperations($database);
        $addLoginToken = $loginOperations->setLoginToken($userIDlogin);
        $appManager = new AppManager($database);
        $appDetails = $appManager->getAppByToken($_SESSION['apptoken']);

        echo "<a href='".$appDetails['callback']."?logintoken=".$addLoginToken."'>Doorgaan</a>";
        header("Location: ".$appDetails['callback']."?logintoken=".$addLoginToken);
        exit();
    }
}