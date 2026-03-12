<?php
// Configuratie laden
require_once(__dir__."/../secure/config.php");
require_once(__dir__."/../Class/CryptoService.php");

// Verbinden met database
$database = new mysqli($config['database']['host'],$config['database']['username'],$config['database']['password'],$config['database']['dbname']);
if($database->connect_errno){
    echo "Failed to connect to MySQL: " . $database->connect_error;
    exit();
}

// Formulier is ingevuld
if(isset($_POST['submit'])){

	// Controleren of mailadres is opgegeven
	if($_POST['email'] == "") {
			echo "geen mailadres opgegeven!";
			exit();
	}
	
	// Controleren of mailadres geldig is
	if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
       		echo "geen geldig mailadres opgegeven!";
			exit();
    }
    $cryptoService = new CryptoService();

	// Mailadres gereed maken voor database
    $email = $database->real_escape_string($cryptoService->encryptData($_POST['email']));
    
    // Tijdelijk wachtwoord genereren
    $password = $cryptoService->generatePassword();
    
    // Wachtwoord hashen
    $passwordhashed = hash("sha512", $password.$config['crypto']['salt']);

	// Gebruiker toevoegen, ontgrendelen en wachtwoord wijziging verplichten
    $adduser = $database->query("INSERT INTO `users` (`email`, `fullname`,`password`,`locked`,`changepassword`,`salted`) VALUES ('$email', 'Beheerder','$passwordhashed','0','0','1')");
    
    echo "<br>Inloggegevens:<br>";
    echo "<table>";

    echo "<tr>";
    echo "<th>E-mail</th>";
    echo "<td>".$_POST['email']."</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Wachtwoord</th>";
    echo "<td>".$password."</td>";
    echo "</tr>";
    echo "</table>";
    exit();
}

// Formulier gebruiker aanmaken
echo "<h1>Gebruiker aanmaken</h1>";
echo "<form method='post'>";
echo "<table>";
echo "<tr>";
echo "<th style='text-align: left;'>E-mail</th>";
echo "<td><input type='text' name='email' id='email'></td>";
echo "</tr>";
echo "<tr>";
echo "<th colspan='2'><input type='submit' name='submit' value='Doorgaan'></th>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "<br>";
echo "<br>";

