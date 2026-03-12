<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /koppelen");
    exit();
}

// Classes laden
$loginOperations = new LoginOperations($database);
$loginOperations->loadCryptoService($cryptoService);
$userOperations = new UserOperations($database);
$userOperations->loadCryptoService($cryptoService);
$googleAuthenticator = new GoogleAuthenticator();

/** 
Is er al een authenticator secret gegenereerd? 
Dit gebeurt wanneer iemand de code onjuist heeft ingevoerd.
We willen voorkomen dat er een nieuwe secret wordt gegenereerd, omdat de gebruiker
dan een nieuw account moet instellen in de authenticator app.
**/
if(isset($_POST['secret']) AND $_POST['secret'] != "") {
	// Secret overnemen van formulier
    $newsecret = $_POST['secret'];
} else {
	// Nieuw secret genereren
    $newsecret = $googleAuthenticator->createSecret();
}

// QR-code genereren op basis van het gegenereerde secret
$qrcode = $googleAuthenticator->getQRCodeGoogleUrl($config['app']['name'],$newsecret);
?>
<!-- Formulier voor 2FA registratie en controle -->
<form method="post" id="twofaform">
    <input type="hidden" name='secret' value="<?php echo $newsecret; ?>">
    <input type="hidden" name='submitform' value="1">
    <div class="message-container" style='z-index: -1;'>
        <p class="message-title">Authenticator instellen</p>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;">Scan de volgende QR code of voer de configuratiecode in in uw Authenticator app. Voer vervolgens de 6-cijferige code uit de Authenticator app in ter controle</div>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;"><img src="<?php echo $qrcode ?>"/></div>
        <div class="input-container" style="text-align: center;">
            <b>Configuratiecode</b>
        </div>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;"><?php echo $newsecret;?></div>
        <div class="input-container" style="text-align: center;">
            <div id="twofaboxes">
                <input type="text" class="twofabox" maxlength="1" id="twofabox1" name="response[0]" onkeyup="$('#twofabox2').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox2" name="response[1]" onkeyup="$('#twofabox3').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox3" name="response[2]" onkeyup="$('#twofabox4').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox4" name="response[3]" onkeyup="$('#twofabox5').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox5" name="response[4]" onkeyup="$('#twofabox6').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox6" name="response[5]" onkeyup="$('#twofaform').submit();">
            </div>
        </div>
        <button type="button" onclick="$('#twofaform').submit();" class="submit">
            Controleren
        </button>

        <span class="errorinline" id="errorinline"></span>

    </div>
</form>

<?php
// Gebruiker moet na het toevoegen in de authenticator app de responscode invoeren
if(isset($_POST["submitform"])){
	// Is er geen secret gegenereerd? Dan gaat iets mis, en kunnen we niet verder
	if($_POST["secret"] == "") {
		echo "<script type='text/javascript'>setErrorMessage('errorinline','Er ging iets fout!');</script>";
		exit();
	}
   
	// Is er geen responscode ingevuld? Toon dan een foutmelding
    if(!isset($_POST["response"])) {
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Voer de code in die u ziet in uw Authenticator app!');</script>";
        exit();
    }
    
    // Controleer de responscode met het gegenereerde secret
    $verifyCode = $googleAuthenticator->verifyCode($_POST["secret"], implode($_POST["response"]));
    
    // Is de responscode juist? Dan is de app goed toegevoegd en kunnen we dit opslaan
    if($verifyCode){
    	// Secret bewaren voor deze gebruiker
        $userOperations->setUser2FASecret($_SESSION["user"]["userid"], $_POST["secret"]);
        // In de sessie registreren dat de 2FA controle is gelukt
        $_SESSION["user"]['2fapass'] = true;
        $_SESSION['user']['2fa'] = $_POST["secret"];
        // Doorsturen naar startpagina
        header("location: /");
    } else {
    	// Is de responscode onjuist? Toon dan een foutmelding
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Code is onjuist!');</script>";
        exit();
    }
}
?>
<script>
// Eerste tekstvakje activeren
$('#twofabox1').focus();
</script>