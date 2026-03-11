<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /wijzigwachtwoord");
    exit();
}

// Classes laden
$loginOperations = new LoginOperations($database);
$userOperations = new UserOperations($database);
?>
<!-- Formulier voor het wijzigen van het wachtwoord -->
<form method="post">
    <div class="message-container" style="height: 400px; z-index: -1;">
        <p class="message-title">Wachtwoord wijzigen</p>
        <div class="input-container">
            <input type="password" id='currentpassword' name="currentpassword" placeholder="Huidig wachtwoord">
        </div>
        <div class="input-container">
            <input type="password" name="newpassword" placeholder="Nieuw wachtwoord">
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
if(isset($_POST["submit"])) {
	// Het huidige wachtwoord controleren door te proberen in te loggen
    $login = $loginOperations->login($_SESSION['user']['email'], $_POST["currentpassword"]);

	// Is inloggen mislukt? Dan is het huidig wachtwoord onjuist
    if ($login == false) {
    	// toon foutmelding
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Huidig wachtwoord onjuist!');</script>";
    } else {
    	// Inloggen is gelukt. Komen de nieuwe wachtwoorden overeen?
        if ($_POST["newpassword"] == $_POST["newpassword2"]) {
        	// Witruimte verwijderen en controleren op kleine letters, hoofdletters en een minimale lengte van 8 karakters
            if (trim($_POST["newpassword"], 'a..z') != '' && trim($_POST["newpassword"], 'A..Z') != '' && strlen($_POST["newpassword"]) >= 8) {
            	// Voldoet het wachtwoord? Sla deze dan op
                $changepassword = $userOperations->changePassword($_SESSION['user']['userid'], $_POST["newpassword"]);
                
                if ($changepassword == true) {
                	// Is het gelukt? Verwijder de verplichting voor het veranderen van het wachtwoord in de sessie
                    $_SESSION["user"]['changepassword'] = 0;
                }
                
                // Log de gebeurtenis
                $loggingService->logEvent($_SESSION['user']['email'], $_SERVER['REQUEST_URI'], "account", "Password change for ".$_SESSION['user']['email']." success", 1);

				// Ga naar de startpagina
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
}
?>
<script>
// Huidig wachtwoord activeren
$('#currentpassword').focus();
</script>
