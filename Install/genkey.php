<?php
echo "Privésleutel genereren...";

// Genereren van een private key voor versleuteling
$keys = openssl_pkey_new(array("private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA));
$public_key_pem = openssl_pkey_get_details($keys)['key'];
openssl_pkey_export($keys, $private_key_pem);

// Lukt het niet? Stop met een foutmelding
if($private_key_pem == null) {
    echo "Kon geen sleutel genereren! Controleer of openSSL is ingeschakeld voor PHP.";
    exit();
}

// Private key bewaren
file_put_contents(__dir__."/../secure/privatekey.pem", $private_key_pem);

// Doorgaan naar het aanmaken van de gebruiker
header("location: /Install/createuser.php");