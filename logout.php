<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /logout");
}
ini_set('session.cookie_domain',$config['app']['cookiedomain']);

session_start();
session_destroy();
header("location: /login");
