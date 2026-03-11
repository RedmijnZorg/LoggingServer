<html>
<head>
</head>
<body>
<!-- configuratie formulier -->
<h1>Configuratie</h1>
<form method="post">
    <b>Database</b>
    <table>
        <tr>
            <th style="text-align: left;">Hostnaam</th>
            <td><input type="text" name="dbhost" value="<?php if(isset($_POST['dbhost'])) { echo $_POST['dbhost']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Database naam</th>
            <td><input type="text" name="dbname" value="<?php if(isset($_POST['dbname'])) { echo $_POST['dbname']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Gebruikersnaam</th>
            <td><input type="text" name="dbuser" value="<?php if(isset($_POST['dbuser'])) { echo $_POST['dbuser']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Wachtwoord</th>
            <td><input type="text" name="dbpass" value="<?php if(isset($_POST['dbpass'])) { echo $_POST['dbpass']; }?>"></td>
        </tr>
    </table>
    <b>App</b>
    <table>
        <tr>
            <th style="text-align: left;">App naam</th>
            <td><input type="text" name="appname" value="<?php if(isset($_POST['appname'])) { echo $_POST['appname']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Logo pad</th>
            <td><input type="text" name="applogo" value="<?php if(isset($_POST['applogo'])) { echo $_POST['applogo']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Hostname / URL</th>
            <td><input type="text" name="apphostname" value="<?php if(isset($_POST['apphostname'])) { echo $_POST['apphostname']; }?>"></td>
        </tr>
        <tr>
            <th style="text-align: left;">Bron e-mail notificaties</th>
            <td><input type="text" name="mailfrom" value="<?php if(isset($_POST['mailfrom'])) { echo $_POST['mailfrom']; }?>"></td>
        </tr>
         <tr>
            <th style="text-align: left;">Bewaartermijn logs</th>
            <td><input type="text" name="logretention" value="<?php if(isset($_POST['logretention'])) { echo $_POST['logretention']; }?>"></td>
        </tr>
    </table>
    <input type="submit" name="setconfig" value="Doorgaan">
</form>
<?php
// Formulier is ingevuld
if(isset($_POST['setconfig'])) {

	// Wachtwoord salt genereren
	require_once(__dir__."/../Class/CryptoService.php");
	$cryptoService = new CryptoService();
	$pwsalt = $cryptoService->generateUUID();

	// Gegevens database opslaan
    if(!isset($_POST['dbhost']) || empty($_POST['dbhost'])) {
        echo "Geef een database hostname op!";
        exit();
    }
    if(!isset($_POST['dbuser']) || empty($_POST['dbuser'])) {
        echo "Geef een database gebruikersnaam op!";
        exit();
    }
    if(!isset($_POST['dbpass']) || empty($_POST['dbpass'])) {
        echo "Geef een database wachtwoord op!";
        exit();
    }

    if(!isset($_POST['dbname']) || empty($_POST['dbname'])) {
        echo "Geef een database naam op!";
        exit();
    }

	// Proberen te verbinden met database voor bootstrapping
    $connectionAttempt = new mysqli($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'],$_POST['dbname']);
    
    // Lukt de verbinding niet? Stop met een foutmelding
    if($connectionAttempt->connect_error) {
        echo "Kon niet met de database verbinden!";
        exit();
    }

	// Vereiste velden controleren
    if(!isset($_POST['appname']) || empty($_POST['appname'])) {
        echo "Geef een naam op voor deze app!";
        exit();
    }

    if(!isset($_POST['applogo']) || empty($_POST['applogo'])) {
        echo "Geef een logo pad op voor deze app!";
        exit();
    }

    if(!isset($_POST['apphostname']) || empty($_POST['apphostname'])) {
        echo "Geef een hostname op voor deze app!";
        exit();
    }

    if(!isset($_POST['mailfrom']) || empty($_POST['mailfrom'])) {
        echo "Geef een bron mailadres op voor deze app!";
        exit();
    }
    
    if(!isset($_POST['logretention']) || empty($_POST['logretention'])) {
        $_POST['logretention'] = 0;
    }
    
    // Parameters gereed maken voor de database
 	$logretention = $connectionAttempt->real_escape_string($_POST['logretention']);
 	
 	// Configuratie legen
    $connectionAttempt->query("TRUNCATE `configuration`");
	
	// Configuratie vullen
    $connectionAttempt->query("INSERT INTO `configuration` (`item`,`value`) VALUES ('LOGGING_RETENTION_DAYS','$logretention')");

	// Configuratiebestand invullen
    $template = file_get_contents("configtemplate");
    $template = str_replace("DATABASE_HOSTNAME",$_POST['dbhost'],$template);
    $template = str_replace("DATABASE_USERNAME",$_POST['dbuser'],$template);
    $template = str_replace("DATABASE_PASSWORD",$_POST['dbpass'],$template);
    $template = str_replace("DATABASE_NAME",$_POST['dbname'],$template);
    $template = str_replace("APP_NAME",$_POST['appname'],$template);
    $template = str_replace("APP_LOGO",$_POST['applogo'],$template);
    $template = str_replace("APP_HOSTNAME",$_POST['apphostname'],$template);
    $template = str_replace("EMAIL_FROM",$_POST['mailfrom'],$template);
    $template = str_replace("CRYPTO_SALT",$pwsalt,$template);
    
    file_put_contents(__dir__."/../secure/config.php",$template);
    
    // Doorgaan naar het genereren van de sleutel
    header("location: /Install/genkey.php");
}
?>
</body>
</html>

