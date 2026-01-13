<?php
$database = new mysqli($config['database']['host'],$config['database']['username'],$config['database']['password'],$config['database']['dbname']);
if($database->connect_errno){
    echo "Failed to connect to MySQL: " . $database->connect_error;
    exit();
}