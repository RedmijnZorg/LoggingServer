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

    <div class="message-container" id='twofacontainer' style="height: 200px;">

<form id="twofaform" name="overLayForm" action="" method="post"
  autocomplete="off">
      <input type="hidden" name="submitform" value="1">

    <div class="formrow verify-form">
      <label id="verify-code" for="digit1">Voer de code uit uw Authenticator app in</label><br>

      <div class="digits">
        <span class="formwrap no-margin"><input id="digit1" type="text"
        class="digit-input" data-indx="0" data-next-id="digit2" value="" size="1"
        maxlength="1" autocomplete="off" name="2facode[0]" /></span> 
        <span class=
        "formwrap no-margin"><input id="digit2" type="text" data-prev-id=
        "digit1" class="digit-input" data-indx="1" data-next-id="digit3" value="" size=
        "1" maxlength="1" autocomplete="off"  name="2facode[1]" /></span> 
        <span class=
        "formwrap no-margin"><input id="digit3" type="text" data-prev-id=
        "digit2" class="digit-input" data-indx="2" data-next-id="digit4" value="" size=
        "1" maxlength="1" autocomplete="off"  name="2facode[2]" /></span> 
        <span class=
        "formwrap no-margin"><input id="digit4" type="text" data-prev-id=
        "digit3" class="digit-input" data-indx="3" data-next-id="digit5" value="" size=
        "1" maxlength="1" autocomplete="off"  name="2facode[3]" /></span> 
        <span class=
        "formwrap no-margin"><input id="digit5" type="text" data-prev-id=
        "digit4" class="digit-input" data-indx="4" data-next-id="digit6" value="" size=
        "1" maxlength="1" autocomplete="off"  name="2facode[4]" /></span> 
        <span class=
        "formwrap no-margin"><input id="digit6" type="text" data-prev-id=
        "digit5" class="digit-input" data-indx="5" value="" size="1" maxlength="1"
        autocomplete="off"  name="2facode[5]"/></span>
    </div>
  </div>
<br>
  <div id="invalidErrorText">
    <span id="invalidErrorText1"></span>
  </div><br>
   <button type="submit" class="submit disabled" id=
        "setupLink" name=
        "setupLink">
			Controleren
		</button><br>
<span class="errormessage" id="twofaerror"></span>
  </form>
</div>
<?php
if(isset($_POST["submitform"])){
    if(isset($_POST["2facode"]) AND count($_POST["2facode"]) == 6){
       $verifycode = $googleAuthenticator->verifyCode($userDetails['2fa'], implode($_POST["2facode"]));
       if($verifycode){
			$_SESSION["user"]['2fapass'] = true;
			header("location: /");
       } else {
			echo "<script type='text/javascript'>setErrorMessage('twofaerror','Code onjuist of verlopen!'); $('#digit1').focus();</script>";
       }
   } else {
       echo "<script type='text/javascript'>setErrorMessage('twofaerror','Code is verplicht!');</script>";
   }
}
?>
<script src="Assets/verify2fa.js?rand=<?php echo uniqid(time());?>"></script>