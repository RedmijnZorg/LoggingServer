<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /resetrequest");
    exit();
}
$userOperations = new UserOperations($database);

?>
<form method="post">
    <div class="message-container" style="height: 280px">
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
if(isset($_POST["submit"])){
    $finduser = $userOperations->findUserByEmail($_POST["email"]);
    if($finduser) {
        $resettoken = $userOperations->assignResetToken($finduser);

        if($resettoken) {
            $loggingService->logEvent($_POST["email"], $_SERVER['REQUEST_URI'], "account", "Assigning reset token ".$resettoken, 1);
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
            if($emailcontents == false) {
                echo "<script>showErrorMessage('Fout','Er is een fout opgetreden!');</script>";
                exit();
            }

            $mailerService->setMailbody($emailcontents);
            $mailerService->sendHTML();
        }
    }
    echo "<script type='text/javascript'>setErrorMessage('successmessage','Als dit e-mailadres bij ons bekend is, zal u de herstelinstructies ontvangen in uw mailbox.');</script>";
}
?>


