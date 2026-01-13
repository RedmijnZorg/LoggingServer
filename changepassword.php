<?php
if(!isset($routerActive)) {
    ob_start();
    header("location: /wijzigwachtwoord");
    exit();
}
$loginOperations = new LoginOperations($database);
$userOperations = new UserOperations($database);

?>
<form method="post">
    <div class="message-container" style="height: 400px;">
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
if(isset($_POST["submit"])) {
    $login = $loginOperations->login($_SESSION['user']['email'], $_POST["currentpassword"]);

    if ($login == false) {
    	$loggingService->logEvent($_SESSION['user']['email'], $_SERVER['REQUEST_URI'], "account", "Password change for ".$_SESSION['user']['email'].", incorrect password", 0);
        echo "<script type='text/javascript'>setErrorMessage('errorinline','Huidig wachtwoord onjuist!');</script>";
    } else {
        if ($_POST["newpassword"] == $_POST["newpassword2"]) {
            if (trim($_POST["newpassword"], 'a..z') != '' && trim($_POST["newpassword"], 'A..Z') != '' && strlen($_POST["newpassword"]) >= 8) {
                $changepassword = $userOperations->changePassword($_SESSION['user']['userid'], $_POST["newpassword"]);
                if ($changepassword == true) {
                    $_SESSION["user"]['changepassword'] = 0;
                }

                header("location: /");

            } else {
                echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoord moet 8 tekens of meer bevatten, met minstens een kleine letter en een hoofdletter!');</script>";
            }
        } else {
            echo "<script type='text/javascript'>setErrorMessage('errorinline','Wachtwoorden komen niet overeen!');</script>";
        }
    }
}
?>

<script>document.getElementById('currentpassword').focus();</script>
