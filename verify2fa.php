<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /controle");
    exit();
}

$googleAuthenticator = new GoogleAuthenticator();
$userOperations = new UserOperations($database);
$userDetails = $userOperations->getUserDetails($_SESSION['user']['userid']);
?>
<form method="post" id="twofaform">
    <input type="hidden" name="submitform" value="1">
    <div class="message-container" style="height: 250px; width: 500px;">
        <p class="message-title">Voer uw authenticator code in</p>
        <div class="input-container">
            <div id="twofaboxes">
                <input type="text" class="twofabox" maxlength="1" id="twofabox1" name="2facode[0]" onkeyup="document.getElementById('twofabox2').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox2" name="2facode[1]" onkeyup="document.getElementById('twofabox3').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox3" name="2facode[2]" onkeyup="document.getElementById('twofabox4').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox4" name="2facode[3]" onkeyup="document.getElementById('twofabox5').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox5" name="2facode[4]" onkeyup="document.getElementById('twofabox6').focus();">
                <input type="text" class="twofabox" maxlength="1" id="twofabox6" name="2facode[5]" onkeyup="document.getElementById('twofaform').submit();">
            </div>
            <span>
          </span>
            <center>
            <button type="button" class="submit" onclick="document.getElementById('twofaform').submit();">
                Controleren
            </button>
            </center>
        </div>

        <span class="errormessage" id="errormessage"></span>

    </div>
</form>

<?php
if(isset($_POST["submitform"])){
    if(isset($_POST["2facode"]) AND count($_POST["2facode"]) == 6){
       $verifycode = $googleAuthenticator->verifyCode($userDetails['2fa'], implode($_POST["2facode"]));
       if($verifycode){
			$_SESSION["user"]['2fapass'] = true;
			header("location: /");
       } else {
			echo "<script type='text/javascript'>setErrorMessage('errormessage','Code onjuist of verlopen!');</script>";
			$loggingService->logEvent($userDetails['email'], $_SERVER['REQUEST_URI'], "account", "2FA challenge failed", 0);
       }
   } else {
       echo "<script type='text/javascript'>setErrorMessage('errormessage','Code is verplicht!');</script>";
   }
}
?>

<script>document.getElementById('twofabox1').focus();</script>