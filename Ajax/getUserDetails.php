<?php
ini_set('display_errors', 0);
header('Content-Type: application/json');
$admintool = true;
require_once(__dir__."/../secure/includes.php");
require_once(__dir__."/../secure/auth_ajax.php");
if(isset($_GET['userid']) AND $_GET['userid'] != ""){
    $userid = $_GET['userid'];
    $userOperations = new UserOperations($database);
    $userdetails = $userOperations->getUserDetails($userid);
    if($userdetails == false){
        http_response_code(404);
        exit();
    } else {
        echo json_encode($userdetails);
    }
} else {
    http_response_code(404);
    exit();
}