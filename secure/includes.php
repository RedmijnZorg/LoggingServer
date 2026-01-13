<?php
spl_autoload_register(function ($class_name) {
    include __dir__."/../Class/".$class_name . '.php';
});
require_once("config.php");
require_once("db_connect.php");
