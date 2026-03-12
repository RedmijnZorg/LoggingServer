<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /reset");
    exit();
}

// Wachtwoord resetten kan alleen met reset token? Is deze niet opgegeven? Stop met uitvoeren.
if(!isset($_GET['token'])) {
    echo "Ongeldige code!";
    exit();
}

// Classes laden
$loginOperations = new LoginOperations($database);
$userOperations = new UserOperations($database);
$loginOperations->loadCryptoService($cryptoService);
$userOperations->loadCryptoService($cryptoService);
$loginOperations->setSalt($config['crypto']['salt']);
$userOperations->setSalt($config['crypto']['salt']);

// Gebruiker zoeken via reset token
$userDetails = $userOperations->getUserByResetToken($_GET['token']);

// Is de gebruiker niet gevonden? Log gebeurtenis en stop met uitvoeren
if($userDetails == false) {
    echo "Ongeldige code!";
    exit();
}
?>
<!-- wijzigignsformulier -->
<form method="post">
    <div class="message-container" style="height: 350px; z-index: -1;">
        <p class="message-title">Wachtwoord wijzigen</p>
        <div class="input-container">
            <input type="password" id='newpassword' name="newpassword" placeholder="Nieuw wachtwoord">
        </div>
        <div class="input-container">
            <input type="password" name="newpassword2" placeholder="Nieuw wachtwoord nogmaals">
        </div>
        <button type="submit" name="submit" class="submit">
            Wachtwoord wijzigen
        </button>

        <span class="errorinline" id="errorinline"></span>

    </div>
</form>

<?php
// Formulier is ingevuld
if (isset($_POST["submit"])) {
   // Komen de nieuwe wachtwoorden overeen?
   if ($_POST["newpassword"] == $_POST["newpassword2"]) {
       // Witruimte verwijderen en controleren op kleine letters, hoofdletters en een minimale lengte van 8 karakters
       if (trim($_POST["newpassword"], 'a..z') != '' && trim($_POST["newpassword"], 'A..Z') != '' && strlen($_POST["newpassword"]) >= 8) {
            	// Voldoet het wachtwoord? Sla deze dan op
                $changepassword = $userOperations->changePassword($userDetails['userid'], $_POST["newpassword"]);
                
                // Doorsturen naar startpagina
                header("location: /");
            } else {
            	// Voldoet het wachtwoord niet? toon foutmelding
                echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoord moet 8 tekens of meer bevatten, met minstens een kleine letter en een hoofdletter!');</script>";
            }
            // Komen de nieuwe wachtwoorden niet overeen?
        } else {
    		// toon foutmelding
            echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoorden komen niet overeen!');</script>";
        }
}
?>
<script>
// Nieuw wachtwoord #1 activeren
$('#newpassword').focus();
</script>