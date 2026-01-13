<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /koppelen");
    exit();
}
$loginOperations = new LoginOperations($database);
$userOperations = new UserOperations($database);
$googleAuthenticator = new GoogleAuthenticator();
if(isset($_POST['secret']) AND $_POST['secret'] != "") {
    $newsecret = $_POST['secret'];
} else {
    $newsecret = $googleAuthenticator->createSecret();
}
$qrcode = $googleAuthenticator->getQRCodeGoogleUrl($config['app']['name'],$newsecret);

?>
<form method="post" id="twofaform">
    <input type="hidden" name='secret' value="<?php echo $newsecret; ?>">
    <input type="hidden" name='submitform' value="1">
    <div class="message-container" style="height: 650px; width: 480px;">
        <p class="message-title">Authenticator instellen</p>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;">Scan de volgende QR code of voer de configuratiecode in in uw Authenticator app. Voer vervolgens de 6-cijferige code uit de Authenticator app in ter controle</div>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;"><img src="<?php echo $qrcode ?>"/></div>
        <div class="input-container" style="text-align: center;">
            <b>Configuratiecode</b>
        </div>
        <div class="input-container" style="padding-bottom: 15px; text-align: center;"><?php echo $newsecret;?></div>
        <div class="input-container" style="text-align: center;">
            <div id="twofaboxes">
                <input type="text" class="twofabox" maxlength="1" id="twofabox1" name="response[0]" onkeyup="document.getElementById('twofabox2').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox2" name="response[1]" onkeyup="document.getElementById('twofabox3').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox3" name="response[2]" onkeyup="document.getElementById('twofabox4').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox4" name="response[3]" onkeyup="document.getElementById('twofabox5').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox5" name="response[4]" onkeyup="document.getElementById('twofabox6').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox6" name="response[5]" onkeyup="document.getElementById('twofaform').submit();">
            </div>
        </div>
        <button type="button" onclick="document.getElementById('twofaform').submit();" class="submit">
            Controleren
        </button>

        <span class="errorinline" id="errorinline"></span>

    </div>
</form>

<?php
if(isset($_POST["submitform"])){
   if($_POST["secret"] == "") {
       echo "<script type='text/javascript'>setErrorMessage('errorinline','Er ging iets fout!');</script>";
       exit();
   }
    if(!isset($_POST["response"])) {
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Voer de code in die u ziet in uw Authenticator app!');</script>";
        exit();
    }
    $verifyCode = $googleAuthenticator->verifyCode($_POST["secret"], implode($_POST["response"]));
    if($verifyCode){
        $userOperations->setUser2FASecret($_SESSION["user"]["userid"], $_POST["secret"]);
        $_SESSION["user"]['2fapass'] = true;
        $_SESSION['user']['2fa'] = $_POST["secret"];
        header("location: /");
    } else {
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Code is onjuist!');</script>";
        exit();
    }
}
?>
<script>document.getElementById('twofabox1').focus();</script>