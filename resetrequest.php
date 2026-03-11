<?php
// Wordt dit script direct geopend zonder router? Redirect dan naar de juiste pagina.
if(!isset($routerActive)) {
    ob_start();
    header("location: /resetrequest");
    exit();
}

// Classes laden
$userOperations = new UserOperations($database);
?>
<!-- herstelformulier -->
<form method="post">
    <div class="message-container" style="height: 280px;  z-index: -1;">
        <p class="message-title">Account herstellen</p>
        <div class="input-container">
            <input type="email" name="email" placeholder="E-mailadres">
            <span>
          </span>
        </div>
        <button type="submit" name="submit" class="submit">
            Nieuw wachtwoord aanvragen
        </button>
        <span class="successmessage" id="successmessage"></span>

    </div>
</form>

<?php
// Formulier is ingevuld
if(isset($_POST["submit"])){

	// Controleer of er een gebruiker met dit mailadres bestaat
    $finduser = $userOperations->findUserByEmail($_POST["email"]);
    
    // Is de gebruiker gevonden?
    if($finduser) {
    
    	// Reset token toewijzen
        $resettoken = $userOperations->assignResetToken($finduser);

		// Is een reset token toegewezen?
        if($resettoken) {
            // Genereer een e-mail
            $renderService = new RenderService();
            $renderService->setTitle('Wachtwoord opnieuw instellen');
            $mailerService = new MailerService();
            $mailerService->setFromAddress($config['email']['from']);
            $mailerService->setSubject("Wachtwoord opnieuw instellen");
            $mailerService->setToAddress($_POST["email"]);
            $emailbody = "U heeft een nieuw wachtwoord aangevraagd. Klik op onderstaande knop om verder te gaan.";
            $renderService->setContenttop( "U heeft een nieuw wachtwoord aangevraagd. Klik op onderstaande knop om verder te gaan.");
            $renderService->setContentbottom("Heeft u dit niet aangevraagd? Dan kunt u deze mail negeren");
            $renderService->setButtonvalue("Wachtwoord herstellen");
            $renderService->setbuttonurl($config['app']['hostname']."/reset?token=".$resettoken);
            $renderService->setFootertext($config['app']['name']);
            $emailcontents = $renderService->renderMail(true);
            
            // Is er geen e-mail gegenereerd? Dan gaat er iets mis. Toon foutmelding en stop meteen
            if($emailcontents == false) {
                echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
                exit();
            }

			// Is de e-mail wel gegenereerd? Stuur deze dan naar de gebruiker
            $mailerService->setMailbody($emailcontents);
            $mailerService->sendHTML();
        }
    }
    
    // Bevestiging tonen, ongeacht dit wel of niet is gelukt om te voorkomen dat we teveel informatie aan kwaadwillenden geven
    echo "<script type='text/javascript'>setErrorMessage('successmessage','Als dit e-mailadres bij ons bekend is, zal u de herstelinstructies ontvangen in uw mailbox.');</script>";
}
?>

