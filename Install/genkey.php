<?php
echo "Privésleutel genereren...";
$keys = openssl_pkey_new(array("private_key_bits" => 4096,"private_key_type" => OPENSSL_KEYTYPE_RSA));
$public_key_pem = openssl_pkey_get_details($keys)['key'];
openssl_pkey_export($keys, $private_key_pem);
if($private_key_pem == null) {
    echo "Kon geen sleutel genereren! Controleer of openSSL is ingeschakeld voor PHP.";
    exit();
}
file_put_contents(__dir__."/../secure/privatekey.pem", $private_key_pem);
echo "<br>Installatie afgerond! Login is admin/admin. Verwijder a.u.b. de Install map";