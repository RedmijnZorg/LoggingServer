<?php
ini_set('display_errors', 0);
session_start();
$userIDlogin = $_SESSION['user']['userid'];
$userOperations = new UserOperations($database);
$userDetails = $userOperations->getUserDetails($userIDlogin);
if(!isset($userDetails['userid'])) {
    http_response_code(403);
    exit();
}

if($_SESSION['user']['changepassword'] == 1) {
    http_response_code(403);
    exit();
}

if($_SESSION['user']['2fapass'] != true) {
    http_response_code(403);
    exit();
}
