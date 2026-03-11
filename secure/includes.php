<?php
// Class autoloader
spl_autoload_register(function ($class_name) {
    include __dir__."/../Class/".$class_name . '.php';
});

// Configuratie laden
require_once("config.php");

// Verbinden met database
require_once("db_connect.php");
